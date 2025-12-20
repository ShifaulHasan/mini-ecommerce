<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'account_no',
        'name',
        'branch',
        'swift_code',
        'initial_balance',
        'current_balance',
        'is_default',
        'note',
        'status',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_default'      => 'boolean',
    ];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // On create
        static::creating(function ($account) {

            // Auto-generate account number
            if (empty($account->account_no)) {
                $account->account_no = self::generateAccountNumber();
            }

            // Set current balance initially
            if (is_null($account->current_balance)) {
                $account->current_balance = $account->initial_balance ?? 0;
            }
        });

        // On save (create/update)
        static::saving(function ($account) {

            // Ensure only one default account
            if ($account->is_default) {
                self::where('id', '!=', $account->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Generate sequential account number (ACC-000001)
     */
    public static function generateAccountNumber()
    {
        $lastAccount = self::orderByDesc('id')->first();

        if (!$lastAccount || !str_starts_with($lastAccount->account_no, 'ACC-')) {
            return 'ACC-000001';
        }

        $lastNumber = (int) substr($lastAccount->account_no, 4);

        return 'ACC-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * =========================
     * Relationships
     * =========================
     */

    /**
     * Account has many transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * =========================
     * Accessors & Helpers
     * =========================
     */

    /**
     * Calculate current balance dynamically
     */
    public function getCalculatedBalanceAttribute()
    {
        $credits = $this->transactions()
            ->where('type', 'credit')
            ->sum('amount');

        $debits = $this->transactions()
            ->where('type', 'debit')
            ->sum('amount');

        return ($this->initial_balance ?? 0) + $credits - $debits;
    }

    /**
     * Get balance up to a specific date
     */
    public function getBalanceOnDate($date)
    {
        $credits = $this->transactions()
            ->where('type', 'credit')
            ->whereDate('date', '<=', $date)
            ->sum('amount');

        $debits = $this->transactions()
            ->where('type', 'debit')
            ->whereDate('date', '<=', $date)
            ->sum('amount');

        return ($this->initial_balance ?? 0) + $credits - $debits;
    }

    /**
     * =========================
     * Scopes
     * =========================
     */

    /**
     * Active accounts only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Default account
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
