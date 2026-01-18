<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouse;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['warehouse', 'creator', 'items.product']);

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        }

        // Warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%");
                
                // Search in supplier names
                $q->orWhereHas('supplierModel', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
                
                $q->orWhereHas('userSupplier', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $purchases = $query->latest('purchase_date')->paginate(15);
        $warehouses = Warehouse::where('status', 'active')->get();

        // Load supplier data dynamically for each row to avoid N+1 issues inside the view
        $purchases->getCollection()->each(function ($purchase) {
            if ($purchase->supplier_type === 'supplier') {
                $purchase->load('supplierModel');
            } else {
                $purchase->load('userSupplier');
            }
        });

        return view('purchases.index', compact('purchases', 'warehouses'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $lastPurchase = Purchase::latest('id')->first();
        $referenceNo = 'PUR-' . date('Ymd') . '-' . str_pad(($lastPurchase ? $lastPurchase->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $products = Product::all();
        $warehouses = Warehouse::where('status', 'active')->get();
        $accounts = Account::where('status', 'active')->get();
        
        // Get formatted suppliers list (Objects)
        $suppliers = $this->getSuppliersForDropdown();

        return view('purchases.create', compact('referenceNo', 'products', 'warehouses', 'suppliers', 'accounts'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date'   => 'required|date|before_or_equal:today',
            'reference_no'    => 'required|unique:purchases,reference_no',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'supplier_id'     => 'nullable|string',
            'purchase_status' => 'required|in:received,pending',
            'payment_status'  => 'required|in:paid,partial,pending,unpaid',
            'payment_method'  => 'nullable|string',
            'account_id'      => 'required|exists:accounts,id',
            'grand_total'     => 'required|numeric|min:0',
            'amount_paid'     => 'nullable|numeric|min:0',
            'tax_percentage'  => 'nullable|numeric|min:0',
            'discount_value'  => 'nullable|numeric|min:0',
            'shipping_cost'   => 'nullable|numeric|min:0',
            'currency'        => 'nullable|string',
            'exchange_rate'   => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
            'document'        => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'items'           => 'required|array|min:1',
        ], [
            'purchase_date.before_or_equal' => 'Purchase date cannot be in the future.',
            'warehouse_id.required' => 'Please select a warehouse.',
            'account_id.required' => 'Please select an account.',
            'items.required' => 'Please add at least one product.',
            'items.min' => 'Please add at least one product.',
        ]);

        // Parse supplier_id
        $supplierData = $this->parseSupplierData($validated['supplier_id'] ?? null);

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            try {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/purchases'), $filename);
                $documentPath = 'uploads/purchases/' . $filename;
            } catch (\Exception $e) {
                Log::warning('Document upload failed: ' . $e->getMessage());
            }
        }

        // Calculate amounts
        $paidAmount = floatval($validated['amount_paid'] ?? 0);
        $grandTotal = floatval($validated['grand_total']);
        $dueAmount  = max(0, $grandTotal - $paidAmount);

        DB::beginTransaction();
        try {
            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemTotal = ($item['quantity'] * $item['cost_price']) - ($item['discount'] ?? 0) + ($item['tax'] ?? 0);
                $subtotal += $itemTotal;
            }

            $taxPercentage = floatval($validated['tax_percentage'] ?? 0);
            $taxAmount = ($subtotal * $taxPercentage) / 100;
            $discountValue = floatval($validated['discount_value'] ?? 0);
            $shippingCost = floatval($validated['shipping_cost'] ?? 0);
            $total = $subtotal + $taxAmount - $discountValue + $shippingCost;

            // Create Purchase
            $purchase = Purchase::create([
                'reference_no'     => $validated['reference_no'],
                'purchase_date'    => $validated['purchase_date'],
                'warehouse_id'     => $validated['warehouse_id'],
                'supplier_id'      => $supplierData['supplier_id'],
                'supplier_type'    => $supplierData['supplier_type'],
                'purchase_status'  => $validated['purchase_status'],
                'payment_status'   => $validated['payment_status'],
                'payment_method'   => $validated['payment_method'] ?? 'cash',
                'account_id'       => $validated['account_id'],
                'currency'         => $validated['currency'] ?? 'BDT',
                'exchange_rate'    => $validated['exchange_rate'] ?? 1.00,
                'subtotal'         => $subtotal,
                'tax_percentage'   => $taxPercentage,
                'tax_amount'       => $taxAmount,
                'discount_type'    => 'flat',
                'discount_value'   => $discountValue,
                'discount_amount'  => $discountValue,
                'shipping_cost'    => $shippingCost,
                'total'            => $total,
                'grand_total'      => $grandTotal,
                'paid_amount'      => $paidAmount,
                'due_amount'       => $dueAmount,
                'notes'            => $validated['notes'] ?? null,
                'document'         => $documentPath,
                'document_path'    => $documentPath,
                'created_by'       => auth()->id(),
            ]);

            // Create Items and update stock
            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'batch_id'    => $item['batch_id'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'cost_price'  => $item['cost_price'] ?? 0,
                    'discount'    => $item['discount'] ?? 0,
                    'tax'         => $item['tax'] ?? 0,
                ]);

                // Update warehouse stock if status is 'received'
                if ($validated['purchase_status'] === 'received') {
                    // Add stock to product_warehouse table
                   // Add stock to product_warehouse table
                    \App\Models\ProductWarehouse::addStock(
                               $validated['warehouse_id'],
                                   $item['product_id'],
                                      $item['quantity'],
                              $item['batch_id'] ?? null,
                           $item['expiry_date'] ?? null
                                            );

                    // Update global product stock
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                        if (isset($item['cost_price'])) {
                            $product->cost_price = $item['cost_price'];
                        }
                        $product->save();
                    }

                    Log::info('Warehouse stock added', [
                        'warehouse_id' => $validated['warehouse_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'batch_id' => $item['batch_id'] ?? 'N/A',
                    ]);
                }
            }

            // Update account balance & create transaction
            if ($paidAmount > 0) {
                $account = Account::lockForUpdate()->find($validated['account_id']);
                if (!$account) throw new \Exception("Account not found");

                if ($account->current_balance < $paidAmount) {
                    throw new \Exception("Insufficient account balance. Available: ৳" . number_format($account->current_balance, 2) . ", Required: ৳" . number_format($paidAmount, 2));
                }

                $balanceBefore = $account->current_balance;
                $account->current_balance -= $paidAmount;
                $account->save();

                AccountTransaction::create([
                    'account_id'       => $account->id,
                    'reference_type'   => 'purchase',
                    'reference_id'     => $purchase->id,
                    'transaction_type' => 'debit',
                    'amount'           => $paidAmount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $account->current_balance,
                    'description'      => "Purchase payment - {$purchase->reference_no}",
                    'transaction_date' => $validated['purchase_date'],
                    'created_by'       => auth()->id(),
                ]);

                Log::info('Account transaction created for purchase', [
                    'account_id' => $account->id,
                    'account_name' => $account->name,
                    'amount' => $paidAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $account->current_balance,
                ]);
            }

            // Update supplier's total_due
            if ($supplierData['supplier_type'] === 'supplier' && $dueAmount > 0) {
                $supplier = Supplier::find($supplierData['supplier_id']);
                if ($supplier) {
                    $supplier->increment('total_due', $dueAmount);
                    
                    Log::info('Supplier due updated', [
                        'supplier_id' => $supplier->id,
                        'supplier_name' => $supplier->name,
                        'due_added' => $dueAmount,
                        'total_due' => $supplier->fresh()->total_due,
                    ]);
                }
            }

            DB::commit();

            Log::info('Purchase completed successfully', [
                'purchase_id' => $purchase->id,
                'reference_no' => $purchase->reference_no,
                'total_items' => count($request->items),
            ]);

            return response()->json([
                'success'     => true,
                'message'     => 'Purchase created successfully!',
                'purchase_id' => $purchase->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('PURCHASE STORE ERROR: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->except(['document']),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        // Load supplier dynamically
        if ($purchase->supplier_type === 'supplier') {
            $purchase->load('supplierModel');
        } else {
            $purchase->load('userSupplier');
        }
        
        // Load all necessary relationships including items with products
        $purchase->load(['warehouse', 'creator', 'items.product', 'account']);
        
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        $purchase->load(['items.product', 'warehouse', 'account']);
        
        $products = Product::all();
        $warehouses = Warehouse::where('status', 'active')->get();
        $accounts = Account::where('status', 'active')->get();

        // Get formatted suppliers list (Objects)
        $suppliers = $this->getSuppliersForDropdown();

        return view('purchases.edit', compact('purchase', 'products', 'warehouses', 'suppliers', 'accounts'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'purchase_date'   => 'required|date',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'supplier_id'     => 'required|string',
            'purchase_status' => 'required|in:received,pending',
            'payment_status'  => 'required|in:paid,partial,pending',
            'payment_method'  => 'nullable|string',
            'grand_total'     => 'required|numeric',
            'amount_paid'     => 'nullable|numeric',
            'notes'           => 'nullable|string',
            'document'        => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'items'           => 'required|array|min:1',
        ]);

        // Parse supplier
        $supplierData = $this->parseSupplierData($validated['supplier_id']);
        
        // Handle document
        if ($request->hasFile('document')) {
            if ($purchase->document_path) {
                @unlink(public_path($purchase->document_path));
            }
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/purchases'), $filename);
            $documentPath = 'uploads/purchases/' . $filename;
        } else {
            $documentPath = $purchase->document_path;
        }

        DB::beginTransaction();
        try {
            $oldPaidAmount = $purchase->paid_amount;
            $newPaidAmount = floatval($validated['amount_paid'] ?? 0);
            $paidDifference = $newPaidAmount - $oldPaidAmount;

            $newDue = floatval($validated['grand_total']) - $newPaidAmount;

            // Update Purchase Info
            $purchase->update([
                'purchase_date'   => $validated['purchase_date'],
                'warehouse_id'    => $validated['warehouse_id'],
                'supplier_id'     => $supplierData['supplier_id'],
                'supplier_type'   => $supplierData['supplier_type'],
                'purchase_status' => $validated['purchase_status'],
                'payment_status'  => $validated['payment_status'],
                'payment_method'  => $validated['payment_method'],
                'grand_total'     => $validated['grand_total'],
                'paid_amount'     => $newPaidAmount,
                'due_amount'      => $newDue,
                'notes'           => $validated['notes'],
                'document'        => $documentPath,
                'document_path'   => $documentPath,
            ]);

            // --- Handle Stock & Items ---

            // 1. Reverse stock from OLD items (if old status was received)
            if ($purchase->purchase_status === 'received') {
                foreach ($purchase->items as $oldItem) {
                    // Remove from product_warehouse
                    try {
                        \App\Models\ProductWarehouse::removeStock(
                            $purchase->warehouse_id,
                            $oldItem->product_id,
                            $oldItem->quantity
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Could not remove old warehouse stock: ' . $e->getMessage());
                    }

                    // Decrease global stock
                    $product = Product::find($oldItem->product_id);
                    if ($product) {
                        $product->decrement('stock', $oldItem->quantity);
                    }
                }
            }

            // 2. Delete old items
            $purchase->items()->delete();

            // 3. Add new items and update stock (if new status is received)
            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'batch_id'    => $item['batch_id'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'cost_price'  => $item['cost_price'] ?? 0,
                    'discount'    => $item['discount'] ?? 0,
                    'tax'         => $item['tax'] ?? 0,
                ]);

              if ($validated['purchase_status'] === 'received') {
    // Add to product_warehouse
    \App\Models\ProductWarehouse::addStock(
        $validated['warehouse_id'],
        $item['product_id'],
        $item['quantity'],
        $item['batch_id'] ?? null,
        $item['expiry_date'] ?? null
    );
                    // Increase global stock
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                        if (isset($item['cost_price'])) {
                            $product->cost_price = $item['cost_price'];
                        }
                        $product->save();
                    }
                }
            }

            // --- Handle Accounts ---
            if ($paidDifference != 0) {
                $account = Account::lockForUpdate()->find($purchase->account_id);
                if ($account) {
                    // If payment increased (Debit more money)
                    if ($paidDifference > 0) {
                        if ($account->current_balance < $paidDifference) {
                            throw new \Exception("Insufficient balance for additional payment.");
                        }
                        $account->current_balance -= $paidDifference;
                        $transType = 'debit';
                        $desc = "Additional Purchase payment - {$purchase->reference_no}";
                    } 
                    // If payment decreased (Refund money / Credit)
                    else {
                        $account->current_balance += abs($paidDifference);
                        $transType = 'credit';
                        $desc = "Purchase payment refund - {$purchase->reference_no}";
                    }
                    $account->save();

                    AccountTransaction::create([
                        'account_id'       => $account->id,
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->id,
                        'transaction_type' => $transType,
                        'amount'           => abs($paidDifference),
                        'balance_before'   => $account->current_balance + ($transType == 'debit' ? abs($paidDifference) : -abs($paidDifference)),
                        'balance_after'    => $account->current_balance,
                        'description'      => $desc,
                        'transaction_date' => $validated['purchase_date'],
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            // --- Handle Supplier Due ---
            if ($supplierData['supplier_type'] === 'supplier') {
                $supplier = Supplier::find($supplierData['supplier_id']);
                if ($supplier) {
                    $totalDue = Purchase::where('supplier_id', $supplier->id)
                        ->where('supplier_type', 'supplier')
                        ->sum('due_amount');
                    $supplier->total_due = $totalDue;
                    $supplier->save();
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase Update Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update purchase: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            // Restore stock
            if ($purchase->purchase_status === 'received') {
                foreach ($purchase->items as $item) {
                    // Remove from product_warehouse
                    try {
                        \App\Models\ProductWarehouse::removeStock(
                            $purchase->warehouse_id,
                            $item->product_id,
                            $item->quantity
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Could not remove warehouse stock during delete: ' . $e->getMessage());
                    }

                    // Decrease global stock
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                    }
                }
            }

            // Restore account balance
            if ($purchase->paid_amount > 0 && $purchase->account_id) {
                $account = Account::lockForUpdate()->find($purchase->account_id);
                if ($account) {
                    $account->current_balance += $purchase->paid_amount;
                    $account->save();

                    AccountTransaction::create([
                        'account_id'       => $account->id,
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->id,
                        'transaction_type' => 'credit',
                        'amount'           => $purchase->paid_amount,
                        'balance_before'   => $account->current_balance - $purchase->paid_amount,
                        'balance_after'    => $account->current_balance,
                        'description'      => "Purchase deletion reversal - {$purchase->reference_no}",
                        'transaction_date' => now()->format('Y-m-d'),
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            // Restore supplier due
            if ($purchase->supplier_type === 'supplier' && $purchase->due_amount > 0) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->decrement('total_due', $purchase->due_amount);
                }
            }

            // Delete file
            if ($purchase->document_path) {
                @unlink(public_path($purchase->document_path));
            }

            $purchase->delete();
            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }

    /**
     * Get Product Details (AJAX)
     */
    public function getProductDetails($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success'=>false,'message'=>'Product not found'],404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id'            => $product->id,
                'name'          => $product->name,
                'code'          => $product->product_code ?? 'N/A',
                'cost_price'    => $product->cost_price ?? 0,
                'current_stock' => $product->stock,
                'unit'          => optional($product->unit)->name ?? 'pc',
            ]
        ]);
    }

    /**
     * Helper: Get suppliers formatted for dropdown as Objects
     */
    private function getSuppliersForDropdown()
    {
        // Get suppliers from suppliers table
        $suppliersFromTable = Supplier::where('status', 'active')
            ->select('id', 'name', 'company', 'phone')
            ->get()
            ->map(function($supplier) {
                return (object)[
                    'id' => 'supplier_' . $supplier->id,
                    'name' => $supplier->name . ($supplier->company ? " ({$supplier->company})" : ''),
                    'type' => 'supplier',
                    'actual_id' => $supplier->id
                ];
            });

        // Get users with role 'Supplier'
        $userSuppliers = User::where('role', 'Supplier')
            ->where('status', 'active')
            ->select('id', 'name', 'phone')
            ->get()
            ->map(function($user) {
                return (object)[
                    'id' => 'user_' . $user->id,
                    'name' => $user->name . ' (User Account)',
                    'type' => 'user',
                    'actual_id' => $user->id
                ];
            });

        // Merge and return as collection of objects
        return $suppliersFromTable->concat($userSuppliers)->sortBy('name')->values();
    }

    /**
     * Helper: Parse supplier data from combined ID
     */
    private function parseSupplierData($combinedId)
    {
        if (strpos($combinedId, 'supplier_') === 0) {
            return [
                'supplier_id' => (int) str_replace('supplier_', '', $combinedId),
                'supplier_type' => 'supplier'
            ];
        } elseif (strpos($combinedId, 'user_') === 0) {
            return [
                'supplier_id' => (int) str_replace('user_', '', $combinedId),
                'supplier_type' => 'user'
            ];
        }

        // Fallback: assume it's a direct supplier ID (integer)
        return [
            'supplier_id' => (int) $combinedId,
            'supplier_type' => 'supplier'
        ];
    }
}