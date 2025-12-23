<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'account_id',
        'sale_id',
        'date',
        'type',          // credit | debit
        'amount',
        'description',
        'reference',
        'payment_method',
        'category',
        'created_by',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relations
     */

    // Transaction belongs to an Account
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Transaction may belong to a Sale
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Transaction created by User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */

    // Only credit transactions
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    // Only debit transactions
    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    // Filter by date range
    public function scopeDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        return $query;
    }
}
