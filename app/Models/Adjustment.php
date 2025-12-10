<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Adjustment extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'adjustment_type',
        'quantity',
        'current_stock',
        'new_stock',
        'reason',
        'created_by'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Stock sync on delete
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($adjustment) {
            $product = $adjustment->product;
            $warehouseId = $adjustment->warehouse_id;
            $qty = $adjustment->quantity;

            if ($adjustment->adjustment_type == 'addition') {
                // Addition delete হলে stock কমানো
                $product->decrement('stock', $qty);

                // ProductWarehouse থেকে কমানো (FIFO reverse)
                $remaining = $qty;
                $batches = ProductWarehouse::where('product_id', $product->id)
                                           ->where('warehouse_id', $warehouseId)
                                           ->where('quantity', '>', 0)
                                           ->latest()
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
            } else {
                // Subtraction delete হলে stock বাড়ানো
                $product->increment('stock', $qty);

                // ProductWarehouse-এ restore করা
                ProductWarehouse::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'batch_id' => 'ADJ-RESTORE-' . time(),
                    'quantity' => $qty
                ]);
            }
        });
    }
}
