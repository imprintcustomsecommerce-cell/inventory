<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private MaterialService $materials)
    {
    }

    /**
     * Generate the next PO number, e.g. PO-2026-0007. Resets per year.
     */
    public function nextNumber(): string
    {
        $year = now()->year;
        $prefix = "PO-{$year}-";

        $last = PurchaseOrder::withTrashed()
            ->where('po_number', 'like', $prefix . '%')
            ->orderByDesc('po_number')
            ->value('po_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** Recompute the order total from its line items. */
    public function recalcTotals(PurchaseOrder $order): void
    {
        $order->update(['total' => (float) $order->items()->sum('total')]);
    }

    /**
     * Receive a quantity against a single line item: add it to the linked
     * material's stock (logged as a movement), refresh the material's unit
     * cost to the purchase cost, and advance the order's status.
     */
    public function receiveItem(PurchaseOrderItem $item, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $quantity = min($quantity, $item->outstanding());
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($item, $quantity) {
            $order = $item->purchaseOrder;

            if ($item->material) {
                $this->materials->record(
                    $item->material,
                    'stock_in',
                    $quantity,
                    $order->po_number,
                    "Received from PO {$order->po_number}"
                );

                // Keep the material's costing current with the latest purchase price.
                if ((float) $item->unit_cost > 0) {
                    $item->material->update(['unit_cost' => $item->unit_cost]);
                }
            }

            $item->update([
                'quantity_received' => (float) $item->quantity_received + $quantity,
            ]);

            $this->refreshStatus($order->fresh('items'));
        });
    }

    /** Receive all outstanding quantities across every line item. */
    public function receiveAll(PurchaseOrder $order): void
    {
        $order->loadMissing('items.material');

        foreach ($order->items as $item) {
            $this->receiveItem($item, $item->outstanding());
        }
    }

    /**
     * Derive the order status from how much of it has been received.
     * A cancelled order stays cancelled.
     */
    public function refreshStatus(PurchaseOrder $order): void
    {
        if ($order->status === 'Cancelled') {
            return;
        }

        $ordered = (float) $order->items->sum('quantity_ordered');
        $received = (float) $order->items->sum('quantity_received');

        $status = match (true) {
            $received <= 0 => $order->status === 'Draft' ? 'Draft' : 'Ordered',
            $received >= $ordered => 'Received',
            default => 'Partially Received',
        };

        $order->update(['status' => $status]);
    }

    public function changeStatus(PurchaseOrder $order, string $status): void
    {
        if ($order->status === $status) {
            return;
        }

        $order->update(['status' => $status]);
    }

    public function getStatistics(): array
    {
        return [
            'total' => PurchaseOrder::count(),
            'open' => PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received'])->count(),
            'overdue' => PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received'])
                ->whereNotNull('expected_date')->whereDate('expected_date', '<', today())->count(),
            'open_value' => (float) PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received'])->sum('total'),
        ];
    }
}
