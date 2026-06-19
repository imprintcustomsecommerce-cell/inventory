<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Collection;

class InventoryService
{
    public function stockIn(InventoryItem $item, int $quantity, ?string $reference = null, ?string $remarks = null): InventoryMovement
    {
        return \DB::transaction(function () use ($item, $quantity, $reference, $remarks) {
            $item->lockForUpdate()->first();

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

    public function stockOut(InventoryItem $item, int $quantity, ?string $reference = null, ?string $remarks = null): InventoryMovement|false
    {
        return \DB::transaction(function () use ($item, $quantity, $reference, $remarks) {
            $item = $item->lockForUpdate()->first();

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

    public function adjustStock(InventoryItem $item, int $actualStock, ?string $reference = null, ?string $remarks = null): InventoryMovement
    {
        return \DB::transaction(function () use ($item, $actualStock, $reference, $remarks) {
            $item = $item->lockForUpdate()->first();
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
