<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'reference_type',
        'reference_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'transaction_date',
        'payment_method',
        'created_by',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'amount'           => 'decimal:2',
        'balance_before'   => 'decimal:2',
        'balance_after'    => 'decimal:2',
        'transaction_date' => 'date',
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
     * Get the reference model dynamically
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . str_replace('_', '', ucwords($this->reference_type, '_'));
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->reference_id);
        }
        
        return null;
    }

    /**
     * =========================
     * Query Scopes
     * =========================
     */

    /**
     * Scope for credit transactions
     */
    public function scopeCredit($query)
    {
        return $query->where('transaction_type', 'credit');
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebit($query)
    {
        return $query->where('transaction_type', 'debit');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific account
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope for specific reference
     */
    public function scopeForReference($query, $referenceType, $referenceId)
    {
        return $query->where('reference_type', $referenceType)
                     ->where('reference_id', $referenceId);
    }

    /**
     * Scope for today's transactions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * Scope for this month's transactions
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('transaction_date', now()->year)
                     ->whereMonth('transaction_date', now()->month);
    }

    /**
     * =========================
     * Helper Methods
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

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance_after, 2);
    }

    /**
     * =========================
     * Static Methods for Creating Transactions
     * =========================
     */

    /**
     * Create a new transaction with automatic balance calculation
     */
    public static function createTransaction(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Get current balance for the account
            $currentBalance = self::getCurrentBalance($data['account_id']);
            
            // Calculate new balance
            $balanceBefore = $currentBalance;
            $balanceAfter = $data['transaction_type'] === 'credit' 
                ? $currentBalance + $data['amount']
                : $currentBalance - $data['amount'];
            
            // Create the transaction
            $transaction = self::create([
                'account_id'       => $data['account_id'],
                'reference_type'   => $data['reference_type'] ?? null,
                'reference_id'     => $data['reference_id'] ?? null,
                'transaction_type' => $data['transaction_type'],
                'amount'           => $data['amount'],
                'balance_before'   => $balanceBefore,
                'balance_after'    => $balanceAfter,
                'description'      => $data['description'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'payment_method'   => $data['payment_method'] ?? null,
                'created_by'       => $data['created_by'] ?? auth()->id(),
            ]);
            
            DB::commit();
            
            return $transaction;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a credit transaction (money in)
     */
    public static function createCredit(array $data)
    {
        $data['transaction_type'] = 'credit';
        return self::createTransaction($data);
    }

    /**
     * Create a debit transaction (money out)
     */
    public static function createDebit(array $data)
    {
        $data['transaction_type'] = 'debit';
        return self::createTransaction($data);
    }

    /**
     * Get current balance for an account
     */
    public static function getCurrentBalance($accountId)
    {
        $lastTransaction = self::where('account_id', $accountId)
            ->orderBy('id', 'desc')
            ->first();
        
        return $lastTransaction ? $lastTransaction->balance_after : 0;
    }

    /**
     * =========================
     * Static Query Methods
     * =========================
     */

    /**
     * Get account statement
     */
    public static function getAccountStatement($accountId, $startDate = null, $endDate = null)
    {
        $query = self::where('account_id', $accountId)
            ->with(['creator'])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        
        return $query->get();
    }

    /**
     * Get transaction summary for an account
     */
    public static function getAccountSummary($accountId, $startDate = null, $endDate = null)
    {
        $query = self::where('account_id', $accountId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        
        return [
            'total_credits' => $query->clone()->where('transaction_type', 'credit')->sum('amount'),
            'total_debits'  => $query->clone()->where('transaction_type', 'debit')->sum('amount'),
            'transaction_count' => $query->count(),
            'current_balance' => self::getCurrentBalance($accountId),
        ];
    }

    /**
     * Get monthly summary
     */
    public static function getMonthlySummary($accountId, $year, $month)
    {
        return self::where('account_id', $accountId)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->selectRaw('
                transaction_type,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->groupBy('transaction_type')
            ->get();
    }
}