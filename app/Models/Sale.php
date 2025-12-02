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
        'notes'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public static function generateReferenceNumber()
    {
        $year = date('Y');
        $lastSale = self::whereYear('created_at', $year)->latest()->first();
        
        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->reference_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return 'SALE-' . $year . '-' . $newNumber;
    }
}