<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key if exists (using raw SQL)
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'product_warehouse' 
            AND COLUMN_NAME = 'purchase_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE product_warehouse DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
        
        // Drop index if exists
        try {
            DB::statement("ALTER TABLE product_warehouse DROP INDEX purchase_id");
        } catch (\Exception $e) {
            // Index doesn't exist
        }
        
        // Drop column
        DB::statement("ALTER TABLE product_warehouse DROP COLUMN purchase_id");
        
        // Add new index
        DB::statement("ALTER TABLE product_warehouse ADD INDEX idx_warehouse_product (warehouse_id, product_id)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE product_warehouse ADD COLUMN purchase_id BIGINT UNSIGNED NULL AFTER quantity");
        DB::statement("ALTER TABLE product_warehouse ADD INDEX purchase_id (purchase_id)");
        DB::statement("ALTER TABLE product_warehouse DROP INDEX idx_warehouse_product");
    }
};