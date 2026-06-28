<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Project;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stockValue = (float) InventoryItem::query()->visibleTo($user)->select(
            DB::raw('COALESCE(SUM(current_stock * unit_cost), 0) as total')
        )->value('total');

        $inventory = [
            'total_items' => InventoryItem::query()->visibleTo($user)->count(),
            'low_stock' => InventoryItem::query()->visibleTo($user)->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0)->count(),
            'out_of_stock' => InventoryItem::query()->visibleTo($user)->where('current_stock', '<=', 0)->count(),
            'stock_value' => $stockValue,
        ];

        $active = ['Pending', 'For Design', 'For Sample', 'For Approval', 'For Production'];
        $projects = [
            'active' => Project::whereIn('status', $active)->count(),
            'in_production' => Project::where('status', 'For Production')->count(),
            'overdue' => Project::whereIn('status', $active)->whereNotNull('due_date')->whereDate('due_date', '<', today())->count(),
            'completed' => Project::where('status', 'Completed')->count(),
        ];

        $lowStockItems = InventoryItem::query()->visibleTo($user)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->limit(6)
            ->get();

        $upcomingProjects = Project::whereIn('status', $active)
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $recentMovements = InventoryMovement::with(['item', 'user'])
            ->whereHas('item', fn ($q) => $q->visibleTo($user))
            ->latest()
            ->limit(8)
            ->get();

        $sales = [
            'today' => (float) Sale::query()->visibleTo($user)->whereDate('created_at', today())->sum('total'),
            'month' => (float) Sale::query()->visibleTo($user)
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total'),
        ];

        $recentSales = Sale::query()->visibleTo($user)->with(['warehouse', 'user'])
            ->latest()->limit(6)->get();

        return view('dashboard', compact(
            'inventory',
            'projects',
            'lowStockItems',
            'upcomingProjects',
            'recentMovements',
            'sales',
            'recentSales'
        ));
    }
}
