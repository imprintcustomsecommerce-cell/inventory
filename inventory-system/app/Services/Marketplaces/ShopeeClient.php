<?php

namespace App\Services\Marketplaces;

use App\Models\SalesChannel;
use Illuminate\Support\Facades\Http;

/**
 * Shopee Open Platform v2 client (partner.shopeemobile.com).
 *
 * Credentials live on the channel (encrypted):
 *   partner_id, partner_key  — from your app at open.shopee.com
 *   shop_id, access_token, refresh_token, expires_at — set by the
 *   authorization callback and refreshed automatically.
 */
class ShopeeClient
{
    public const BASE_URL = 'https://partner.shopeemobile.com';

    public function __construct(private SalesChannel $channel)
    {
    }

    /** URL to send the seller to so they authorize the shop for our app. */
    public function authUrl(string $redirect): string
    {
        $path = '/api/v2/shop/auth_partner';
        $timestamp = time();

        return self::BASE_URL . $path . '?' . http_build_query([
            'partner_id' => (int) $this->cred('partner_id'),
            'timestamp' => $timestamp,
            'sign' => $this->publicSign($path, $timestamp),
            'redirect' => $redirect,
        ]);
    }

    /** Exchange the authorization code from the callback for tokens. */
    public function exchangeCode(string $code, int $shopId): void
    {
        $data = $this->publicPost('/api/v2/auth/token/get', [
            'code' => $code,
            'shop_id' => $shopId,
            'partner_id' => (int) $this->cred('partner_id'),
        ]);

        $this->storeTokens($data, $shopId);
    }

    /** Refresh the access token when it is near expiry. */
    public function ensureFreshToken(): void
    {
        $expiresAt = (int) $this->cred('expires_at', 0);
        if ($expiresAt > time() + 300) {
            return; // still valid for 5+ minutes
        }

        $data = $this->publicPost('/api/v2/auth/access_token/get', [
            'refresh_token' => (string) $this->cred('refresh_token'),
            'shop_id' => (int) $this->cred('shop_id'),
            'partner_id' => (int) $this->cred('partner_id'),
        ]);

        $this->storeTokens($data, (int) $this->cred('shop_id'));
    }

    /**
     * Pull recent orders (last N days) as raw rows shaped for
     * MarketplaceClient::mapOrder().
     *
     * @return array<int, array<string, mixed>>
     */
    public function pullOrders(int $days = 7, int $pageSize = 50): array
    {
        $this->ensureFreshToken();

        // 1. List order numbers in the window (Shopee caps windows at 15 days).
        $list = $this->shopGet('/api/v2/order/get_order_list', [
            'time_range_field' => 'create_time',
            'time_from' => now()->subDays(min($days, 15))->timestamp,
            'time_to' => now()->timestamp,
            'page_size' => min($pageSize, 100),
        ]);

        $orderSns = array_column($list['order_list'] ?? [], 'order_sn');
        if (empty($orderSns)) {
            return [];
        }

        // 2. Fetch details (max 50 per call).
        $rows = [];
        foreach (array_chunk($orderSns, 50) as $chunk) {
            $detail = $this->shopGet('/api/v2/order/get_order_detail', [
                'order_sn_list' => implode(',', $chunk),
                'response_optional_fields' => 'buyer_username,recipient_address,item_list,total_amount,create_time,order_status',
            ]);

            foreach ($detail['order_list'] ?? [] as $order) {
                $items = $order['item_list'] ?? [];
                $first = $items[0]['item_name'] ?? 'Shopee order';
                $extra = count($items) - 1;

                $rows[] = [
                    'order_sn' => $order['order_sn'],
                    'buyer_username' => $order['buyer_username'] ?? 'Shopee buyer',
                    'buyer_phone' => $order['recipient_address']['phone'] ?? null,
                    'item_name' => $extra > 0 ? "{$first} (+{$extra} more)" : $first,
                    'qty' => max(1, array_sum(array_column($items, 'model_quantity_purchased'))),
                    'total_amount' => (float) ($order['total_amount'] ?? 0),
                    'is_custom' => false,
                    'create_time' => isset($order['create_time'])
                        ? date('c', (int) $order['create_time'])
                        : now()->toIso8601String(),
                ];
            }
        }

        return $rows;
    }

    // ─── signing & transport ────────────────────────────────────────────

    /** Public (pre-authorization) endpoints: sign partner_id+path+timestamp. */
    private function publicSign(string $path, int $timestamp): string
    {
        return hash_hmac(
            'sha256',
            $this->cred('partner_id') . $path . $timestamp,
            (string) $this->cred('partner_key')
        );
    }

    /** Shop endpoints: sign partner_id+path+timestamp+access_token+shop_id. */
    private function shopSign(string $path, int $timestamp): string
    {
        return hash_hmac(
            'sha256',
            $this->cred('partner_id') . $path . $timestamp . $this->cred('access_token') . $this->cred('shop_id'),
            (string) $this->cred('partner_key')
        );
    }

    private function publicPost(string $path, array $body): array
    {
        $timestamp = time();

        $response = Http::timeout(15)->acceptJson()->post(
            self::BASE_URL . $path . '?' . http_build_query([
                'partner_id' => (int) $this->cred('partner_id'),
                'timestamp' => $timestamp,
                'sign' => $this->publicSign($path, $timestamp),
            ]),
            $body
        );

        return $this->parse($response->json(), $path);
    }

    private function shopGet(string $path, array $params): array
    {
        $timestamp = time();

        $response = Http::timeout(15)->acceptJson()->get(self::BASE_URL . $path, array_merge([
            'partner_id' => (int) $this->cred('partner_id'),
            'timestamp' => $timestamp,
            'access_token' => (string) $this->cred('access_token'),
            'shop_id' => (int) $this->cred('shop_id'),
            'sign' => $this->shopSign($path, $timestamp),
        ], $params));

        return $this->parse($response->json(), $path);
    }

    private function parse(?array $json, string $path): array
    {
        if (!is_array($json)) {
            throw new \RuntimeException("Shopee API returned an unreadable response for {$path}.");
        }
        if (!empty($json['error'])) {
            $msg = $json['message'] ?? $json['error'];
            throw new \RuntimeException("Shopee API error on {$path}: {$msg}");
        }

        return $json['response'] ?? $json;
    }

    private function storeTokens(array $data, int $shopId): void
    {
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Shopee did not return an access token.');
        }

        $this->channel->update(['credentials' => array_merge($this->channel->credentials ?? [], [
            'shop_id' => $shopId,
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $this->cred('refresh_token'),
            'expires_at' => time() + (int) ($data['expire_in'] ?? 14400),
        ])]);

        $this->channel->refresh();
    }

    private function cred(string $key, mixed $default = null): mixed
    {
        return $this->channel->credentials[$key] ?? $default;
    }
}
