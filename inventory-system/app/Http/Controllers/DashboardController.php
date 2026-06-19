<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stockValue = (float) InventoryItem::select(
            DB::raw('COALESCE(SUM(current_stock * unit_cost), 0) as total')
        )->value('total');

        $inventory = [
            'total_items' => InventoryItem::count(),
            'low_stock' => InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0)->count(),
            'out_of_stock' => InventoryItem::where('current_stock', '<=', 0)->count(),
            'stock_value' => $stockValue,
        ];

        $active = ['Pending', 'For Design', 'For Sample', 'For Approval', 'For Production'];
        $projects = [
            'active' => Project::whereIn('status', $active)->count(),
            'in_production' => Project::where('status', 'For Production')->count(),
            'overdue' => Project::whereIn('status', $active)->whereNotNull('due_date')->whereDate('due_date', '<', today())->count(),
            'completed' => Project::where('status', 'Completed')->count(),
        ];

        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->limit(6)
            ->get();

        $upcomingProjects = Project::whereIn('status', $active)
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $recentMovements = InventoryMovement::with(['item', 'user'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'inventory',
            'projects',
            'lowStockItems',
            'upcomingProjects',
            'recentMovements'
        ));
    }
}
