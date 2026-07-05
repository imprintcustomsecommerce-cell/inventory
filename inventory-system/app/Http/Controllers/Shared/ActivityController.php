<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $activities = $query->latest()->paginate(50)->withQueryString();

        // Mark the feed as seen for the current user.
        $request->user()->update(['activity_seen_at' => now()]);

        return role_view('shared.activities.index', compact('activities'));
    }
}
