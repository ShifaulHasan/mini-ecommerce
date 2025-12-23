<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

// Models
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Account;
use App\Models\Transaction;

class POSController extends Controller
{
    /**
     * POS main page - loads products, categories, customers, main warehouse
     */
    public function index()
    {
        $mainWarehouse = Warehouse::first();
        
        // 🔥 FIXED: Get customers from User model with role = Customer
        $customers = User::where('role', 'Customer')
            ->orderBy('name', 'ASC')
            ->get();
            
        $categories = Category::orderBy('name')->get();
        $products = Product::select('id', 'name', 'price', 'stock', 'image', 'category_id')->get();
        $accounts = Account::where('status', 'active')->get();

        return view('pos.index', compact(
            'mainWarehouse',
            'customers',
            'products',
            'categories',
            'accounts'
        ));
    }

    /**
     * Return current cart (useful for AJAX refresh)
     */
    public function cart(Request $request)
    {
        $cart = session('pos_cart', []);
        $customerId = session('pos_customer_id', null);

        // optional inputs for preview
        $taxPercentage = $request->input('tax_percentage', 0);
        $discountAmount = $request->input('discount_amount', 0);

        $summary = $this->calculateSummary($cart, $taxPercentage, $discountAmount);

        return response()->json([
            'success' => true,
            'cart' => array_values($cart), // 🔥 FIXED: Return indexed array
            'summary' => $summary,
            'customer_id' => $customerId,
        ]);
    }

    /**
     * Add product to cart (AJAX)
     * Route: POST /pos/add-to-cart/{id}
     */
    public function addToCart(Request $request, $productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $stock = (int) ($product->stock ?? 0);
        if ($stock <= 0) {
            return response()->json(['success' => false, 'message' => 'Product out of stock.'], 400);
        }

        $unitPrice = isset($product->price) ? (float)$product->price : ((isset($product->selling_price) ? (float)$product->selling_price : 0.0));

        $cart = session('pos_cart', []);

        // find existing item
        $foundKey = null;
        foreach ($cart as $k => $item) {
            if ((int)$item['product_id'] === (int)$product->id) {
                $foundKey = $k;
                break;
            }
        }

        if ($foundKey !== null) {
            // increment quantity if stock allows
            if ($cart[$foundKey]['quantity'] + 1 > $stock) {
                return response()->json(['success' => false, 'message' => 'Not enough stock.'], 400);
            }
            $cart[$foundKey]['quantity'] += 1;
        } else {
            // 🔥 FIXED: Added product_name field
            $cart[] = [
                'product_id'   => (int)$product->id,
                'product_name' => $product->name, // 🔥 THIS WAS MISSING!
                'name'         => $product->name,
                'unit_price'   => round($unitPrice, 2),
                'quantity'     => 1,
                'stock'        => $stock,
                'discount'     => 0,
                'image'        => $product->image ?? null,
            ];
        }

        session(['pos_cart' => $cart]);

        // return updated cart + summary
        $summary = $this->calculateSummary($cart, $request->input('tax_percentage', 0), $request->input('discount_amount', 0));

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart.',
            'cart' => array_values($cart), // 🔥 FIXED: Return indexed array
            'summary' => $summary,
        ]);
    }

    /**
     * Update quantity for a cart item
     * Route: POST /pos/update-qty
     * payload: product_id, quantity
     */
    public function updateQty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productId = (int)$request->product_id;
        $quantity  = (int)$request->quantity;

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $stock = (int)($product->stock ?? 0);
        if ($quantity > $stock) {
            return response()->json(['success' => false, 'message' => 'Not enough stock.'], 400);
        }

        $cart = session('pos_cart', []);
        foreach ($cart as $k => $item) {
            if ((int)$item['product_id'] === $productId) {
                $cart[$k]['quantity'] = $quantity;
                break;
            }
        }
        session(['pos_cart' => $cart]);

        $summary = $this->calculateSummary($cart, $request->input('tax_percentage', 0), $request->input('discount_amount', 0));

        return response()->json([
            'success' => true,
            'cart' => array_values($cart), // 🔥 FIXED: Return indexed array
            'summary' => $summary,
        ]);
    }

    /**
     * Remove item from cart
     * Route: DELETE /pos/remove/{id}
     */
    public function removeItem(Request $request, $productId)
    {
        $cart = session('pos_cart', []);
        $cart = array_values(array_filter($cart, function ($item) use ($productId) {
            return (int)$item['product_id'] !== (int)$productId;
        }));
        session(['pos_cart' => $cart]);

        $summary = $this->calculateSummary($cart, $request->input('tax_percentage', 0), $request->input('discount_amount', 0));

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    /**
     * Clear cart
     * Route: POST /pos/clear
     */
    public function clearCart(Request $request)
    {
        session(['pos_cart' => []]);
        session()->forget('pos_customer_id');

        return response()->json(['success' => true, 'message' => 'Cart cleared.']);
    }

    /**
     * Set customer (select from dropdown)
     * Route: POST /pos/set-customer
     * payload: customer_id
     */
    public function setCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:users,id', // 🔥 FIXED: Changed to users table
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        session(['pos_customer_id' => (int)$request->customer_id]);

        return response()->json(['success' => true, 'customer_id' => session('pos_customer_id')]);
    }

    /**
     * Store the sale (complete checkout)
     * Route: POST /pos/store
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id'   => 'nullable|integer|exists:warehouses,id',
            'customer_id'    => 'nullable|integer|exists:users,id', // 🔥 FIXED: Changed to users, made nullable
            'products'       => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'account_id'     => 'nullable|integer|exists:accounts,id', // Made nullable
            'amount_paid'    => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0',
            'discount_amount'=> 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 🔥 FIXED: Validate customer role if provided
        if (!empty($request->customer_id)) {
            $customer = User::find($request->customer_id);
            if (!$customer || $customer->role !== 'Customer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a customer'
                ], 422);
            }
        }

        $cart = $request->products;
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart empty.'], 400);
        }

        DB::beginTransaction();
        try {
            // Re-check stock
            foreach ($cart as $item) {
                $p = Product::lockForUpdate()->find($item['product_id']);
                if (!$p) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Product not found: ID '.$item['product_id']], 404);
                }
                if (($p->stock ?? 0) < $item['quantity']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Insufficient stock for '.$p->name], 400);
                }
            }

            $referenceNumber = 'SAL-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $currentDateTime = now();

            // Calculate totals
            $subtotal = 0;
            foreach ($cart as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $taxAmount = ($subtotal * ($request->tax_percentage ?? 0)) / 100;
            $discountAmount = $request->discount_amount ?? 0;
            $grandTotal = $subtotal + $taxAmount - $discountAmount;
            $amountPaid = (float) ($request->amount_paid ?? $grandTotal);
            $dueAmount = max(0, $grandTotal - $amountPaid);

            // Determine payment status
            if ($amountPaid >= $grandTotal) {
                $paymentStatus = 'paid';
            } elseif ($amountPaid > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // 🔥 Get biller name (logged-in user)
            $billerName = auth()->user()->name ?? 'POS User';

            // Create sale
            $sale = Sale::create([
                'reference_number' => $referenceNumber,
                'customer_id'      => $request->customer_id, // Can be null for walk-in
                'warehouse_id'     => $request->warehouse_id,
                'biller'           => $billerName, // 🔥 FIXED: Added biller
                'sale_date'        => $currentDateTime->format('Y-m-d'),
                'grand_total'      => $grandTotal,
                'returned_amount'  => 0.00,
                'paid_amount'      => $amountPaid,
                'due_amount'       => $dueAmount,
                'sale_status'      => 'completed',
                'payment_status'   => $paymentStatus,
                'payment_method'   => $request->payment_method,
                'account_id'       => $request->account_id,
                'sale_type'        => 'regular',
                'delivery_status'  => 'delivered', // 🔥 FIXED: Added delivery_status
                'notes'            => $request->notes ?? null,
                'created_by'       => auth()->id(), // 🔥 FIXED: Added created_by
                'created_at'       => $currentDateTime,
                'updated_at'       => $currentDateTime,
            ]);

            // Create sale items
            foreach ($cart as $item) {
                $p = Product::find($item['product_id']);
                $unitPrice = $item['unit_price'];
                $quantity = $item['quantity'];
                $discount = $item['discount'] ?? 0;
                $lineSubtotal = ($unitPrice * $quantity) - $discount;

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $p->id,
                    'quantity'   => $quantity,
                    'price'      => $unitPrice,
                    'unit_price' => $unitPrice, // Some systems use this
                    'discount'   => $discount,
                    'tax'        => 0,
                    'subtotal'   => $lineSubtotal,
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                ]);

                // Reduce stock
                $p->stock = max(0, ($p->stock ?? 0) - $quantity);
                $p->save();
            }

           // Update account balance & create transaction
if ($amountPaid > 0 && $request->account_id) {
    $account = Account::lockForUpdate()->find($request->account_id);
    
    if ($account) {
        // Store balance before update
        $balanceBefore = $account->current_balance;

        // Increase account balance
        $account->current_balance += $amountPaid;
        $account->save();

        // 🔥 CRITICAL FIX: Create transaction record with correct field names
        \App\Models\AccountTransaction::create([
            'account_id'       => $account->id,
            'reference_type'   => 'sale',
            'reference_id'     => $sale->id,
            'transaction_type' => 'credit',
            'amount'           => $amountPaid,
            'balance_before'   => $balanceBefore,
            'balance_after'    => $account->current_balance,
            'description'      => "POS Sale - {$referenceNumber}",
            'transaction_date' => $currentDateTime->format('Y-m-d'),
            'created_by'       => auth()->id(),
        ]);

        // 🔥 LOG for debugging
        \Log::info('POS Sale Transaction Created', [
            'sale_id' => $sale->id,
            'reference_number' => $referenceNumber,
            'account_id' => $account->id,
            'amount' => $amountPaid,
            'transaction_date' => $currentDateTime->format('Y-m-d'),
        ]);
    }
}

            DB::commit();

            // Clear session cart
            session(['pos_cart' => []]);
            session()->forget('pos_customer_id');

            return response()->json([
                'success' => true,
                'reference_no' => $referenceNumber,
                'change' => max(0, $amountPaid - $grandTotal),
                'sale_id' => $sale->id,
                'message' => 'Sale completed successfully!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('POS Sale Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: compute summary totals (used for cart preview)
     */
    protected function calculateSummary(array $cart, $taxPercentage = 0, $discountAmount = 0)
    {
        $subtotal = 0;
        $itemsCount = 0;

        foreach ($cart as $item) {
            $line = (($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0)) - ($item['discount'] ?? 0);
            $subtotal += $line;
            $itemsCount += ($item['quantity'] ?? 0);
        }

        $tax = ($subtotal * floatval($taxPercentage)) / 100;
        $grand = $subtotal + $tax - floatval($discountAmount);

        return [
            'items_count' => $itemsCount,
            'subtotal'    => round($subtotal, 2),
            'tax'         => round($tax, 2),
            'discount'    => round(floatval($discountAmount), 2),
            'grand_total' => round($grand, 2),
        ];
    }
}