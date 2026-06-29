<?php

namespace App\Http\Controllers;

use App\Support\MockOrderFactory;
use Illuminate\Http\Request;

/**
 * Stand-in marketplace API. The app's own MarketplaceClient calls these
 * endpoints over HTTP, exactly as it would call Shopee/Lazada/TikTok.
 * Public (no auth) so it behaves like an external service.
 */
class MockApiController extends Controller
{
    public function orders(Request $request, string $platform)
    {
        $limit = (int) $request->integer('limit', 2);
        $limit = max(1, min($limit, 10));

        return response()->json([
            'platform' => $platform,
            'count' => $limit,
            'orders' => MockOrderFactory::payload($platform, $limit),
        ]);
    }
}
