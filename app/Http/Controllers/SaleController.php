<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer']);

        // Filters
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('sale_status')) {
            $query->where('sale_status', $request->sale_status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 10);
        $sales = $query->latest()->paginate($perPage)->appends($request->except('page'));

        $warehouses = Warehouse::all();
        $customers = Customer::all();

        return view('sales.index', compact('sales', 'warehouses', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::all();
        $warehouses = Warehouse::all();

        // Get products filtered by warehouse if selected
        $warehouseId = $request->get('warehouse_id');

        if ($warehouseId) {
            $products = Product::whereHas('productWarehouses', function($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                      ->where('quantity', '>', 0);
            })->get();
        } else {
            $products = Product::where('stock', '>', 0)->get();
        }

        return view('sales.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,bkash,nagad,cheque',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $grandTotal = 0;
            foreach ($request->products as $product) {
                $grandTotal += $product['quantity'] * $product['price'];
            }

            $paidAmount = $request->paid_amount ?? 0;
            $dueAmount = $grandTotal - $paidAmount;

            $paymentStatus = 'unpaid';
            if ($paidAmount >= $grandTotal) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            // Create sale
            $sale = Sale::create([
                'reference_number' => Sale::generateReferenceNumber(),
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'sale_date' => $request->sale_date,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'sale_status' => $request->sale_status ?? 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create sale items and update stock
            foreach ($request->products as $product) {
                $subtotal = $product['quantity'] * $product['price'];

                $sale->saleItems()->create([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'subtotal' => $subtotal
                ]);

                // Reduce stock in main product
                Product::find($product['product_id'])->decrement('stock', $product['quantity']);

                // Reduce stock in warehouse
                ProductWarehouse::where('product_id', $product['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->decrement('quantity', $product['quantity']);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load('customer', 'saleItems.product');
        return view('sales.show', compact('sale'));
    }
     // ==============================
    // 🔥 NEW: EDIT SALE
    // ==============================
    public function edit($id)
    {
        $sale = Sale::with(['saleItems.product', 'customer'])->findOrFail($id);
        $customers = Customer::all();
        $warehouses = Warehouse::all();

        return view('sales.edit', compact('sale', 'customers', 'warehouses'));
    }

    // ==============================
    // 🔥 NEW: UPDATE SALE
    // ==============================
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($id);

            $sale->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'sale_date' => $request->sale_date,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        try {
            // Restore stock when deleting sale
            foreach ($sale->saleItems as $item) {
                Product::find($item->product_id)->increment('stock', $item->quantity);

                ProductWarehouse::where('product_id', $item->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->increment('quantity', $item->quantity);
            }

            $sale->delete();
            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete sale');
        }
    }

    public function getWarehouseProducts(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        $products = Product::whereHas('productWarehouses', function($query) use ($warehouseId) {
            $query->where('warehouse_id', $warehouseId)
                  ->where('quantity', '>', 0);
        })
        ->with(['productWarehouses' => function($query) use ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }])
        ->get()
        ->map(function($product) use ($warehouseId) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => ProductWarehouse::getStock($product->id, $warehouseId)
            ];
        });

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }
}