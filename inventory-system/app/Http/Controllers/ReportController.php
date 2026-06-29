<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $now = now();

        // ── Sales: this month revenue + gross profit, and a 6-month trend ──
        $monthSales = Sale::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)->get();

        $sales = [
            'revenue_month' => (float) $monthSales->sum('total'),
            'profit_month' => (float) $monthSales->sum(fn (Sale $s) => $s->profit()),
            'orders_month' => $monthSales->count(),
        ];

        $trend = collect(range(5, 0))->map(function ($back) use ($now) {
            $month = $now->copy()->subMonths($back);
            $revenue = (float) Sale::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)->sum('total');

            return ['label' => $month->format('M'), 'revenue' => $revenue];
        });
        $trendMax = max(1, $trend->max('revenue'));

        $topProducts = Sale::query()
            ->selectRaw('item_label, SUM(total) as revenue, SUM(quantity) as qty')
            ->groupBy('item_label')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ── Quotes pipeline + conversion ──
        $quoteTotal = Quote::count();
        $quotes = [
            'total' => $quoteTotal,
            'pipeline' => (float) Quote::whereIn('status', ['Sent', 'Approved'])->sum('total'),
            'won' => Quote::whereIn('status', ['Approved', 'Converted'])->count(),
            'conversion' => $quoteTotal > 0
                ? round(Quote::whereIn('status', ['Approved', 'Converted'])->count() / $quoteTotal * 100)
                : 0,
            'by_status' => collect(Quote::STATUSES)->mapWithKeys(
                fn ($s) => [$s => Quote::where('status', $s)->count()]
            ),
        ];

        // ── Invoices: receivables + collections + aging ──
        $open = Invoice::whereIn('status', ['Unpaid', 'Partial'])->get();
        $invoices = [
            'receivables' => (float) $open->sum(fn (Invoice $i) => $i->balance()),
            'overdue' => (float) $open->filter(fn (Invoice $i) => $i->isOverdue())
                ->sum(fn (Invoice $i) => $i->balance()),
            'collected_month' => (float) \App\Models\Payment::whereMonth('paid_at', $now->month)
                ->whereYear('paid_at', $now->year)->sum('amount'),
            'by_status' => collect(Invoice::STATUSES)->mapWithKeys(
                fn ($s) => [$s => Invoice::where('status', $s)->count()]
            ),
        ];

        // ── Projects: status mix + total margin on completed/active work ──
        $projectList = Project::with('materials.inventoryItem')->get();
        $projects = [
            'by_status' => collect(Project::STATUSES)->mapWithKeys(
                fn ($s) => [$s => $projectList->where('status', $s)->count()]
            ),
            'margin' => (float) $projectList->sum(fn (Project $p) => $p->margin() ?? 0),
            'quoted' => (float) $projectList->sum(fn (Project $p) => (float) ($p->quoted_price ?? 0)),
        ];

        // ── Purchasing: spend this month, open commitments, top suppliers ──
        $purchasing = [
            'spend_month' => (float) PurchaseOrder::whereMonth('order_date', $now->month)
                ->whereYear('order_date', $now->year)->sum('total'),
            'open_value' => (float) PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received'])->sum('total'),
            'by_supplier' => PurchaseOrder::query()
                ->with('supplier')
                ->selectRaw('supplier_id, SUM(total) as spend')
                ->groupBy('supplier_id')
                ->orderByDesc('spend')
                ->limit(5)
                ->get(),
        ];

        // ── Expenses & net profit (gross sales profit − overhead this month) ──
        $monthExpenses = Expense::whereMonth('expense_date', $now->month)
            ->whereYear('expense_date', $now->year)->get();

        $expenses = [
            'total_month' => (float) $monthExpenses->sum('amount'),
            'net_profit' => $sales['profit_month'] - (float) $monthExpenses->sum('amount'),
            'by_category' => $monthExpenses->groupBy('category')
                ->map(fn ($g) => (float) $g->sum('amount'))
                ->sortDesc(),
        ];

        return view('reports.index', compact(
            'sales', 'trend', 'trendMax', 'topProducts',
            'quotes', 'invoices', 'projects', 'purchasing', 'expenses'
        ));
    }
}
