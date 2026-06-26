<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Move stock from one warehouse to another. Deducts from the source item
     * and adds to a matching item in the destination warehouse (created if it
     * doesn't exist yet). Both legs are recorded as movements.
     */
    public function transfer(InventoryItem $source, Warehouse $destination, float $quantity, ?string $remarks = null): bool
    {
        return DB::transaction(function () use ($source, $destination, $quantity, $remarks) {
            $source->refresh();

            if ($source->warehouse_id === $destination->id || $quantity <= 0 || $quantity > (float) $source->current_stock) {
                return false;
            }

            $fromName = $source->warehouse?->name ?? 'another warehouse';

            $out = $this->stockOut($source, $quantity, "Transfer to {$destination->name}", $remarks);
            if (!$out) {
                return false;
            }

            $target = InventoryItem::firstOrCreate(
                [
                    'warehouse_id' => $destination->id,
                    'name' => $source->name,
                    'size' => $source->size,
                ],
                [
                    'product_id' => $source->product_id,
                    'category' => $source->category,
                    'unit' => $source->unit,
                    'minimum_stock' => $source->minimum_stock,
                    'unit_cost' => $source->unit_cost,
                    'status' => 'active',
                ]
            );

            // Keep the destination copy linked to the same product.
            if (!$target->product_id && $source->product_id) {
                $target->update(['product_id' => $source->product_id]);
            }

            $this->stockIn($target, $quantity, "Transfer from {$fromName}", $remarks);

            return true;
        });
    }

    public function stockIn(InventoryItem $item, float $quantity, ?string $reference = null, ?string $remarks = null): InventoryMovement
    {
        return \DB::transaction(function () use ($item, $quantity, $reference, $remarks) {
            $item = InventoryItem::whereKey($item->id)->lockForUpdate()->first();

            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => 'stock_in',
                'quantity' => $quantity,
                'reference' => $reference,
                'remarks' => $remarks,
            ]);

            $item->update(['current_stock' => $item->current_stock + $quantity]);

            return $item->movements()->latest()->first();
        });
    }

    public function stockOut(InventoryItem $item, float $quantity, ?string $reference = null, ?string $remarks = null): InventoryMovement|false
    {
        return \DB::transaction(function () use ($item, $quantity, $reference, $remarks) {
            $item = InventoryItem::whereKey($item->id)->lockForUpdate()->first();

            if ($quantity > $item->current_stock) {
                return false;
            }

            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => 'stock_out',
                'quantity' => $quantity,
                'reference' => $reference,
                'remarks' => $remarks,
            ]);

            $item->update(['current_stock' => $item->current_stock - $quantity]);

            return $item->movements()->latest()->first();
        });
    }

    public function adjustStock(InventoryItem $item, float $actualStock, ?string $reference = null, ?string $remarks = null): InventoryMovement
    {
        return \DB::transaction(function () use ($item, $actualStock, $reference, $remarks) {
            $item = InventoryItem::whereKey($item->id)->lockForUpdate()->first();
            $previousStock = $item->current_stock;

            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity' => $actualStock,
                'reference' => $reference,
                'remarks' => "Adjusted from {$previousStock} to {$actualStock}. " . ($remarks ?? ''),
            ]);

            $item->update(['current_stock' => $actualStock]);

            return $item->movements()->latest()->first();
        });
    }

    public function getLowStockItems(): Collection
    {
        return InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->get();
    }

    public function getOutOfStockItems(): Collection
    {
        return InventoryItem::where('current_stock', '<=', 0)->get();
    }

    public function getStatistics(): array
    {
        return [
            'total_items' => InventoryItem::count(),
            'low_stock_items' => InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0)->count(),
            'out_of_stock_items' => InventoryItem::where('current_stock', '<=', 0)->count(),
            'total_movements' => InventoryMovement::count(),
        ];
    }
}
