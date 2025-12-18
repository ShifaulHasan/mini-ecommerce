<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add role column if it doesn't exist
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'Admin', 
                    'User', 
                    'Employee', 
                    'Manager', 
                    'Cashier', 
                    'Supplier', 
                    'Customer', 
                    'Biller'
                ])->default('User')->after('email');
            }
            
            // Add additional supplier/customer fields if they don't exist
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('role');
            }
            
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('company_name');
            }
            
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'company_name', 'phone', 'address']);
        });
    }
};