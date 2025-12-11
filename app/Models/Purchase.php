<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_date',
        'reference_no',
        'warehouse_id',
        'supplier_id',
        'status',
        'tax_percentage',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'grand_total',
        'payment_method',      // CRITICAL
        'payment_status',      // CRITICAL
        'paid_amount',         // CRITICAL
        'due_amount',          // CRITICAL
        'currency',
        'exchange_rate',
        'notes',
        'document',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Generate Reference Number
    public static function generateReferenceNo()
    {
        $date = date('Ymd');
        $lastPurchase = self::whereDate('created_at', today())->latest()->first();
        $number = $lastPurchase ? intval(substr($lastPurchase->reference_no, -4)) + 1 : 1;
        return 'PUR-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}