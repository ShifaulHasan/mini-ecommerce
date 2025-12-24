<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Customer extends Model
{
    use HasFactory;

    /**
     * ========================
     * Mass Assignable Fields
     * ========================
     */
    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'email',
        'company_name',
        'tax_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'customer_group_id',
        'discount_percentage',
        'reward_points',
        'deposited_balance',
        'total_due',
        'is_supplier',
        'is_active',
        'user_id',
        'created_by',
    ];

    /**
     * ========================
     * Casts
     * ========================
     */
    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'reward_points'       => 'integer',
        'deposited_balance'   => 'decimal:2',
        'total_due'           => 'decimal:2',
        'is_supplier'         => 'boolean',
        'is_active'           => 'boolean',
    ];

    /**
     * ========================
     * Boot Method
     * ========================
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = self::generateCustomerCode();
            }
            
            // Initialize total_due to 0 on creation
            if (!isset($customer->total_due)) {
                $customer->total_due = 0;
            }
        });
    }

    /**
     * Generate unique customer code
     */
    public static function generateCustomerCode()
    {
        $lastCustomer = self::latest('id')->first();
        $nextId = $lastCustomer ? $lastCustomer->id + 1 : 1;

        return 'CUS-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * ========================
     * Relationships
     * ========================
     */

    /**
     * 🔥 CRITICAL: Customer → Sales
     * Required for due sync between Sale List & Customer List
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }

    /**
     * Customer Payments (Due Payments)
     */
    public function payments()
    {
        return $this->hasMany(CustomerPayment::class, 'customer_id');
    }

    /**
     * Customer Group
     */
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Linked User Account
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Creator (Admin / Staff)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ========================
     * 🔥 SINGLE SOURCE OF TRUTH: Due Calculation & Sync
     * ========================
     */

    /**
     * Calculate total due from sales (SINGLE SOURCE OF TRUTH)
     * This is the ONLY correct way to know customer's due
     */
    public function calculateTotalDue()
    {
        return $this->sales()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum('due_amount');
    }

    /**
     * 🔥 Sync total_due with actual sales data
     * Call this after any sale creation/update/payment
     */
    public function syncTotalDue()
    {
        $calculatedDue = $this->calculateTotalDue();
        
        if ($this->total_due != $calculatedDue) {
            $oldDue = $this->total_due;
            $this->total_due = $calculatedDue;
            $this->save();
            
            Log::info('Customer due synchronized', [
                'customer_id' => $this->id,
                'customer_name' => $this->name,
                'old_total_due' => $oldDue,
                'new_total_due' => $calculatedDue,
                'difference' => $calculatedDue - $oldDue,
            ]);
        }
        
        return $calculatedDue;
    }

    /**
     * ========================
     * Reward Points
     * ========================
     */

    public function addRewardPoints($points)
    {
        $this->increment('reward_points', $points);
    }

    public function deductRewardPoints($points)
    {
        $this->decrement('reward_points', $points);
    }
}