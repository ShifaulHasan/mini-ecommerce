<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * ===========================
     * SALE LIST
     * ===========================
     */
    public function index()
    {
        // Eager load relationships
        $sales = Sale::with(['warehouse', 'customer', 'creator', 'items.product'])
            ->latest()
            ->paginate(20);

        // Pass warehouses for filter or display
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
        $lastSale = Sale::latest('id')->first();
        $referenceNo = 'SAL-' . date('Ymd') . '-' . str_pad(($lastSale ? $lastSale->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        $products = Product::all();
        $warehouses = Warehouse::all();
        $customers = User::where('role', 'Customer')->get();

        return view('sales.create', compact('referenceNo', 'products', 'warehouses', 'customers'));
    }

    /**
     * ===========================
     * STORE SALE
     * ===========================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'reference_no' => 'required|unique:sales,reference_no',
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'nullable|exists:users,id',
            'sale_status' => 'required|in:completed,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'currency' => 'required|string',
            'exchange_rate' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
        ]);

        // Verify customer role
        if ($validated['customer_id']) {
            $customer = User::find($validated['customer_id']);
            if ($customer->role !== 'Customer') {
                return back()->with('error', 'Selected user is not a customer!');
            }
        }

        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            // Create sale
            $sale = Sale::create($validated);

            // Store sale items & update stock if completed
            foreach ($request->items as $item) {
                $sale->items()->create($item);

                if ($validated['sale_status'] === 'completed') {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    if ($product->stock < $item['quantity']) {
                        DB::rollBack();
                        return back()->with('error', "Insufficient stock for {$product->name}!");
                    }

                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
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
        $products = Product::all();
        $warehouses = Warehouse::all();
        $customers = User::where('role', 'Customer')->get();

        return view('sales.edit', compact('sale', 'products', 'warehouses', 'customers'));
    }

    /**
     * ===========================
     * UPDATE SALE
     * ===========================
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'nullable|exists:users,id',
            'sale_status' => 'required|in:completed,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validated['customer_id']) {
            $customer = User::find($validated['customer_id']);
            if ($customer->role !== 'Customer') {
                return back()->with('error', 'Selected user is not a customer!');
            }
        }

        DB::beginTransaction();
        try {
            $sale->update($validated);
            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
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
            // Restore stock if sale was completed
            foreach ($sale->items as $item) {
                if ($sale->sale_status === 'completed') {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            $sale->delete();
            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete sale: ' . $e->getMessage());
        }
    }
}
