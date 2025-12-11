<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\ProductWarehouse;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /** ===========================
     * PURCHASE LIST
     * =========================== */
    public function index()
    {
        $purchases  = Purchase::with(['warehouse','supplier','creator'])
                        ->latest()
                        ->paginate(20);

        return view('purchases.index', [
            'purchases'  => $purchases,
            'warehouses' => Warehouse::all(),
            'suppliers'  => Supplier::all(),
        ]);
    }

    /** ===========================
     * CREATE PAGE
     * =========================== */
    public function create()
    {
        return view('purchases.create', [
            'warehouses'  => Warehouse::all(),
            'suppliers'   => Supplier::all(),
            'products'    => Product::all(),
            'referenceNo' => Purchase::generateReferenceNo(), // যদি method না থাকে তা model-এ add করে নিও
        ]);
    }

    /** ===========================
     * EDIT PURCHASE
     * =========================== */
    public function edit(Purchase $purchase)
    {
        $purchase->load(['items.product','warehouse','supplier']);

        return view('purchases.edit', [
            'purchase'   => $purchase,
            'warehouses' => Warehouse::all(),
            'suppliers'  => Supplier::all(),
            'products'   => Product::all(),
        ]);
    }

    /** ===========================
     * UPDATE PURCHASE
     * =========================== */
    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'purchase_date'=> 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $purchase->update([
            'warehouse_id'  => $request->warehouse_id,
            'supplier_id'   => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
            'notes'         => $request->notes,
        ]);

        return redirect()->route('purchases.index')
                ->with('success', 'Purchase updated successfully!');
    }

    /** ===========================
     * STORE PURCHASE
     * =========================== */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id'   => 'required|exists:warehouses,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'purchase_date'  => 'required|date',
            'reference_no'   => 'required|unique:purchases,reference_no',
            'products'       => 'required|array|min:1',
            'payment_method' => 'required|string',
            'payment_status' => 'required|in:pending,partial,paid',
            'amount_paid'    => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            /** CALCULATE SUBTOTAL */
            $subtotal = 0;
            foreach ($request->products as $item) {
                $subtotal += ($item['quantity'] * $item['cost_price'])
                           - ($item['discount'] ?? 0)
                           + ($item['tax'] ?? 0);
            }

            $taxPercentage  = floatval($request->tax_percentage ?? 0);
            $taxAmount      = ($subtotal * $taxPercentage) / 100;
            $discountAmount = floatval($request->discount_value ?? 0);
            $shippingCost   = floatval($request->shipping_cost ?? 0);

            $grandTotal = $subtotal + $taxAmount - $discountAmount + $shippingCost;

            /** PAYMENT HANDLING */
            $amountPaid = floatval($request->amount_paid ?? 0);
            $dueAmount  = $grandTotal - $amountPaid;

            if ($amountPaid >= $grandTotal)     $paymentStatus = 'paid';
            elseif ($amountPaid > 0)            $paymentStatus = 'partial';
            else                                 $paymentStatus = 'pending';

            /** DOCUMENT UPLOAD */
            $documentPath = null;
            if ($request->hasFile('document')) {
                $file      = $request->file('document');
                $filename  = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/purchases'), $filename);
                $documentPath = 'uploads/purchases/' . $filename;
            }

            /** CREATE PURCHASE */
            $purchase = Purchase::create([
                'purchase_date'   => $request->purchase_date,  // FIXED HERE
                'reference_no'    => $request->reference_no,
                'warehouse_id'    => $request->warehouse_id,
                'supplier_id'     => $request->supplier_id,
                'status'          => $request->purchase_status ?? 'received',

                'tax_percentage'  => $taxPercentage,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_cost'   => $shippingCost,
                'grand_total'     => $grandTotal,

                'payment_method'  => $request->payment_method,
                'payment_status'  => $paymentStatus,
                'paid_amount'     => $amountPaid,
                'due_amount'      => $dueAmount,

                'notes'           => $request->notes,
                'document'        => $documentPath,
                'currency'        => $request->currency ?? 'BDT',
                'exchange_rate'   => $request->exchange_rate ?? 1,

                'created_by'      => auth()->id(),
            ]);

            /** ADD ITEMS AND STOCK UPDATE */
            foreach ($request->products as $item) {

                $batchId = $item['batch_id']
                    ?: 'BATCH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'cost_price'  => $item['cost_price'],
                    'discount'    => $item['discount'] ?? 0,
                    'tax'         => $item['tax'] ?? 0,
                    'batch_id'    => $batchId,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                /** STOCK UPDATE */
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('stock', $item['quantity']);
                    $product->cost_price = $item['cost_price'];
                    $product->save();
                }
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Purchase created successfully!',
                'purchase_id'   => $purchase->reference_no,
                'payment_status'=> $paymentStatus,
                'paid_amount'   => $amountPaid,
                'due_amount'    => $dueAmount,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            \Log::error("Purchase Create Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** ===========================
     * SHOW PURCHASE
     * =========================== */
    public function show(Purchase $purchase)
    {
        $purchase->load(['warehouse','supplier','items.product','creator']);
        return view('purchases.show', compact('purchase'));
    }

    /** ===========================
     * DELETE PURCHASE
     * =========================== */
    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();

        try {

            foreach ($purchase->items as $item) {
                Product::find($item->product_id)
                        ->decrement('stock', $item->quantity);

                ProductWarehouse::where('purchase_id', $purchase->id)
                                ->where('product_id', $item->product_id)
                                ->delete();
            }

            if ($purchase->document) {
                @unlink(public_path($purchase->document));
            }

            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                    ->with('success', 'Purchase deleted successfully!');

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /** ===========================
     * PRODUCT DETAILS (AJAX)
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
                'id'            => $product->id,
                'name'          => $product->name,
                'code'          => $product->product_code ?? 'N/A',
                'cost_price'    => $product->cost_price ?? 0,
                'current_stock' => $product->stock,
                'unit'          => optional($product->unit)->name ?? 'pc'
            ]
        ]);
    }
}
