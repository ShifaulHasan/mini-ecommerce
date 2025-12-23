<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    /**
     * ===========================
     * SALE LIST
     * ===========================
     */
    public function index()
    {
        $sales = Sale::with(['warehouse', 'customer', 'creator', 'items.product'])
            ->latest()
            ->paginate(20);

        $warehouses = Warehouse::all();

        return view('sales.index', compact('sales', 'warehouses'));
    }

    /**
     * ===========================
     * CREATE SALE PAGE
     * ===========================
     */
public function create()
{
    $referenceNo = Sale::generateReferenceNo();

    $products   = Product::all();
    $warehouses = Warehouse::all();
    $customers  = User::where('role', 'Customer')->get();
    $accounts   = \App\Models\Account::where('status', 'active')->get(); // 🔥 NEW

    return view('sales.create', compact(
        'referenceNo',
        'products',
        'warehouses',
        'customers',
        'accounts' // 🔥 NEW
    ));
}
    /**
     * ===========================
     * STORE SALE (UPDATED)
     * ===========================
     */
   public function store(Request $request)
{
    /**
     * ✅ JSON support
     */
    if ($request->isJson()) {
        $request->merge($request->json()->all());
    }

    /**
     * ✅ Validation (ADDED account_id)
     */
    $validated = $request->validate([
        'sale_date'      => 'required|date',
        'warehouse_id'   => 'required|exists:warehouses,id',
        'customer_id'    => 'nullable|exists:users,id',

        'sale_status'    => 'required|in:completed,pending',
        'payment_status' => 'required|in:paid,partial,pending',
        'payment_method' => 'nullable|string',
        'account_id'     => 'required|exists:accounts,id', // 🔥 Required account
        'delivery_status' => 'nullable|in:pending,processing,delivered,cancelled',

        'grand_total'    => 'required|numeric|min:0',
        'amount_paid'    => 'nullable|numeric|min:0',

        'notes'          => 'nullable|string',

        'items'                  => 'required|array|min:1',
        'items.*.product_id'     => 'required|exists:products,id',
        'items.*.quantity'       => 'required|numeric|min:1',
        'items.*.unit_price'     => 'required|numeric|min:0',
        'items.*.discount'       => 'nullable|numeric|min:0',
        'items.*.tax'            => 'nullable|numeric|min:0',
    ]);

    /**
     * ✅ Customer role check
     */
    if (!empty($validated['customer_id'])) {
        $customer = User::find($validated['customer_id']);
        if (!$customer || $customer->role !== 'Customer') {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not a customer'
            ], 422);
        }
    }

    /**
     * ✅ Amount calculation
     */
    $paidAmount = $validated['amount_paid'] ?? 0;
    $dueAmount  = max(0, $validated['grand_total'] - $paidAmount);

    /**
     * 🔥 Get biller name (logged-in user's name)
     */
    $billerName = auth()->user()->name ?? 'Admin';

    /**
     * 🔥 Set delivery_status based on sale_status
     */
    $deliveryStatus = $validated['delivery_status'] ?? null;
    
    if (!$deliveryStatus) {
        if ($validated['sale_status'] === 'completed') {
            $deliveryStatus = 'delivered';
        } else {
            $deliveryStatus = 'pending';
        }
    }

    DB::beginTransaction();

    try {
        /**
         * ======================
         * CREATE SALE (UPDATED)
         * ======================
         */
        $sale = Sale::create([
            'reference_number' => Sale::generateReferenceNo(),
            'sale_date'        => $validated['sale_date'],
            'warehouse_id'     => $validated['warehouse_id'],
            'customer_id'      => $validated['customer_id'] ?? null,

            'biller'           => $billerName,
            'sale_status'      => $validated['sale_status'],
            'payment_status'   => $validated['payment_status'],
            'payment_method'   => $validated['payment_method'] ?? null,
            'account_id'       => $validated['account_id'], // 🔥 Save account_id
            'delivery_status'  => $deliveryStatus,

            'grand_total'      => $validated['grand_total'],
            'paid_amount'      => $paidAmount,
            'due_amount'       => $dueAmount,

            'notes'            => $validated['notes'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        /**
         * ======================
         * SALE ITEMS + STOCK
         * ======================
         */
        foreach ($validated['items'] as $item) {

            $subtotal =
                ($item['quantity'] * $item['unit_price'])
                - ($item['discount'] ?? 0)
                + ($item['tax'] ?? 0);

            $sale->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['unit_price'],
                'unit_price' => $item['unit_price'],
                'discount'   => $item['discount'] ?? 0,
                'tax'        => $item['tax'] ?? 0,
                'subtotal'   => $subtotal,
            ]);

            /**
             * STOCK UPDATE
             */
            if ($validated['sale_status'] === 'completed') {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception(
                        "Insufficient stock for {$product->name}"
                    );
                }

                $product->decrement('stock', $item['quantity']);
            }
        }

        /**
         * 🔥🔥🔥 FIXED: UPDATE ACCOUNT BALANCE & CREATE TRANSACTION 🔥🔥🔥
         */
        if ($paidAmount > 0 && $validated['account_id']) {
            $account = \App\Models\Account::lockForUpdate()->find($validated['account_id']);
            
            if (!$account) {
                throw new \Exception("Account not found");
            }

            // Store balance before update
            $balanceBefore = $account->current_balance;

            // Increase account balance
            $account->current_balance += $paidAmount;
            $account->save();

            // 🔥 CRITICAL FIX: Create transaction record with correct field names
            \App\Models\AccountTransaction::create([
                'account_id'       => $account->id,
                'reference_type'   => 'sale',
                'reference_id'     => $sale->id,
                'transaction_type' => 'credit',
                'amount'           => $paidAmount,
                'balance_before'   => $balanceBefore,
                'balance_after'    => $account->current_balance,
                'description'      => "Sale payment - {$sale->reference_number}",
                'transaction_date' => $validated['sale_date'],
                'created_by'       => auth()->id(),
            ]);

            // 🔥 LOG for debugging
            \Log::info('Sale Transaction Created', [
                'sale_id' => $sale->id,
                'reference_number' => $sale->reference_number,
                'account_id' => $account->id,
                'amount' => $paidAmount,
                'transaction_date' => $validated['sale_date'],
            ]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Sale created successfully',
            'sale_id'  => $sale->id,
            'redirect' => route('sales.index')
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('SALE STORE ERROR: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }

}
    /**
     * ===========================
     * SHOW SALE
     * ===========================
     */
    public function show(Sale $sale)
    {
        $sale->load(['warehouse', 'customer', 'creator', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * ===========================
     * EDIT SALE
     * ===========================
     */
    public function edit(Sale $sale)
    {
        $products   = Product::all();
        $warehouses = Warehouse::all();
        $customers  = User::where('role', 'Customer')->get();

        return view('sales.edit', compact(
            'sale',
            'products',
            'warehouses',
            'customers'
        ));
    }

    /**
     * ===========================
     * UPDATE SALE
     * ===========================
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'sale_date'      => 'required|date',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'customer_id'    => 'nullable|exists:users,id',
            'sale_status'    => 'required|in:completed,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $sale->update($validated);

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale updated successfully');
    }

    /**
     * ===========================
     * DELETE SALE
     * ===========================
     */
    public function destroy(Sale $sale)
    {
        DB::beginTransaction();

        try {
            if ($sale->sale_status === 'completed') {
                foreach ($sale->items as $item) {
                    Product::where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            $sale->delete();

            DB::commit();

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SALE DELETE ERROR: ' . $e->getMessage());

            return back()->with('error', 'Delete failed');
        }
    }
}