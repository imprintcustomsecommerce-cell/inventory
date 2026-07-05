<?php

namespace App\Services\Marketplaces;

use App\Models\SalesChannel;
use Illuminate\Support\Facades\Http;

/**
 * TikTok Shop Partner API client (Open Platform, 202309 API version).
 *
 * Credentials on the channel (encrypted):
 *   app_key, app_secret               — from partner.tiktokshop.com
 *   access_token, refresh_token,
 *   expires_at, shop_cipher, shop_id  — set by the authorization callback.
 *
 * NOTE: TikTok evolves its API versions faster than the others; if a call
 * fails after go-live, check the current version path in the seller docs —
 * only the version segment (e.g. 202309) usually changes.
 */
class TikTokClient
{
    public const BASE_URL = 'https://open-api.tiktokglobalshop.com';
    public const AUTH_BASE = 'https://auth.tiktok-shops.com';
    public const AUTHORIZE_URL = 'https://services.tiktokshop.com/open/authorize';

    public function __construct(private SalesChannel $channel)
    {
    }

    public function authUrl(string $state = ''): string
    {
        // TikTok redirects to the URL registered on the app console,
        // so no redirect param here — set it in the Partner Center.
        return self::AUTHORIZE_URL . '?' . http_build_query([
            'app_key' => (string) $this->cred('app_key'),
            'state' => $state,
        ]);
    }

    public function exchangeCode(string $code): void
    {
        $json = Http::timeout(15)->acceptJson()->get(self::AUTH_BASE . '/api/v2/token/get', [
            'app_key' => (string) $this->cred('app_key'),
            'app_secret' => (string) $this->cred('app_secret'),
            'auth_code' => $code,
            'grant_type' => 'authorized_code',
        ])->json();

        $this->storeTokens($this->parseAuth($json));
        $this->fetchShopCipher();
    }

    public function ensureFreshToken(): void
    {
        if ((int) $this->cred('expires_at', 0) > time() + 300) {
            return;
        }

        $json = Http::timeout(15)->acceptJson()->get(self::AUTH_BASE . '/api/v2/token/refresh', [
            'app_key' => (string) $this->cred('app_key'),
            'app_secret' => (string) $this->cred('app_secret'),
            'refresh_token' => (string) $this->cred('refresh_token'),
            'grant_type' => 'refresh_token',
        ])->json();

        $this->storeTokens($this->parseAuth($json));
    }

    /** @return array<int, array<string, mixed>> rows for MarketplaceClient::mapOrder() */
    public function pullOrders(int $days = 7, int $pageSize = 50): array
    {
        $this->ensureFreshToken();

        $data = $this->signedPost('/order/202309/orders/search', [
            'shop_cipher' => (string) $this->cred('shop_cipher'),
            'page_size' => min($pageSize, 100),
        ], [
            'create_time_ge' => now()->subDays($days)->timestamp,
            'create_time_lt' => now()->timestamp,
        ]);

        $rows = [];
        foreach ($data['orders'] ?? [] as $order) {
            $items = $order['line_items'] ?? [];
            $first = $items[0]['product_name'] ?? 'TikTok Shop order';
            $extra = count($items) - 1;

            $rows[] = [
                'order_sn' => (string) $order['id'],
                'buyer_username' => $order['recipient_address']['name'] ?? 'TikTok buyer',
                'buyer_phone' => $order['recipient_address']['phone_number'] ?? null,
                'item_name' => $extra > 0 ? "{$first} (+{$extra} more)" : $first,
                'qty' => max(1, count($items)),
                'total_amount' => (float) ($order['payment']['total_amount'] ?? 0),
                'is_custom' => false,
                'create_time' => isset($order['create_time'])
                    ? date('c', (int) $order['create_time'])
                    : now()->toIso8601String(),
            ];
        }

        return $rows;
    }

    // ─── signing & transport ────────────────────────────────────────────

    /**
     * TikTok signature: HMAC-SHA256 over app_secret + path + sorted
     * query params (k+v, excluding sign/access_token) + body + app_secret.
     */
    private function signedPost(string $path, array $query, array $body): array
    {
        $query = array_merge($query, [
            'app_key' => (string) $this->cred('app_key'),
            'timestamp' => (string) time(),
        ]);

        $secret = (string) $this->cred('app_secret');
        ksort($query);

        $base = $path;
        foreach ($query as $k => $v) {
            $base .= $k . $v;
        }
        $bodyJson = empty($body) ? '' : json_encode($body);
        $query['sign'] = hash_hmac('sha256', $secret . $base . $bodyJson . $secret, $secret);

        $json = Http::timeout(15)
            ->acceptJson()
            ->withHeaders(['x-tts-access-token' => (string) $this->cred('access_token')])
            ->post(self::BASE_URL . $path . '?' . http_build_query($query), $body)
            ->json();

        if (!is_array($json)) {
            throw new \RuntimeException("TikTok API returned an unreadable response for {$path}.");
        }
        if ((int) ($json['code'] ?? 0) !== 0) {
            throw new \RuntimeException("TikTok API error on {$path}: " . ($json['message'] ?? $json['code']));
        }

        return $json['data'] ?? [];
    }

    /** After authorization, look up the shop cipher needed by shop APIs. */
    private function fetchShopCipher(): void
    {
        $data = $this->signedPost('/authorization/202309/shops', [], []);
        $shop = $data['shops'][0] ?? null;

        if ($shop) {
            $this->channel->update(['credentials' => array_merge($this->channel->credentials ?? [], [
                'shop_id' => $shop['id'] ?? null,
                'shop_cipher' => $shop['cipher'] ?? null,
            ])]);
            $this->channel->refresh();
        }
    }

    private function parseAuth(?array $json): array
    {
        if (!is_array($json) || (int) ($json['code'] ?? 1) !== 0 || empty($json['data']['access_token'])) {
            $msg = is_array($json) ? ($json['message'] ?? 'no access token returned') : 'unreadable response';
            throw new \RuntimeException("TikTok authorization failed: {$msg}");
        }

        return $json['data'];
    }

    private function storeTokens(array $data): void
    {
        $this->channel->update(['credentials' => array_merge($this->channel->credentials ?? [], [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $this->cred('refresh_token'),
            'expires_at' => (int) ($data['access_token_expire_in'] ?? time() + 86400),
        ])]);

        $this->channel->refresh();
    }

    private function cred(string $key, mixed $default = null): mixed
    {
        return $this->channel->credentials[$key] ?? $default;
    }
}
