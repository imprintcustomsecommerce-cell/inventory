<?php

namespace App\Services\Marketplaces;

use App\Models\SalesChannel;
use Illuminate\Support\Facades\Http;

/**
 * Lazada Open Platform client (Philippines endpoint).
 *
 * Credentials on the channel (encrypted):
 *   app_key, app_secret            — from your app at open.lazada.com
 *   access_token, refresh_token,
 *   expires_at                     — set by the authorization callback.
 *
 * Signing: every call is signed with HMAC-SHA256 over the API path plus the
 * alphabetically sorted parameters, per Lazada's signature spec.
 */
class LazadaClient
{
    public const BASE_URL = 'https://api.lazada.com.ph/rest';
    public const AUTH_URL = 'https://auth.lazada.com/oauth/authorize';
    public const TOKEN_BASE = 'https://auth.lazada.com/rest';

    public function __construct(private SalesChannel $channel)
    {
    }

    public function authUrl(string $redirect): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'response_type' => 'code',
            'force_auth' => 'true',
            'redirect_uri' => $redirect,
            'client_id' => (string) $this->cred('app_key'),
        ]);
    }

    public function exchangeCode(string $code): void
    {
        $data = $this->call(self::TOKEN_BASE, '/auth/token/create', ['code' => $code]);
        $this->storeTokens($data);
    }

    public function ensureFreshToken(): void
    {
        if ((int) $this->cred('expires_at', 0) > time() + 300) {
            return;
        }

        $data = $this->call(self::TOKEN_BASE, '/auth/token/refresh', [
            'refresh_token' => (string) $this->cred('refresh_token'),
        ]);
        $this->storeTokens($data);
    }

    /** @return array<int, array<string, mixed>> rows for MarketplaceClient::mapOrder() */
    public function pullOrders(int $days = 7, int $limit = 50): array
    {
        $this->ensureFreshToken();

        $list = $this->call(self::BASE_URL, '/orders/get', [
            'access_token' => (string) $this->cred('access_token'),
            'created_after' => now()->subDays($days)->toIso8601String(),
            'limit' => min($limit, 100),
            'sort_direction' => 'DESC',
        ]);

        $rows = [];
        foreach ($list['orders'] ?? [] as $order) {
            $buyer = trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
            $itemCount = (int) ($order['items_count'] ?? 1);

            $rows[] = [
                'order_sn' => (string) ($order['order_number'] ?? $order['order_id']),
                'buyer_username' => $buyer !== '' ? $buyer : 'Lazada buyer',
                'buyer_phone' => $order['address_shipping']['phone'] ?? null,
                'item_name' => 'Lazada order' . ($itemCount > 1 ? " ({$itemCount} items)" : ''),
                'qty' => max(1, $itemCount),
                'total_amount' => (float) ($order['price'] ?? 0),
                'is_custom' => false,
                'create_time' => $order['created_at'] ?? now()->toIso8601String(),
            ];
        }

        return $rows;
    }

    // ─── signing & transport ────────────────────────────────────────────

    private function call(string $base, string $apiPath, array $params): array
    {
        $params = array_merge($params, [
            'app_key' => (string) $this->cred('app_key'),
            'sign_method' => 'sha256',
            'timestamp' => (string) (int) (microtime(true) * 1000),
        ]);
        $params['sign'] = $this->sign($apiPath, $params);

        $response = Http::timeout(15)->acceptJson()->get($base . $apiPath, $params);
        $json = $response->json();

        if (!is_array($json)) {
            throw new \RuntimeException("Lazada API returned an unreadable response for {$apiPath}.");
        }
        if (($json['code'] ?? '0') !== '0') {
            throw new \RuntimeException("Lazada API error on {$apiPath}: " . ($json['message'] ?? $json['code']));
        }

        return $json['data'] ?? $json;
    }

    /** HMAC-SHA256 of path + sorted key/value pairs, uppercase hex. */
    private function sign(string $apiPath, array $params): string
    {
        ksort($params);
        $base = $apiPath;
        foreach ($params as $k => $v) {
            $base .= $k . $v;
        }

        return strtoupper(hash_hmac('sha256', $base, (string) $this->cred('app_secret')));
    }

    private function storeTokens(array $data): void
    {
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Lazada did not return an access token.');
        }

        $this->channel->update(['credentials' => array_merge($this->channel->credentials ?? [], [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $this->cred('refresh_token'),
            'expires_at' => time() + (int) ($data['expires_in'] ?? 604800),
        ])]);

        $this->channel->refresh();
    }

    private function cred(string $key, mixed $default = null): mixed
    {
        return $this->channel->credentials[$key] ?? $default;
    }
}
