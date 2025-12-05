<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleReturn::with(['sale.customer']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('sale', function($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }
        
        $perPage = $request->get('per_page', 10);
        $saleReturns = $query->latest()->paginate($perPage)->appends($request->except('page'));
        
        return view('sale-returns.index', compact('saleReturns'));
    }

    public function create()
    {
        $sales = Sale::where('sale_status', 'completed')
                    ->with('customer', 'saleItems.product')
                    ->get();
        
        return view('sale-returns.create', compact('sales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($request->products as $product) {
                $totalAmount += $product['quantity'] * $product['price'];
            }

            $saleReturn = SaleReturn::create([
                'sale_id' => $request->sale_id,
                'return_date' => $request->return_date,
                'total_amount' => $totalAmount,
                'reason' => $request->reason
            ]);

            foreach ($request->products as $product) {
                $saleReturn->saleReturnItems()->create([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price']
                ]);

                // Restore stock
                Product::find($product['product_id'])->increment('stock', $product['quantity']);
            }

            // Update sale returned amount
            $sale = Sale::find($request->sale_id);
            $sale->increment('returned_amount', $totalAmount);
            $sale->decrement('paid_amount', $totalAmount);
            $sale->update(['due_amount' => $sale->grand_total - $sale->paid_amount]);

            DB::commit();
            return redirect()->route('sale-returns.index')->with('success', 'Sale return created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load('sale.customer', 'saleReturnItems.product');
        return view('sale-returns.show', compact('saleReturn'));
    }

    public function destroy(SaleReturn $saleReturn)
    {
        DB::beginTransaction();
        try {
            // Deduct stock back
            foreach ($saleReturn->saleReturnItems as $item) {
                $item->product->decrement('stock', $item->quantity);
            }

            // Update sale returned amount
            $sale = $saleReturn->sale;
            $sale->decrement('returned_amount', $saleReturn->total_amount);
            $sale->increment('paid_amount', $saleReturn->total_amount);
            $sale->update(['due_amount' => $sale->grand_total - $sale->paid_amount]);

            $saleReturn->delete();
            
            DB::commit();
            return redirect()->route('sale-returns.index')->with('success', 'Sale return deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete sale return');
        }
    }

    // AJAX endpoint to get sale items
    public function getSaleItems($saleId)
    {
        $sale = Sale::with('saleItems.product')->find($saleId);
        
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        return response()->json([
            'success' => true,
            'customer' => $sale->customer ? $sale->customer->name : 'Walk-in Customer',
            'reference' => $sale->reference_number,
            'items' => $sale->saleItems->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity_sold' => $item->quantity,
                    'price' => $item->price
                ];
            })
        ]);
    }
}