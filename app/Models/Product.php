<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'price', 'stock', 'image'];

    // Relationship: A product belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship: A product has many order items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function warehouse()
{
    return $this->belongsTo(Warehouse::class);
}

public function productWarehouses()
{
    return $this->hasMany(ProductWarehouse::class);
}

// Get stock in specific warehouse
public function getWarehouseStock($warehouseId)
{
    return ProductWarehouse::getStock($this->id, $warehouseId);
}
}