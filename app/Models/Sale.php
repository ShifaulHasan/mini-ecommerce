<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'reference_number',
        'customer_id',
        'warehouse_id',
        'biller',
        'sale_date',
        'grand_total',
        'returned_amount',
        'paid_amount',
        'due_amount',
        'sale_status',
        'payment_status',
        'payment_method',
        'sale_type',
        'delivery_status',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'grand_total' => 'decimal:2',
        'returned_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}