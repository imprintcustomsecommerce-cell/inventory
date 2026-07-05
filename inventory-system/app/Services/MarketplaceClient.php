<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\SalesChannel;
use App\Support\MockOrderFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for marketplace order ingestion. Today it talks to the in-app
 * mock API; to go live, point services.marketplace.mock_url at the real
 * host and add the platform's auth headers / request signing below.
 */
class MarketplaceClient
{
    /**
     * Pull recent orders for a channel and persist any new ones.
     *
     * @return array{ok: bool, created: int, error?: string}
     */
    public function pullOrders(SalesChannel $channel, int $limit = 2): array
    {
        $baseUrl = rtrim((string) config('services.marketplace.mock_url'), '/');

        if ($this->isSelf($baseUrl)) {
            // The mock marketplace lives inside this app. Calling ourselves
            // over HTTP deadlocks under `php artisan serve` (single worker)
            // and breaks whenever the port changes — generate in-process.
            $rows = MockOrderFactory::payload($channel->platform, $limit);
        } else {
            $url = "{$baseUrl}/mock-api/{$channel->platform}/orders";

            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->get($url, ['limit' => $limit]);
            } catch (\Throwable $e) {
                Log::warning("Marketplace pull failed for {$channel->platform}: {$e->getMessage()}");

                return ['ok' => false, 'created' => 0, 'error' => 'Could not reach the marketplace API.'];
            }

            if ($response->failed()) {
                return ['ok' => false, 'created' => 0, 'error' => "Marketplace API returned {$response->status()}."];
            }

            $rows = $response->json('orders', []);
        }

        $created = 0;

        foreach ($rows as $row) {
            $mapped = $this->mapOrder($channel, $row);

            // Skip orders already ingested (idempotent on the marketplace ref).
            $exists = OnlineOrder::where('sales_channel_id', $channel->id)
                ->where('external_ref', $mapped['external_ref'])
                ->exists();

            if ($exists) {
                continue;
            }

            OnlineOrder::create($mapped);
            $created++;
        }

        return ['ok' => true, 'created' => $created];
    }

    /**
     * True when the configured marketplace URL is this app itself (or unset),
     * i.e. we are in mock mode rather than talking to a real remote host.
     */
    private function isSelf(string $baseUrl): bool
    {
        if ($baseUrl === '') {
            return true;
        }

        $host = (string) parse_url($baseUrl, PHP_URL_HOST);

        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
            || $host === (string) parse_url((string) config('app.url'), PHP_URL_HOST);
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
