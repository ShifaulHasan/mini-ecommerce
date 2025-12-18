<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Sale relationship
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    // Product relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
