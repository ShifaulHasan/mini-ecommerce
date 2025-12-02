<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'warehouse']);
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }
        
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by purchase status
        if ($request->filled('purchase_status')) {
            $query->where('purchase_status', $request->purchase_status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // Get per page value
        $perPage = $request->get('per_page', 10);
        
        $purchases = $query->latest()->paginate($perPage)->appends($request->except('page'));
        
        // Get filter options
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();
        
        return view('purchases.index', compact('purchases', 'warehouses', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $products = Product::all();
        $referenceNumber = Purchase::generateReferenceNumber();
        
        return view('purchases.create', compact('suppliers', 'warehouses', 'products', 'referenceNumber'));
    }

   public function store(Request $request)
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'purchase_date' => 'required|date',
        'products' => 'required|array',
        'products.*.product_id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'products.*.price' => 'required|numeric|min:0'
    ]);

    DB::beginTransaction();
    try {
        // Calculate totals
        $grandTotal = 0;
        foreach ($request->products as $product) {
            $subtotal = $product['quantity'] * $product['price']; // Calculate subtotal
            $grandTotal += $subtotal;
        }
        
        $paidAmount = $request->paid_amount ?? 0;
        $dueAmount = $grandTotal - $paidAmount;
        
        // Determine payment status
        $paymentStatus = 'unpaid';
        if ($paidAmount >= $grandTotal) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        }

        // Create purchase
        $purchase = Purchase::create([
            'reference_number' => Purchase::generateReferenceNumber(),
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'purchase_date' => $request->purchase_date,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'purchase_status' => $request->purchase_status ?? 'pending',
            'payment_status' => $paymentStatus,
            'notes' => $request->notes
        ]);

        // Create purchase items
        foreach ($request->products as $product) {
            $subtotal = $product['quantity'] * $product['price'];
            
            // THIS IS THE FIX - Make sure subtotal is included
            $purchase->purchaseItems()->create([
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'subtotal' => $subtotal  // <-- THIS WAS MISSING
            ]);

            // Update product stock if status is received
            if ($request->purchase_status == 'received') {
                Product::find($product['product_id'])->increment('stock', $product['quantity']);
            }
        }

        DB::commit();
        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage())->withInput();
    }
}

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'warehouse', 'purchaseItems.product');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $products = Product::all();
        $purchase->load('purchaseItems');
        
        return view('purchases.edit', compact('purchase', 'suppliers', 'warehouses', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'purchase_status' => 'required|in:pending,received,cancelled',
            'paid_amount' => 'nullable|numeric|min:0'
        ]);

        $paidAmount = $request->paid_amount ?? $purchase->paid_amount;
        $dueAmount = $purchase->grand_total - $paidAmount;
        
        $paymentStatus = 'unpaid';
        if ($paidAmount >= $purchase->grand_total) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        }

        $purchase->update([
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'purchase_date' => $request->purchase_date,
            'purchase_status' => $request->purchase_status,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'notes' => $request->notes
        ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            // Restore stock if purchase was received
            if ($purchase->purchase_status == 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }
            
            $purchase->delete();
            DB::commit();
            
            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete purchase');
        }
    }

    // Export functions
    public function export(Request $request)
    {
        // This will be implemented with Laravel Excel package
        // For now, return JSON
        $purchases = Purchase::with(['supplier', 'warehouse'])->get();
        return response()->json($purchases);
    }
}