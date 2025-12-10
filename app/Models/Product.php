<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Final merged fillable (ONLY ONE!)
    protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
        'warehouse_id',
        'name',
        'product_code',
        'description',
        'price',
        'cost_price',
        'stock',
        'image'
    ];

    /* ============================
        Relationships
    ============================ */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productWarehouses()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /* ============================
        Helpers
    ============================ */

    // Get stock for specific warehouse
    public function getWarehouseStock($warehouseId)
    {
        return ProductWarehouse::getStock($this->id, $warehouseId);
    }

    // Generate unique product code
    public static function generateProductCode()
    {
        do {
            $code = 'PRD-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('product_code', $code)->exists());
        
        return $code;
    }
}
