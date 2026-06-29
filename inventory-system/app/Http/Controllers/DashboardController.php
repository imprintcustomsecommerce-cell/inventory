<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\OnlineOrder;
use App\Models\Project;
use App\Models\ProjectDelivery;
use App\Models\ProjectFeedback;
use App\Models\ProjectIssue;
use App\Models\PurchaseOrder;
use App\Models\Quote;
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

        $crm = [
            'customers' => Customer::count(),
            'open_quotes' => Quote::whereIn('status', ['Draft', 'Sent'])->count(),
            'pipeline' => (float) Quote::whereIn('status', ['Sent', 'Approved'])->sum('total'),
            'receivables' => (float) Invoice::whereIn('status', ['Unpaid', 'Partial'])->sum(DB::raw('total - amount_paid')),
            'overdue_invoices' => Invoice::whereIn('status', ['Unpaid', 'Partial'])
                ->whereNotNull('due_date')->whereDate('due_date', '<', today())->count(),
        ];

        $recentQuotes = Quote::with('customer')->latest()->limit(6)->get();

        // ── Operations: the production/fulfilment modules ──
        $operations = [
            'new_orders' => OnlineOrder::where('status', 'New')->count(),
            'in_transit' => ProjectDelivery::whereIn('status', ['Scheduled', 'Out for Delivery'])->count(),
            'open_issues' => ProjectIssue::whereIn('status', ['Open', 'In Progress'])->count(),
            'avg_rating' => round((float) ProjectFeedback::avg('rating'), 1),
            'feedback_count' => ProjectFeedback::count(),
        ];

        // ── "Needs attention" — actionable items slipping across the business ──
        $alerts = [];

        $overdueInvoices = Invoice::whereIn('status', ['Unpaid', 'Partial'])
            ->whereNotNull('due_date')->whereDate('due_date', '<', today())->get();
        if ($overdueInvoices->count() > 0) {
            $alerts[] = [
                'tone' => 'red',
                'label' => $overdueInvoices->count() . ' overdue ' . str('invoice')->plural($overdueInvoices->count()),
                'detail' => '₱' . number_format($overdueInvoices->sum(fn (Invoice $i) => $i->balance()), 2) . ' outstanding',
                'route' => route('invoices.index', ['status' => 'Unpaid']),
            ];
        }

        if ($operations['new_orders'] > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'label' => $operations['new_orders'] . ' new online ' . str('order')->plural($operations['new_orders']),
                'detail' => 'Waiting to be routed to a project or sale',
                'route' => route('online-orders.index'),
            ];
        }

        if ($operations['open_issues'] > 0) {
            $alerts[] = [
                'tone' => 'red',
                'label' => $operations['open_issues'] . ' open quality ' . str('issue')->plural($operations['open_issues']),
                'detail' => 'Defects, reprints, or returns to resolve',
                'route' => route('quality.index'),
            ];
        }

        $expiredQuotes = Quote::where('status', 'Sent')
            ->whereNotNull('valid_until')->whereDate('valid_until', '<', today())->count();
        if ($expiredQuotes > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'label' => $expiredQuotes . ' expired ' . str('quote')->plural($expiredQuotes),
                'detail' => 'Sent but past their validity date',
                'route' => route('quotes.index', ['status' => 'Sent']),
            ];
        }

        $dueProjects = Project::whereIn('status', $active)
            ->whereNotNull('due_date')->whereDate('due_date', '<=', today()->addDays(3))->count();
        if ($dueProjects > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'label' => $dueProjects . ' ' . str('project')->plural($dueProjects) . ' due soon',
                'detail' => 'Due within 3 days or already overdue',
                'route' => route('projects.index'),
            ];
        }

        if ($inventory['low_stock'] + $inventory['out_of_stock'] > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'label' => ($inventory['low_stock'] + $inventory['out_of_stock']) . ' items low or out of stock',
                'detail' => $inventory['out_of_stock'] . ' out of stock',
                'route' => route('inventory.lowStock'),
            ];
        }

        // Materials & purchasing alerts are only relevant to the stockroom side.
        if ($user->canSeeMaterials()) {
            $overduePos = PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received'])
                ->whereNotNull('expected_date')->whereDate('expected_date', '<', today())->count();
            if ($overduePos > 0) {
                $alerts[] = [
                    'tone' => 'amber',
                    'label' => $overduePos . ' overdue purchase ' . str('order')->plural($overduePos),
                    'detail' => 'Past expected delivery date',
                    'route' => route('purchases.index'),
                ];
            }

            $lowMaterials = Material::query()->visibleTo($user)
                ->whereColumn('current_stock', '<=', 'minimum_stock')->count();
            if ($lowMaterials > 0) {
                $alerts[] = [
                    'tone' => 'amber',
                    'label' => $lowMaterials . ' ' . str('material')->plural($lowMaterials) . ' below minimum',
                    'detail' => 'At or below minimum stock — consider a purchase order',
                    'route' => route('materials.index', ['status' => 'low']),
                ];
            }
        }

        return view('dashboard', compact(
            'inventory',
            'projects',
            'lowStockItems',
            'upcomingProjects',
            'recentMovements',
            'sales',
            'recentSales',
            'crm',
            'recentQuotes',
            'operations',
            'alerts'
        ));
    }
}
