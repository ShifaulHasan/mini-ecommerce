<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * ========================
     * Mass Assignable Fields
     * ========================
     */
    protected $fillable = [
        'reference_no',
        'purchase_date',

        'warehouse_id',
        'supplier_id',

        'status',

        // Tax & Discount
        'tax_percentage',
        'tax_amount',
        'discount_amount',
        'shipping_cost',

        // Payment
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'account_id', // ✅ linked account

        // Currency
        'currency',
        'exchange_rate',

        // Others
        'notes',
        'document',
        'created_by',
    ];

    /**
     * ========================
     * Casts
     * ========================
     */
    protected $casts = [
        'purchase_date'   => 'date',

        'tax_percentage'  => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost'   => 'decimal:2',

        'grand_total'     => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',

        'exchange_rate'   => 'decimal:4',
    ];

    /**
     * ========================
     * Relationships
     * ========================
     */

    // Purchase creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Supplier
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    // Warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Purchase items
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // ✅ Account used for payment
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * ========================
     * Reference Number Generator
     * ========================
     * Example: PUR-20251217-0001
     */
    public static function generateReferenceNo()
    {
        $date = now()->format('Ymd');

        $lastPurchase = self::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        $number = $lastPurchase
            ? intval(substr($lastPurchase->reference_no, -4)) + 1
            : 1;

        return 'PUR-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
