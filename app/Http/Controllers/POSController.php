<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('stock', '>', 0);
        
        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('id', $request->search);
        }
        
        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        $products = $query->get();
        $categories = Category::all();
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        
        return view('pos.index', compact('products', 'categories', 'customers', 'warehouses'));
    }

    public function completeSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment',
            'paid_amount' => 'required|numeric|min:0',
            'cart' => 'required|array',
            'total' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $total = $request->total;
            $paidAmount = $request->paid_amount;
            $dueAmount = $total - $paidAmount;
            
            $paymentStatus = 'unpaid';
            if ($paidAmount >= $total) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $sale = Sale::create([
                'reference_number' => Sale::generateReferenceNumber(),
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'biller' => Auth::user()->name,
                'sale_date' => now()->format('Y-m-d'),
                'grand_total' => $total,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'sale_status' => 'completed',
                'payment_status' => $paymentStatus,
                'payment_method' => $request->payment_method,
                'shipping_cost' => 0,
                'order_tax' => 0,
                'order_discount' => 0
            ]);

            foreach ($request->cart as $item) {
                $product = Product::find($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price']
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'sale_id' => $sale->id,
                'reference' => $sale->reference_number
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}