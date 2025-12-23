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

        'biller',              // 🔥 CRITICAL: This must be here!
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
        'sale_date'        => 'date',

        'grand_total'      => 'decimal:2',
        'returned_amount'  => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'due_amount'       => 'decimal:2',

        'exchange_rate'    => 'decimal:4',
    ];

    /**
     * ========================
     * Relationships
     * ========================
     */

    // Sale creator (logged-in user)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Customer (User table with role = customer)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Sale items
     * Controller compatibility ensured
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    // Alias (optional but safe)
    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function account()
{
    return $this->belongsTo(Account::class);
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
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastSale
            ? intval(substr($lastSale->reference_number, -4)) + 1
            : 1;

        return 'SAL-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    /**
 * Sale has account transactions
 */
public function accountTransactions()
{
    return $this->hasMany(AccountTransaction::class, 'reference_id')
        ->where('reference_type', 'sale');
}
    
}