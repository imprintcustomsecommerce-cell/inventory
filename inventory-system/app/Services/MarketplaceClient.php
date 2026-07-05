<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\SalesChannel;
use Illuminate\Support\Facades\Log;

/**
 * Marketplace order ingestion. Orders come ONLY from the real platform APIs —
 * a channel must have API credentials saved and its shop authorized before
 * anything appears in Online Orders.
 */
class MarketplaceClient
{
    /**
     * Pull recent orders for a channel and persist any new ones.
     *
     * @return array{ok: bool, created: int, error?: string}
     */
    public function pullOrders(SalesChannel $channel, int $limit = 50): array
    {
        if (!$channel->isLive()) {
            return [
                'ok' => false,
                'created' => 0,
                'error' => "{$channel->name} is not live yet — save your API keys and authorize the shop first. Orders only sync from the real marketplace.",
            ];
        }

        try {
            $rows = match ($channel->platform) {
                'shopee' => (new Marketplaces\ShopeeClient($channel))->pullOrders(),
                'lazada' => (new Marketplaces\LazadaClient($channel))->pullOrders(),
                'tiktok' => (new Marketplaces\TikTokClient($channel))->pullOrders(),
                default => throw new \RuntimeException(
                    ucfirst($channel->platform) . ' live API is not integrated yet.'
                ),
            };
        } catch (\Throwable $e) {
            Log::warning("Live marketplace pull failed for {$channel->platform}: {$e->getMessage()}");

            return ['ok' => false, 'created' => 0, 'error' => $e->getMessage()];
        }

        return ['ok' => true, 'created' => $this->ingest($channel, $rows)];
    }

    /**
     * Persist raw marketplace rows as OnlineOrders, skipping ones already
     * ingested (idempotent on the marketplace ref). Returns created count.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function ingest(SalesChannel $channel, array $rows): int
    {
        $created = 0;

        foreach ($rows as $row) {
            $mapped = $this->mapOrder($channel, $row);

            $exists = OnlineOrder::where('sales_channel_id', $channel->id)
                ->where('external_ref', $mapped['external_ref'])
                ->exists();

            if ($exists) {
                continue;
            }

            OnlineOrder::create($mapped);
            $created++;
        }

        return $created;
    }

    /**
     * Normalise a raw marketplace row into our OnlineOrder shape.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapOrder(SalesChannel $channel, array $row): array
    {
        $qty = (int) ($row['qty'] ?? 1);

        return [
            'sales_channel_id' => $channel->id,
            'external_ref' => (string) ($row['order_sn'] ?? uniqid('ORD-')),
            'buyer_name' => (string) ($row['buyer_username'] ?? 'Unknown buyer'),
            'buyer_contact' => $row['buyer_phone'] ?? null,
            'item_label' => (string) ($row['item_name'] ?? 'Item'),
            'quantity' => max(1, $qty),
            'amount' => (float) ($row['total_amount'] ?? 0),
            'order_type' => !empty($row['is_custom']) ? 'custom' : 'stock',
            'status' => 'New',
            'ordered_at' => isset($row['create_time']) ? \Carbon\Carbon::parse($row['create_time']) : now(),
        ];
    }
}
