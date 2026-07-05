<?php

namespace App\Console\Commands;

use App\Models\SalesChannel;
use App\Services\MarketplaceClient;
use Illuminate\Console\Command;

class SyncChannels extends Command
{
    protected $signature = 'channels:sync';

    protected $description = 'Pull new orders from every live (authorized) sales channel';

    public function handle(MarketplaceClient $marketplace): int
    {
        $channels = SalesChannel::where('status', 'connected')->get()
            ->filter(fn ($c) => $c->isLive());

        if ($channels->isEmpty()) {
            $this->info('No live channels to sync.');

            return self::SUCCESS;
        }

        foreach ($channels as $channel) {
            $result = $marketplace->pullOrders($channel);
            $channel->update(['last_synced_at' => now()]);

            $result['ok']
                ? $this->info("{$channel->name}: {$result['created']} new order(s).")
                : $this->error("{$channel->name}: " . ($result['error'] ?? 'sync failed'));
        }

        return self::SUCCESS;
    }
}
