<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Record a sale: deduct stock from the item and book the revenue.
     *
     * @return Sale|false  false if not enough stock
     */
    public function sell(InventoryItem $item, float $quantity, float $unitPrice, ?string $remarks = null): Sale|false
    {
        return DB::transaction(function () use ($item, $quantity, $unitPrice, $remarks) {
            $out = $this->inventory->stockOut($item, $quantity, 'Sale', $remarks);
            if ($out === false) {
                return false;
            }

            $label = trim($item->name . ($item->size ? " ({$item->size})" : ''));

            return Sale::create([
                'warehouse_id' => $item->warehouse_id,
                'inventory_item_id' => $item->id,
                'product_id' => $item->product_id,
                'user_id' => auth()->id(),
                'item_label' => $label,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => round($quantity * $unitPrice, 2),
                'remarks' => $remarks,
            ]);
        });
    }
}
