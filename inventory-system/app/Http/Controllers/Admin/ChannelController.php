<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SalesChannel;
use App\Services\MarketplaceClient;
use App\Services\Marketplaces\LazadaClient;
use App\Services\Marketplaces\ShopeeClient;
use App\Services\Marketplaces\TikTokClient;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function __construct(private MarketplaceClient $marketplace)
    {
    }

    public function index()
    {
        $channels = SalesChannel::withCount([
            'orders',
            'orders as new_orders_count' => fn ($q) => $q->where('status', 'New'),
        ])->orderBy('name')->get();

        return role_view('admin.channels.index', compact('channels'));
    }

    public function toggle(SalesChannel $channel)
    {
        // Connecting requires a real authorized shop; disconnecting just
        // pauses syncing (credentials are kept).
        if (!$channel->isConnected()) {
            if (!$channel->isLive()) {
                return back()->with('error',
                    "{$channel->name} has no authorized shop yet — save your API keys and click “Authorize shop” under Live API setup.");
            }

            $channel->update(['status' => 'connected']);

            return back()->with('success', "{$channel->name} syncing resumed.");
        }

        $channel->update(['status' => 'disconnected']);

        return back()->with('success', "{$channel->name} syncing paused.");
    }

    public function sync(SalesChannel $channel)
    {
        if (!$channel->isConnected()) {
            return back()->with('error', "Connect {$channel->name} first.");
        }

        $result = $this->marketplace->pullOrders($channel, random_int(1, 3));
        $channel->update(['last_synced_at' => now()]);

        if (!$result['ok']) {
            return back()->with('error', $result['error'] ?? 'Sync failed.');
        }

        return back()->with('success', "Synced {$channel->name} — {$result['created']} new order(s) pulled.");
    }

    /** Save the developer-app API keys for a channel. */
    public function saveCredentials(Request $request, SalesChannel $channel)
    {
        $data = $request->validate($channel->platform === 'shopee'
            ? ['partner_id' => 'required|numeric', 'partner_key' => 'required|string|min:10']
            : ['app_key' => 'required|string|min:4', 'app_secret' => 'required|string|min:10']);

        $channel->update(['credentials' => array_merge(
            $channel->credentials ?? [],
            array_map(fn ($v) => trim((string) $v), $data)
        )]);

        return back()->with('success',
            "{$channel->name} API keys saved. Now click “Authorize shop” to link your Seller Centre shop.");
    }

    /** Send the seller to the marketplace to authorize our app for their shop. */
    public function authorize(SalesChannel $channel)
    {
        if (!$channel->hasApiCredentials()) {
            return back()->with('error', 'Save your app keys first.');
        }

        $redirect = route('channels.callback', $channel);

        $url = match ($channel->platform) {
            'shopee' => (new ShopeeClient($channel))->authUrl($redirect),
            'lazada' => (new LazadaClient($channel))->authUrl($redirect),
            'tiktok' => (new TikTokClient($channel))->authUrl((string) $channel->id),
            default => null,
        };

        return $url
            ? redirect()->away($url)
            : back()->with('error', 'Unknown platform.');
    }

    /** The marketplace redirects back here with an auth code after consent. */
    public function callback(Request $request, SalesChannel $channel)
    {
        $code = (string) $request->query('code');

        if ($code === '') {
            return redirect()->route('channels.index')
                ->with('error', "{$channel->name} authorization was cancelled or returned no code.");
        }

        try {
            match ($channel->platform) {
                'shopee' => (new ShopeeClient($channel))->exchangeCode($code, (int) $request->query('shop_id')),
                'lazada' => (new LazadaClient($channel))->exchangeCode($code),
                'tiktok' => (new TikTokClient($channel))->exchangeCode($code),
                default => throw new \RuntimeException('Unknown platform.'),
            };
        } catch (\Throwable $e) {
            return redirect()->route('channels.index')
                ->with('error', "Authorization failed: {$e->getMessage()}");
        }

        $channel->update(['status' => 'connected', 'last_synced_at' => null]);

        return redirect()->route('channels.index')
            ->with('success', "{$channel->name} is now LIVE. Click Sync to pull real orders — auto-sync runs every 15 minutes.");
    }
}
