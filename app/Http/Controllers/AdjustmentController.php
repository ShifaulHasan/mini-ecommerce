<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdjustmentController extends Controller
{
    // Adjustment list
    public function index()
    {
        $adjustments = Adjustment::with(['warehouse', 'product', 'creator'])
                                 ->latest()
                                 ->paginate(20);
        return view('adjustments.index', compact('adjustments'));
    }

    // Add adjustment form
    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('adjustments.create', compact('warehouses', 'products'));
    }

    // Store adjustment
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:addition,subtraction',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Current stock
            $currentStock = ProductWarehouse::where('product_id', $request->product_id)
                                            ->where('warehouse_id', $request->warehouse_id)
                                            ->sum('quantity');

            // New stock calculation
            if ($request->adjustment_type == 'addition') {
                $newStock = $currentStock + $request->quantity;
            } else {
                if ($currentStock < $request->quantity) {
                    return back()->with('error', "Insufficient stock! Current stock: {$currentStock}")
                                ->withInput();
                }
                $newStock = $currentStock - $request->quantity;
            }

            // Create adjustment record
            Adjustment::create([
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $request->product_id,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $request->quantity,
                'current_stock' => $currentStock,
                'new_stock' => $newStock,
                'reason' => $request->reason,
                'created_by' => Auth::id()
            ]);

            // Update ProductWarehouse
            if ($request->adjustment_type == 'addition') {
                $existing = ProductWarehouse::where('product_id', $request->product_id)
                                            ->where('warehouse_id', $request->warehouse_id)
                                            ->first();
                if ($existing) {
                    $existing->increment('quantity', $request->quantity);
                } else {
                    ProductWarehouse::create([
                        'product_id' => $request->product_id,
                        'warehouse_id' => $request->warehouse_id,
                        'batch_id' => 'ADJ-' . time(),
                        'quantity' => $request->quantity
                    ]);
                }
            } else {
                // Subtraction FIFO
                $remaining = $request->quantity;
                $batches = ProductWarehouse::where('product_id', $request->product_id)
                                          ->where('warehouse_id', $request->warehouse_id)
                                          ->where('quantity', '>', 0)
                                          ->oldest()
                                          ->get();
                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;
                    if ($batch->quantity >= $remaining) {
                        $batch->decrement('quantity', $remaining);
                        $remaining = 0;
                    } else {
                        $remaining -= $batch->quantity;
                        $batch->update(['quantity' => 0]);
                    }
                }
            }

            // Update main product stock
            $product = Product::find($request->product_id);
            if ($request->adjustment_type == 'addition') {
                $product->increment('stock', $request->quantity);
            } else {
                $product->decrement('stock', $request->quantity);
            }

            DB::commit();
            return redirect()->route('adjustments.index')
                             ->with('success', 'Stock adjustment completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    // Delete adjustment
    public function destroy($id)
    {
        $adjustment = Adjustment::findOrFail($id);
        $adjustment->delete(); // Model boot will handle stock sync
        return redirect()->route('adjustments.index')
                         ->with('success', 'Adjustment deleted and stock updated!');
    }

    // AJAX: Get stock
    public function getStock(Request $request)
    {
        $stock = ProductWarehouse::where('product_id', $request->product_id)
                                 ->where('warehouse_id', $request->warehouse_id)
                                 ->sum('quantity');

        $product = Product::find($request->product_id);

        return response()->json([
            'success' => true,
            'stock' => $stock,
            'product_name' => $product->name ?? 'Unknown',
            'product_code' => $product->product_code ?? 'N/A'
        ]);
    }
}
