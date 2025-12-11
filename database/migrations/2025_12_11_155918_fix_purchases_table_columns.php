<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Add purchase_date if missing
            if (!Schema::hasColumn('purchases', 'purchase_date')) {
                $table->date('purchase_date')->after('id');
            }
            
            // Add status column if missing (for purchase status: received/pending)
            if (!Schema::hasColumn('purchases', 'status')) {
                $table->enum('status', ['received', 'pending', 'ordered'])->default('received')->after('supplier_id');
            }
            
            // Add tax columns if missing
            if (!Schema::hasColumn('purchases', 'tax_percentage')) {
                $table->decimal('tax_percentage', 8, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('purchases', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_percentage');
            }
            
            // Add discount column if missing
            if (!Schema::hasColumn('purchases', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
            }
            
            // Add shipping cost if missing
            if (!Schema::hasColumn('purchases', 'shipping_cost')) {
                $table->decimal('shipping_cost', 15, 2)->default(0)->after('discount_amount');
            }
            
            // Add grand_total if missing
            if (!Schema::hasColumn('purchases', 'grand_total')) {
                $table->decimal('grand_total', 15, 2)->default(0)->after('shipping_cost');
            }
            
            // Add payment columns if missing
            if (!Schema::hasColumn('purchases', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('grand_total');
            }
            if (!Schema::hasColumn('purchases', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('purchases', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_status');
            }
            if (!Schema::hasColumn('purchases', 'due_amount')) {
                $table->decimal('due_amount', 15, 2)->default(0)->after('paid_amount');
            }
            
            // Add currency columns if missing
            if (!Schema::hasColumn('purchases', 'currency')) {
                $table->string('currency', 10)->default('BDT')->after('due_amount');
            }
            if (!Schema::hasColumn('purchases', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 4)->default(1.0000)->after('currency');
            }
            
            // Add notes if missing
            if (!Schema::hasColumn('purchases', 'notes')) {
                $table->text('notes')->nullable()->after('exchange_rate');
            }
            
            // Add document if missing
            if (!Schema::hasColumn('purchases', 'document')) {
                $table->string('document')->nullable()->after('notes');
            }
            
            // Add created_by if missing
            if (!Schema::hasColumn('purchases', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('document');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_date',
                'status',
                'tax_percentage',
                'tax_amount',
                'discount_amount',
                'shipping_cost',
                'grand_total',
                'payment_method',
                'payment_status',
                'paid_amount',
                'due_amount',
                'currency',
                'exchange_rate',
                'notes',
                'document',
                'created_by'
            ]);
        });
    }
};