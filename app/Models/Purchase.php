<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * ========================
     * Mass Assignable Fields
     * ========================
     */
    protected $fillable = [
        'reference_no',
        'purchase_date',
        'date',
        
        'warehouse_id',
        'supplier_id',
        'supplier_type', // 'supplier' or 'user'
        
        'status',
        'purchase_status',
        
        // Tax & Discount
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'shipping_cost',
        'total',
        
        // Payment
        'grand_total',
        'returned_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'account_id',
        
        // Currency
        'currency',
        'exchange_rate',
        
        // Others
        'notes',
        'document',
        'document_path',
        'created_by',
    ];

    /**
     * ========================
     * Casts
     * ========================
     */
    protected $casts = [
        'purchase_date'   => 'date',
        'date'            => 'date',
        
        'subtotal'        => 'decimal:2',
        'tax_percentage'  => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_value'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'total'           => 'decimal:2',
        
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
     * Creator (Admin/User who created the purchase)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the supplier (polymorphic based on supplier_type)
     * This method dynamically returns either Supplier or User
     */
    public function supplier()
    {
        if ($this->supplier_type === 'supplier') {
            return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
        }
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Get supplier from suppliers table (when supplier_type = 'supplier')
     */
    public function supplierModel()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    /**
     * Get supplier from users table (when supplier_type = 'user')
     */
    public function userSupplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Warehouse relationship
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Purchase Items relationship
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Payment Account relationship
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * ========================
     * Accessors & Mutators
     * ========================
     */

    /**
     * Get supplier name regardless of type
     */
    public function getSupplierNameAttribute()
    {
        if (!$this->supplier_id) {
            return 'N/A';
        }

        if ($this->supplier_type === 'supplier') {
            $supplier = \App\Models\Supplier::find($this->supplier_id);
            return $supplier ? $supplier->name : 'N/A';
        } else {
            $user = User::find($this->supplier_id);
            return $user ? $user->name : 'N/A';
        }
    }

    /**
     * Get supplier details (name with company if applicable)
     */
    public function getSupplierDetailsAttribute()
    {
        if (!$this->supplier_id) {
            return 'N/A';
        }

        if ($this->supplier_type === 'supplier') {
            $supplier = \App\Models\Supplier::find($this->supplier_id);
            if ($supplier) {
                return $supplier->name . ($supplier->company ? " ({$supplier->company})" : '');
            }
        } else {
            $user = User::find($this->supplier_id);
            if ($user) {
                return $user->name . ' (User Account)';
            }
        }

        return 'N/A';
    }

    /**
     * ========================
     * Static Methods
     * ========================
     */

    /**
     * Generate unique reference number
     * Example: PUR-20251225-0001
     */
    public static function generateReferenceNo()
    {
        $date = now()->format('Ymd');

        $lastPurchase = self::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        $number = $lastPurchase
            ? intval(substr($lastPurchase->reference_no, -4)) + 1
            : 1;

        return 'PUR-' . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ========================
     * Scopes
     * ========================
     */

    /**
     * Scope to filter by supplier type
     */
    public function scopeBySupplierType($query, $type)
    {
        return $query->where('supplier_type', $type);
    }

    /**
     * Scope to get purchases with supplier from suppliers table
     */
    public function scopeWithSupplierModel($query)
    {
        return $query->where('supplier_type', 'supplier')
            ->with('supplierModel');
    }

    /**
     * Scope to get purchases with supplier from users table
     */
    public function scopeWithUserSupplier($query)
    {
        return $query->where('supplier_type', 'user')
            ->with('userSupplier');
    }

    /**
     * Scope to filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to filter by purchase status
     */
    public function scopeByPurchaseStatus($query, $status)
    {
        return $query->where('purchase_status', $status);
    }
}