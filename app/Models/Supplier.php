<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'company',
        'vat_number',
        'status',
        'image',
        'total_due',
        'bank_name',
        'branch_name',
        'account_number',
        'routing_number',
        'swift_code',
        'iban',
        'currency_type',
        'bank_address',
        'mobile_banking',
    ];

    protected $casts = [
        'total_due' => 'decimal:2',
    ];

    /**
     * Append calculated attributes
     */
    protected $appends = ['calculated_total_due'];

    /**
     * Relationship to purchases
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id')
            ->where('supplier_type', 'supplier');
    }

    /**
     * Relationship to payments
     */
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /**
     * Calculate total due from purchases (real-time calculation)
     * This ensures accuracy by summing actual purchase dues
     */
    public function getCalculatedTotalDueAttribute()
    {
        return $this->purchases()
            ->where('supplier_type', 'supplier')
            ->sum('due_amount') ?? 0;
    }

    /**
     * Get the correct total due
     * This method returns the calculated value from purchases
     * to ensure it's always accurate
     */
    public function getTotalDueAttribute($value)
    {
        // Return calculated value from purchases for accuracy
        return $this->calculated_total_due;
    }

    /**
     * Recalculate and sync total_due from purchases
     * Call this method after any payment or purchase changes
     */
    public function recalculateTotalDue()
    {
        $calculatedDue = $this->purchases()
            ->where('supplier_type', 'supplier')
            ->sum('due_amount') ?? 0;

        // Update the database field
        $this->update(['total_due' => $calculatedDue]);

        return $calculatedDue;
    }

    /**
     * Boot method to auto-calculate on load
     */
    protected static function booted()
    {
        // Optionally auto-sync when model is retrieved
        static::retrieved(function ($supplier) {
            // This ensures the value is fresh from purchases
            $supplier->setRawAttributes(
                array_merge($supplier->getAttributes(), [
                    'total_due' => $supplier->calculated_total_due
                ])
            );
        });
    }
}