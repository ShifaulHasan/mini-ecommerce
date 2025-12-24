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
        'customer_id',     // 🔥 customers table ID
        'warehouse_id',
        'account_id',

        'biller',
        'sale_status',
        'payment_status',
        'payment_method',
        'sale_type',
        'delivery_status',

        // Amounts
        'grand_total',
        'returned_amount',
        'paid_amount',
        'due_amount',

        // Currency
        'currency',
        'exchange_rate',

        // Others
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

        'grand_total'     => 'decimal:2',
        'returned_amount' => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',

        'exchange_rate'   => 'decimal:4',
    ];

    /**
     * ========================
     * Relationships
     * ========================
     */

    /**
     * Sale creator (logged-in user)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 🔥 Customer relationship
     * sales.customer_id → customers.id
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Sale items
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    /**
     * Alias (safe)
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    /**
     * Account used for sale
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Account transactions linked with this sale
     */
    public function accountTransactions()
    {
        return $this->hasMany(AccountTransaction::class, 'reference_id')
            ->where('reference_type', 'sale');
    }

    /**
     * ========================
     * Reference Number Generator
     * ========================
     * Example: SAL-20251217-0001
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
