<?php

namespace App\Services;

use App\Models\StockRequest;
use Illuminate\Support\Facades\DB;

class StockRequestService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Lines that cannot be fully covered by current stockroom stock.
     * A request may only be fulfilled when this returns an empty array.
     *
     * @return array<int, string> human-readable shortage descriptions
     */
    public function shortages(StockRequest $request): array
    {
        $request->load('items.inventoryItem');

        $short = [];
        foreach ($request->items as $line) {
            $available = $line->inventoryItem
                ? (float) $line->inventoryItem->fresh()->current_stock
                : 0.0;

            if ($available < (float) $line->quantity) {
                $short[] = $line->item_label
                    . ' (need ' . $this->fmt($line->quantity)
                    . ', available ' . $this->fmt($available) . ')';
            }
        }

        return $short;
    }

    /**
     * Fulfill a request by transferring every line in full from its source
     * stockroom item to the requesting location.
     *
     * All-or-nothing: if ANY line lacks stock, nothing is transferred and the
     * shortages are returned so the caller can show them.
     *
     * @return array{moved: int, short: array<int, string>}
     */
    public function fulfill(StockRequest $request): array
    {
        $short = $this->shortages($request);
        if (!empty($short)) {
            return ['moved' => 0, 'short' => $short];
        }

        $request->load('items.inventoryItem.warehouse', 'warehouse');
        $destination = $request->warehouse;

        $moved = 0;

        DB::transaction(function () use ($request, $destination, &$moved) {
            foreach ($request->items as $line) {
                $source = $line->inventoryItem;
                if (!$source) {
                    continue;
                }

                // Re-check inside the transaction so a concurrent sale/transfer
                // can't sneak the stock out from under us.
                $available = (float) $source->fresh()->current_stock;
                $needed = (float) $line->quantity;
                if ($available < $needed) {
                    throw new \RuntimeException(
                        'Stock changed while fulfilling: ' . $line->item_label
                        . ' now has only ' . $this->fmt($available)
                        . ' available (need ' . $this->fmt($needed) . '). Nothing was transferred.'
                    );
                }

                $ok = $this->inventory->transfer($source, $destination, $needed, "Request #{$request->id}");
                if (!$ok) {
                    throw new \RuntimeException(
                        'Transfer failed for ' . $line->item_label . '. Nothing was transferred.'
                    );
                }

                $line->update(['fulfilled_quantity' => $needed]);
                $moved++;
            }

            $request->update([
                'status' => 'fulfilled',
                'handled_by_id' => auth()->id(),
                'handled_at' => now(),
            ]);
        });

        return ['moved' => $moved, 'short' => []];
    }

    private function fmt(float|string $qty): string
    {
        return rtrim(rtrim(number_format((float) $qty, 2), '0'), '.');
    }
}
