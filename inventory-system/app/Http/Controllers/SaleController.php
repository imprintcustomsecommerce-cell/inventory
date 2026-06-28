<?php

namespace App\Http\Controllers;

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
        $user = $request->user();
        $query = Sale::query()->visibleTo($user)->with(['warehouse', 'user'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $totalQuery = (clone $query);
        $revenue = (float) $totalQuery->sum('total');
        $count = (clone $query)->count();

        $sales = $query->paginate(50)->withQueryString();

        return view('sales.index', compact('sales', 'revenue', 'count'));
    }

    /** Show the sell form for a specific stock item. */
    public function create(InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

        return view('sales.create', ['item' => $inventoryItem]);
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

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.receipt', compact('sale'))
            ->setPaper('a6') // compact receipt size
            ->stream("receipt-{$sale->id}.pdf");
    }

    public function export(Request $request)
    {
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
    }
}
