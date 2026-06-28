<?php

namespace App\Services;

use App\Models\StockRequest;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockRequestService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Fulfill a request: transfer each line from its source stockroom item to
     * the requesting location (capped at available stock). Returns a summary.
     *
     * @return array{moved: int, short: array<int, string>}
     */
    public function fulfill(StockRequest $request): array
    {
        $request->load('items.inventoryItem.warehouse', 'warehouse');
        $destination = $request->warehouse;

        $moved = 0;
        $short = [];

        DB::transaction(function () use ($request, $destination, &$moved, &$short) {
            foreach ($request->items as $line) {
                $source = $line->inventoryItem;
                if (!$source) {
                    continue;
                }

                $available = (float) $source->fresh()->current_stock;
                $send = min((float) $line->quantity, $available);

                if ($send > 0) {
                    $ok = $this->inventory->transfer($source, $destination, $send, "Request #{$request->id}");
                    if ($ok) {
                        $line->update(['fulfilled_quantity' => $send]);
                        $moved++;
                    }
                }

                if ($send < (float) $line->quantity) {
                    $short[] = $line->item_label . ' (sent ' . rtrim(rtrim(number_format($send, 2), '0'), '.') . ' of ' . rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') . ')';
                }
            }

            $request->update([
                'status' => 'fulfilled',
                'handled_by_id' => auth()->id(),
                'handled_at' => now(),
            ]);
        });

        return ['moved' => $moved, 'short' => $short];
    }
}
