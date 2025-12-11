<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
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
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'paid_amount', 'due_amount']);
        });
    }
};