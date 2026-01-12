<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductWarehouse extends Model
{
    protected $table = 'product_warehouse';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'expiry_date',
        'quantity',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    // ==================== Relationships ====================
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // ==================== Helper Methods ====================

    /**
     * Generate unique batch ID based on product and warehouse
     * This ensures same product + warehouse always gets same batch_id
     */
    public static function generateBatchId($productId, $warehouseId)
    {
        return 'BATCH-P' . $productId . '-W' . $warehouseId;
    }

    /**
     * Get total stock for a product in a warehouse
     */
    public static function getStock($productId, $warehouseId)
    {
        return self::where('product_id', $productId)
                   ->where('warehouse_id', $warehouseId)
                   ->sum('quantity');
    }

    /**
     * Add stock to specific warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $quantity
     * @param string|null $batchId (optional, will auto-generate if not provided)
     * @param string|null $expiryDate
     * @return ProductWarehouse
     */
    public static function addStock($warehouseId, $productId, $quantity, $batchId = null, $expiryDate = null)
    {
        // Generate consistent batch ID for this product + warehouse combination
        if (!$batchId) {
            $batchId = self::generateBatchId($productId, $warehouseId);
        }

        // Check if stock already exists for this product + warehouse (ignore expiry date for now)
        $existingStock = self::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('batch_id', $batchId)
            ->first();

        if ($existingStock) {
            // Update existing record - just increment quantity
            $existingStock->increment('quantity', $quantity);
            
            // Update expiry date if provided
            if ($expiryDate && !$existingStock->expiry_date) {
                $existingStock->expiry_date = $expiryDate;
                $existingStock->save();
            }
            
            Log::info('Warehouse stock updated (same batch)', [
                'id' => $existingStock->id,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'batch_id' => $batchId,
                'quantity_added' => $quantity,
                'new_total' => $existingStock->quantity,
            ]);
            
            return $existingStock;
        }

        // Create new stock record
        $newStock = self::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'batch_id' => $batchId,
            'expiry_date' => $expiryDate,
            'quantity' => $quantity,
        ]);

        Log::info('Warehouse stock added (new record)', [
            'id' => $newStock->id,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'batch_id' => $batchId,
            'quantity' => $quantity,
        ]);

        return $newStock;
    }

    /**
     * Remove stock from warehouse (FIFO - First In First Out)
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $quantity
     * @return bool
     * @throws \Exception
     */
    public static function removeStock($warehouseId, $productId, $quantity)
    {
        // Check available stock
        $availableStock = self::getAvailableStock($warehouseId, $productId);
        
        if ($availableStock < $quantity) {
            $product = Product::find($productId);
            $productName = $product ? $product->name : "Product ID: $productId";
            
            throw new \Exception(
                "❌ Insufficient stock for '{$productName}' in warehouse. Available: {$availableStock}, Requested: {$quantity}"
            );
        }

        $remainingQty = $quantity;
        
        // Get all stock records for this product in this warehouse (FIFO - oldest first)
        $stocks = self::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($stocks as $stock) {
            if ($remainingQty <= 0) break;

            if ($stock->quantity >= $remainingQty) {
                // This stock is enough to fulfill remaining quantity
                $stock->decrement('quantity', $remainingQty);
                
                Log::info('Warehouse stock deducted', [
                    'id' => $stock->id,
                    'batch_id' => $stock->batch_id,
                    'quantity_removed' => $remainingQty,
                    'remaining_in_stock' => $stock->quantity,
                ]);
                
                $remainingQty = 0;
            } else {
                // Take all from this stock and continue to next
                $takenQty = $stock->quantity;
                $stock->update(['quantity' => 0]);
                
                Log::info('Warehouse stock depleted', [
                    'id' => $stock->id,
                    'batch_id' => $stock->batch_id,
                    'quantity_removed' => $takenQty,
                ]);
                
                $remainingQty -= $takenQty;
            }
        }

        // Safety check
        if ($remainingQty > 0) {
            throw new \Exception("Failed to deduct full quantity. Remaining: {$remainingQty}");
        }

        return true;
    }

    /**
     * Get available stock for a product in a warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return float
     */
    public static function getAvailableStock($warehouseId, $productId)
    {
        return self::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Get all batches for a product in a warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBatches($warehouseId, $productId)
    {
        return self::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Transfer stock between warehouses
     * 
     * @param int $fromWarehouseId
     * @param int $toWarehouseId
     * @param int $productId
     * @param float $quantity
     * @return bool
     * @throws \Exception
     */
    public static function transferStock($fromWarehouseId, $toWarehouseId, $productId, $quantity)
    {
        DB::beginTransaction();
        
        try {
            // Remove from source warehouse
            self::removeStock($fromWarehouseId, $productId, $quantity);
            
            // Add to destination warehouse
            self::addStock($toWarehouseId, $productId, $quantity);
            
            Log::info('Stock transferred between warehouses', [
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Stock transfer failed', [
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get stock details by warehouse (for reports/dashboard)
     * 
     * @param int|null $warehouseId
     * @return array
     */
    public static function getStockByWarehouse($warehouseId = null)
    {
        $query = self::with(['product', 'warehouse'])
            ->select('warehouse_id', 'product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('warehouse_id', 'product_id')
            ->having('total_quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get();
    }

    /**
     * Get expired batches
     * 
     * @param int|null $warehouseId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getExpiredBatches($warehouseId = null)
    {
        $query = self::with(['product', 'warehouse'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->where('quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->orderBy('expiry_date', 'asc')->get();
    }

    /**
     * Get near expiry batches (within X days)
     * 
     * @param int $days
     * @param int|null $warehouseId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNearExpiryBatches($days = 30, $warehouseId = null)
    {
        $query = self::with(['product', 'warehouse'])
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->where('quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->orderBy('expiry_date', 'asc')->get();
    }

    /**
     * Get low stock items in warehouse
     * 
     * @param int $warehouseId
     * @param int $threshold
     * @return array
     */
    public static function getLowStockItems($warehouseId, $threshold = 10)
    {
        return self::with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('warehouse_id', $warehouseId)
            ->groupBy('product_id')
            ->havingRaw('SUM(quantity) <= ?', [$threshold])
            ->get();
    }

    /**
     * Sync global product stock with warehouse stocks
     * Useful for data reconciliation
     * 
     * @param int $productId
     * @return bool
     */
    public static function syncGlobalStock($productId)
    {
        $totalStock = self::where('product_id', $productId)->sum('quantity');
        
        $product = Product::find($productId);
        if ($product) {
            $product->stock = $totalStock;
            $product->save();
            
            Log::info('Global stock synced with warehouse stocks', [
                'product_id' => $productId,
                'product_name' => $product->name,
                'synced_stock' => $totalStock,
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Clean up zero quantity records (optional maintenance task)
     * 
     * @return int Number of records deleted
     */
    public static function cleanupZeroStock()
    {
        $deleted = self::where('quantity', '<=', 0)->delete();
        
        Log::info('Zero stock records cleaned up', [
            'records_deleted' => $deleted,
        ]);
        
        return $deleted;
    }
}