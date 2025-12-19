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
        'initial_balance'  => 'decimal:2',
        'current_balance'  => 'decimal:2',
        'is_default'       => 'boolean',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new account
        static::creating(function ($account) {

            // Generate account number if missing
            if (empty($account->account_no)) {
                $account->account_no = self::generateAccountNumber();
            }

            // Set current balance equal to initial balance
            if (empty($account->current_balance)) {
                $account->current_balance = $account->initial_balance ?? 0;
            }
        });

        // When saving (create or update)
        static::saving(function ($account) {

            // Ensure only one default account exists
            if ($account->is_default) {
                self::where('id', '!=', $account->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Generate sequential account number
     */
    public static function generateAccountNumber()
    {
        $lastAccount = self::orderBy('id', 'desc')->first();

        if (!$lastAccount || !str_starts_with($lastAccount->account_no, 'ACC-')) {
            return 'ACC-000001';
        }

        $lastNumber = (int) substr($lastAccount->account_no, 4);
        $newNumber  = $lastNumber + 1;

        return 'ACC-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: Active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Default account
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
