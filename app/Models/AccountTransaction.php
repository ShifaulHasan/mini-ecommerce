<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransaction extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'account_transactions';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'account_id',
        'reference_type',     // sale, purchase, expense etc
        'reference_id',
        'transaction_type',   // credit / debit
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'transaction_date',
        'created_by',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'amount'          => 'decimal:2',
        'balance_before'  => 'decimal:2',
        'balance_after'   => 'decimal:2',
        'transaction_date'=> 'date',
    ];

    /**
     * =========================
     * Relationships
     * =========================
     */

    /**
     * Transaction belongs to an account
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Transaction created by user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * =========================
     * Reference relationship (polymorphic-like)
     * =========================
     */

    /**
     * Get related reference model (sale, purchase, etc.)
     */
    public function reference()
    {
        switch ($this->reference_type) {
            case 'sale':
                return $this->belongsTo(Sale::class, 'reference_id');
            case 'purchase':
                return $this->belongsTo(Purchase::class, 'reference_id');
            case 'expense':
                return $this->belongsTo(Expense::class, 'reference_id');
            default:
                return null;
        }
    }

    /**
     * =========================
     * Helpers
     * =========================
     */

    /**
     * Check if credit transaction
     */
    public function isCredit()
    {
        return $this->transaction_type === 'credit';
    }

    /**
     * Check if debit transaction
     */
    public function isDebit()
    {
        return $this->transaction_type === 'debit';
    }
}
