<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    protected $table = 'product_warehouse';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'expiry_date',
        'quantity',
        'purchase_id'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // Generate unique batch ID
    public static function generateBatchId()
    {
        return 'BATCH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // Get total stock for a product in a warehouse
    public static function getStock($productId, $warehouseId)
    {
        return self::where('product_id', $productId)
                   ->where('warehouse_id', $warehouseId)
                   ->sum('quantity');
    }
}
