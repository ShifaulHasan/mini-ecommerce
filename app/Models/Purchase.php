<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'reference_number',
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'grand_total',
        'returned_amount',
        'paid_amount',
        'due_amount',
        'purchase_status',
        'payment_status',
        'notes'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Generate unique reference number
    public static function generateReferenceNumber()
    {
        $year = date('Y');
        $lastPurchase = self::whereYear('created_at', $year)->latest()->first();
        
        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->reference_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return 'PUR-' . $year . '-' . $newNumber;
    }
}