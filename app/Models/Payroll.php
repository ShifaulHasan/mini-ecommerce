<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_reference',
        'employee_id',
        'account_id',
        'amount',
        'payment_method',
        'payment_date',
        'note',
        'is_approve',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate payroll reference on create
        static::creating(function ($payroll) {
            if (empty($payroll->payroll_reference)) {
                $payroll->payroll_reference = self::generatePayrollReference();
            }
        });
    }

    /**
     * Generate payroll reference (payroll-YYYYMMDD-XXXXXX)
     */
    public static function generatePayrollReference()
    {
        $date = date('Ymd');
        $lastPayroll = self::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        if (!$lastPayroll) {
            $sequence = '000001';
        } else {
            $lastReference = $lastPayroll->payroll_reference;
            $lastSequence = (int) substr($lastReference, -6);
            $sequence = str_pad($lastSequence + 1, 6, '0', STR_PAD_LEFT);
        }

        return 'payroll-' . $date . '-' . $sequence;
    }

    /**
     * Available payment methods
     */
    public static function getPaymentMethods()
    {
        return [
            'Cash',
            'Bank Transfer',
            'Cheque',
            'Mobile Banking',
            'Credit Card',
            'Debit Card',
        ];
    }

    /**
     * =========================
     * Relationships
     * =========================
     */

    /**
     * Payroll belongs to an employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Payroll belongs to an account
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Payroll created by user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get related transaction
     */
    public function transaction()
    {
        return $this->hasOne(AccountTransaction::class, 'reference_id')
            ->where('reference_type', 'payroll');
    }

    /**
     * =========================
     * Scopes
     * =========================
     */

    /**
     * Scope for today's payrolls
     */
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    /**
     * Scope for this month's payrolls
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}