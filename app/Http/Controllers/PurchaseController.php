<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
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

        return view('purchases.create', compact('referenceNo', 'products', 'warehouses', 'suppliers'));
    }

    /** ===========================
     * STORE PURCHASE
     * =========================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'reference_no' => 'required|unique:purchases,reference_no',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:users,id',
            'purchase_status' => 'required|in:received,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'grand_total' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'items' => 'required|array|min:1',
        ]);

        // Verify supplier role
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

        DB::beginTransaction();
        try {
            $purchase = Purchase::create($validated);

            // Store items and update product stock if received
            foreach ($request->items as $item) {
                $purchase->items()->create($item);

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

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase Store Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create purchase: ' . $e->getMessage());
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

        return view('purchases.edit', compact('purchase', 'products', 'warehouses', 'suppliers'));
    }

    /** ===========================
     * UPDATE PURCHASE
     * =========================== */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:users,id',
            'purchase_status' => 'required|in:received,pending',
            'payment_status' => 'required|in:paid,partial,pending',
            'payment_method' => 'nullable|string',
            'grand_total' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'items' => 'required|array|min:1',
        ]);

        if ($validated['supplier_id']) {
            $supplier = User::find($validated['supplier_id']);
            if (!$supplier || $supplier->role !== 'Supplier') {
                return back()->with('error', 'Selected user is not a supplier!');
            }
        }

        // Handle document upload
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/purchases'), $filename);
            $validated['document'] = 'uploads/purchases/' . $filename;
        }

        DB::beginTransaction();
        try {
            $purchase->update($validated);

            // Delete old items and add new
            $purchase->items()->delete();
            foreach ($request->items as $item) {
                $purchase->items()->create($item);

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
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('stock', $item->quantity);
                }
            }

            if ($purchase->document) {
                @unlink(public_path($purchase->document));
            }

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
            'success'=>true,
            'product'=>[
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->product_code ?? 'N/A',
                'cost_price' => $product->cost_price ?? 0,
                'current_stock' => $product->stock,
                'unit' => optional($product->unit)->name ?? 'pc',
            ]
        ]);
    }
}
