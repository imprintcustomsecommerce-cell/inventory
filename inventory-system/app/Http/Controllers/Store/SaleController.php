<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Models\InventoryItem;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    use ExportsCsv;

    public function __construct(private SaleService $sales)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->canSell(), 403);
        $user = $request->user();
        $query = Sale::query()->visibleTo($user)->with(['warehouse', 'user'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $revenue = (float) (clone $query)->sum('total');
        $count = (clone $query)->count();
        $profit = (float) (clone $query)->reorder()->selectRaw('COALESCE(SUM(total - (unit_cost * quantity)), 0) as p')->value('p');
        $showProfit = $user->isAdmin();

        $sales = $query->paginate(50)->withQueryString();

        return role_view('store.sales.index', compact('sales', 'revenue', 'count', 'profit', 'showProfit'));
    }

    /** Show the sell form for a specific stock item. */
    public function create(InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

        return role_view('store.sales.create', ['item' => $inventoryItem]);
    }

    public function store(Request $request, InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01|max:' . max(0.01, $inventoryItem->current_stock),
            'unit_price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        $sale = $this->sales->sell($inventoryItem, (float) $data['quantity'], (float) $data['unit_price'], $data['remarks'] ?? null);

        if ($sale === false) {
            return back()->withInput()->with('error', 'Not enough stock to sell.');
        }

        return redirect()->route('sales.index')->with('success', 'Sale recorded: ₱' . number_format($sale->total, 2) . '.');
    }

    public function receipt(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->warehouse_id && $sale->warehouse_id !== $user->warehouse_id) {
            abort(403);
        }

        $sale->load(['warehouse', 'user']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('store.sales.receipt', compact('sale'))
            ->setPaper('a6') // compact receipt size
            ->stream("receipt-{$sale->id}.pdf");
    }

    public function export(Request $request)
    {
        abort_unless($request->user()->canSell(), 403);
        $user = $request->user();
        $query = Sale::query()->visibleTo($user)->with(['warehouse', 'user'])->latest();
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $rows = $query->get()->map(fn (Sale $s) => [
            $s->created_at->format('Y-m-d H:i'),
            $s->warehouse?->name,
            $s->item_label,
            $s->quantity,
            number_format((float) $s->unit_price, 2, '.', ''),
            number_format((float) $s->total, 2, '.', ''),
            $s->user?->name,
        ]);

        return $this->streamXlsx(
            'sales-' . now()->format('Y-m-d') . '.xlsx',
            ['Date', 'Location', 'Item', 'Qty', 'Unit Price', 'Total', 'Sold By'],
            $rows
        );
    }

    private function guard(InventoryItem $item): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->warehouse_id && $item->warehouse_id !== $user->warehouse_id) {
            abort(403, 'This item belongs to another location.');
        }
        // Stock can only be sold from a store or event, never the stockroom.
        if (!$item->warehouse || !$item->warehouse->sellsStock()) {
            abort(403, 'This item is in the stockroom and cannot be sold here. Transfer it to a store first.');
        }
    }
}
