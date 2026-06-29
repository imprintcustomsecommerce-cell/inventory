<?php

namespace App\Support;

/**
 * Produces API-shaped sample order payloads, mimicking what each
 * marketplace would return from its "get orders" endpoint. This is served
 * by the in-app mock API; replace it with the real API response when going
 * live. Field names are intentionally platform-flavoured so the client's
 * mapper has something realistic to normalise.
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

    /**
     * Build a list of raw marketplace order rows for a platform.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function payload(string $platform, int $count = 1): array
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $custom = (bool) random_int(0, 1);
            $qty = $custom ? random_int(10, 100) : random_int(1, 5);
            $unit = $custom ? random_int(120, 450) : random_int(150, 900);
            $item = $custom
                ? self::CUSTOM_ITEMS[array_rand(self::CUSTOM_ITEMS)]
                : self::STOCK_ITEMS[array_rand(self::STOCK_ITEMS)];

            $rows[] = [
                'order_sn' => strtoupper($platform) . '-' . random_int(100000, 999999),
                'buyer_username' => self::BUYERS[array_rand(self::BUYERS)],
                'buyer_phone' => '09' . random_int(100000000, 999999999),
                'item_name' => $item,
                'qty' => $qty,
                'total_amount' => $qty * $unit,
                'is_custom' => $custom,
                'create_time' => now()->subMinutes(random_int(0, 2880))->toIso8601String(),
            ];
        }

        return $rows;
    }
}
