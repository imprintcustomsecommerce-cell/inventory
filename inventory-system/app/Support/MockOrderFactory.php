<?php

namespace App\Support;

use App\Models\OnlineOrder;
use App\Models\SalesChannel;

/**
 * Generates believable sample marketplace orders for the mock integration.
 * Replace with real webhook ingestion when going live.
 */
class MockOrderFactory
{
    private const BUYERS = [
        'Maria Santos', 'Juan dela Cruz', 'Liza Reyes', 'Mark Villanueva',
        'Andrea Lim', 'Paolo Garcia', 'Kim Bautista', 'Nico Aquino',
    ];

    private const STOCK_ITEMS = [
        'Plain White Tee (M)', 'Black Hoodie (L)', 'Tote Bag', 'Ceramic Mug 11oz',
    ];

    private const CUSTOM_ITEMS = [
        'Custom Printed Shirt - Team Logo', 'Personalized Tumbler', 'Event Lanyard (50pcs)',
        'Custom Tarpaulin 3x5', 'Embroidered Cap - Company',
    ];

    public static function generateForChannel(SalesChannel $channel, int $count = 1): int
    {
        for ($i = 0; $i < $count; $i++) {
            $custom = (bool) random_int(0, 1);
            $qty = $custom ? random_int(10, 100) : random_int(1, 5);
            $unit = $custom ? random_int(120, 450) : random_int(150, 900);

            OnlineOrder::create([
                'sales_channel_id' => $channel->id,
                'external_ref' => strtoupper($channel->platform) . '-' . random_int(100000, 999999),
                'buyer_name' => self::BUYERS[array_rand(self::BUYERS)],
                'buyer_contact' => '09' . random_int(100000000, 999999999),
                'item_label' => $custom
                    ? self::CUSTOM_ITEMS[array_rand(self::CUSTOM_ITEMS)]
                    : self::STOCK_ITEMS[array_rand(self::STOCK_ITEMS)],
                'quantity' => $qty,
                'amount' => $qty * $unit,
                'order_type' => $custom ? 'custom' : 'stock',
                'status' => 'New',
                'ordered_at' => now()->subMinutes(random_int(0, 2880)),
            ]);
        }

        return $count;
    }
}
