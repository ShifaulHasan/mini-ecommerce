<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
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

    public function create()
    {
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        $products = Product::where('stock', '>', 0)->get();
        
        return view('sales.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        // Implementation here
    }

    public function show(Sale $sale)
    {
        $sale->load('customer', 'saleItems.product');
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully!');
    }
   


    
}