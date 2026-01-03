<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * ========================
     * Mass Assignable Fields
     * ========================
     */
    protected $fillable = [
        'reference_number',

        'sale_date',
        'customer_id',
        'warehouse_id',
        'account_id',

        'biller',
        'sale_status',
        'payment_status',
        'payment_method',
        'sale_type',
        'delivery_status',

        // Amounts
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'grand_total',
        'returned_amount',
        'paid_amount',
        'amount_paid',
        'due_amount',
        'amount_due',

        // Currency
        'currency',
        'exchange_rate',

        // Others
        'document',
        'notes',
        'created_by',
    ];

    /**
     * ========================
     * Casts
     * ========================
     */
    protected $casts = [
        'sale_date'       => 'date',

        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'returned_amount' => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
        'amount_due'      => 'decimal:2',

        'exchange_rate'   => 'decimal:4',
    ];

    /**
     * ========================
     * Relationships
     * ========================
     */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function accountTransactions()
    {
        return $this->hasMany(AccountTransaction::class, 'reference_id')
            ->where('reference_type', 'sale');
    }

    /**
     * ========================
     * Reference Number Generator
     * ========================
     */
    public static function generateReferenceNo()
    {
        $date = now()->format('Ymd');

        $lastSale = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $number = $lastSale
            ? ((int) substr($lastSale->reference_number, -4)) + 1
            : 1;

        return 'SAL-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}