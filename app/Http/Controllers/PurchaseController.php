<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /** ===========================
     * PURCHASE LIST
     * =========================== */
    public function index()
    {
        $purchases = Purchase::with(['warehouse', 'supplier', 'creator'])
            ->latest()
            ->paginate(20);

        $warehouses = Warehouse::all();
        $suppliers = User::where('role', 'Supplier')->get();

        return view('purchases.index', compact('purchases', 'warehouses', 'suppliers'));
    }

    /** ===========================
     * CREATE PAGE
     * =========================== */
    public function create()
    {
        $lastPurchase = Purchase::latest('id')->first();
        $referenceNo = 'PUR-' . date('Ymd') . '-' . str_pad(($lastPurchase ? $lastPurchase->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $products = Product::all();
        $warehouses = Warehouse::all();
        $suppliers = User::where('role', 'Supplier')->get();
        $accounts = Account::where('status', 'active')->get();

        return view('purchases.create', compact('referenceNo', 'products', 'warehouses', 'suppliers', 'accounts'));
    }

    /** ===========================
     * STORE PURCHASE
     * =========================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date'   => 'required|date',
            'reference_no'    => 'required|unique:purchases,reference_no',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'supplier_id'     => 'nullable|exists:users,id',
            'purchase_status' => 'required|in:received,pending',
            'payment_status'  => 'required|in:paid,partial,pending',
            'payment_method'  => 'nullable|string',
            'account_id'      => 'required|exists:accounts,id',
            'grand_total'     => 'required|numeric',
            'amount_paid'     => 'nullable|numeric',
            'notes'           => 'nullable|string',
            'document'        => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'items'           => 'required|array|min:1',
        ]);

        if ($validated['supplier_id']) {
            $supplier = User::find($validated['supplier_id']);
            if (!$supplier || $supplier->role !== 'Supplier') {
                return back()->with('error', 'Selected user is not a supplier!');
            }
        }

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/purchases'), $filename);
            $documentPath = 'uploads/purchases/' . $filename;
        }

        $validated['created_by'] = auth()->id();
        $validated['document'] = $documentPath;

        // Amounts
        $paidAmount = $validated['amount_paid'] ?? 0;
        $dueAmount  = max(0, $validated['grand_total'] - $paidAmount);
        $validated['paid_amount'] = $paidAmount;
        $validated['due_amount']  = $dueAmount;

        DB::beginTransaction();
        try {
            // Create Purchase
            $purchase = Purchase::create($validated);

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

                if ($validated['purchase_status'] === 'received') {
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

            // Update account balance & create transaction
            if ($paidAmount > 0) {
                $account = Account::lockForUpdate()->find($validated['account_id']);
                if (!$account) throw new \Exception("Account not found");

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
            }

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Purchase created successfully!',
                'purchase_id' => $purchase->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /** ===========================
     * SHOW PURCHASE
     * =========================== */
    public function show(Purchase $purchase)
    {
        $purchase->load(['warehouse', 'supplier', 'creator', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    /** ===========================
     * EDIT PURCHASE
     * =========================== */
    public function edit(Purchase $purchase)
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        $suppliers = User::where('role', 'Supplier')->get();
        $accounts = Account::where('status', 'active')->get();

        return view('purchases.edit', compact('purchase', 'products', 'warehouses', 'suppliers', 'accounts'));
    }

    /** ===========================
     * UPDATE PURCHASE
     * =========================== */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'purchase_date'   => 'required|date',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'supplier_id'     => 'nullable|exists:users,id',
            'purchase_status' => 'required|in:received,pending',
            'payment_status'  => 'required|in:paid,partial,pending',
            'payment_method'  => 'nullable|string',
            'grand_total'     => 'required|numeric',
            'amount_paid'     => 'nullable|numeric',
            'notes'           => 'nullable|string',
            'document'        => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'items'           => 'required|array|min:1',
        ]);

        if ($validated['supplier_id']) {
            $supplier = User::find($validated['supplier_id']);
            if (!$supplier || $supplier->role !== 'Supplier') {
                return back()->with('error', 'Selected user is not a supplier!');
            }
        }

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/purchases'), $filename);
            $validated['document'] = 'uploads/purchases/' . $filename;
        }

        DB::beginTransaction();
        try {
            $purchase->update($validated);

            // Remove old items and add new
            $purchase->items()->delete();
            foreach ($request->items as $item) {
                $purchase->items()->create($item);

                if ($validated['purchase_status'] === 'received') {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                        if (isset($item['cost_price'])) $product->cost_price = $item['cost_price'];
                        $product->save();
                    }
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase Update Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update purchase: ' . $e->getMessage());
        }
    }

    /** ===========================
     * DELETE PURCHASE
     * =========================== */
    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) $product->decrement('stock', $item->quantity);
            }

            // Restore account balance
            if ($purchase->paid_amount > 0 && $purchase->account_id) {
                $account = Account::lockForUpdate()->find($purchase->account_id);
                if ($account) {
                    $balanceBefore = $account->current_balance;
                    $account->current_balance += $purchase->paid_amount;
                    $account->save();

                    AccountTransaction::create([
                        'account_id'       => $account->id,
                        'reference_type'   => 'purchase',
                        'reference_id'     => $purchase->id,
                        'transaction_type' => 'credit',
                        'amount'           => $purchase->paid_amount,
                        'balance_before'   => $balanceBefore,
                        'balance_after'    => $account->current_balance,
                        'description'      => "Purchase deletion reversal - {$purchase->reference_no}",
                        'transaction_date' => now()->format('Y-m-d'),
                        'created_by'       => auth()->id(),
                    ]);
                }
            }

            if ($purchase->document) @unlink(public_path($purchase->document));

            $purchase->delete();
            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }

    /** ===========================
     * GET PRODUCT DETAILS (AJAX)
     * =========================== */
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
}
