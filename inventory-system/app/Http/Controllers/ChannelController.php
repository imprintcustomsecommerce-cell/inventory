<?php

namespace App\Http\Controllers;

use App\Models\SalesChannel;
use App\Services\MarketplaceClient;

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

        return view('channels.index', compact('channels'));
    }

    public function toggle(SalesChannel $channel)
    {
        $connecting = !$channel->isConnected();

        $channel->update([
            'status' => $connecting ? 'connected' : 'disconnected',
            'last_synced_at' => $connecting ? now() : $channel->last_synced_at,
        ]);

        // In a real integration this is where the OAuth handshake would run.
        $msg = $connecting
            ? "{$channel->name} connected (mock). Use Sync to pull sample orders."
            : "{$channel->name} disconnected.";

        return back()->with('success', $msg);
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
}
