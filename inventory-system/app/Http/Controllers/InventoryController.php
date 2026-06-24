<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockOutRequest;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ExportsCsv;

    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $items = $query->orderBy('name')->paginate(50);
        $categories = InventoryItem::whereNotNull('category')->distinct()->pluck('category')->sort();
        $stats = $this->inventoryService->getStatistics();

        return view('inventory.index', compact('items', 'categories', 'stats'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(StoreInventoryItemRequest $request)
    {
        InventoryItem::create($request->validated());
        return redirect()->route('inventory.index')->with('success', 'Item created successfully.');
    }

    public function edit(InventoryItem $inventoryItem)
    {
        return view('inventory.edit', ['item' => $inventoryItem]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem)
    {
        $inventoryItem->update($request->validated());
        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();
        return redirect()->route('inventory.index')->with('success', 'Item deleted successfully.');
    }

    public function stockInForm(InventoryItem $inventoryItem)
    {
        return view('inventory.stock-in', ['item' => $inventoryItem]);
    }

    public function stockIn(StockInRequest $request, InventoryItem $inventoryItem)
    {
        $this->inventoryService->stockIn(
            $inventoryItem,
            $request->input('quantity'),
            $request->input('reference'),
            $request->input('remarks')
        );

        return redirect()->route('inventory.index')->with('success', 'Stock added successfully.');
    }

    public function stockOutForm(InventoryItem $inventoryItem)
    {
        return view('inventory.stock-out', ['item' => $inventoryItem]);
    }

    public function stockOut(StockOutRequest $request, InventoryItem $inventoryItem)
    {
        $result = $this->inventoryService->stockOut(
            $inventoryItem,
            $request->input('quantity'),
            $request->input('reference'),
            $request->input('remarks')
        );

        if (!$result) {
            return back()->with('error', 'Not enough stock available.');
        }

        return redirect()->route('inventory.index')->with('success', 'Stock deducted successfully.');
    }

    public function adjustForm(InventoryItem $inventoryItem)
    {
        return view('inventory.adjust', ['item' => $inventoryItem]);
    }

    public function adjustStock(AdjustStockRequest $request, InventoryItem $inventoryItem)
    {
        $this->inventoryService->adjustStock(
            $inventoryItem,
            $request->input('actual_stock'),
            $request->input('reference'),
            $request->input('remarks')
        );

        return redirect()->route('inventory.index')->with('success', 'Stock adjusted successfully.');
    }

    public function movements(InventoryItem $inventoryItem)
    {
        $movements = $inventoryItem->movements()->with('user')->latest()->paginate(50);
        return view('inventory.movements', ['item' => $inventoryItem, 'movements' => $movements]);
    }

    public function allMovements(Request $request)
    {
        $query = InventoryMovement::with(['item', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            })->orWhere('reference', 'like', '%' . $search . '%')
                ->orWhere('remarks', 'like', '%' . $search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $movements = $query->paginate(100);

        return view('inventory.all-movements', compact('movements'));
    }

    public function lowStock(Request $request)
    {
        $query = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'out_of_stock') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->input('status') === 'low_stock') {
                $query->where('current_stock', '>', 0);
            }
        }

        $items = $query->orderBy('current_stock')->paginate(50);

        return view('inventory.low-stock', compact('items'));
    }

    public function export(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $items = $query->orderBy('name')->get();

        $rows = $items->map(fn (InventoryItem $i) => [
            $i->name,
            $i->category,
            $i->size,
            $i->unit,
            $i->current_stock,
            $i->minimum_stock,
            number_format((float) $i->unit_cost, 2, '.', ''),
            number_format($i->current_stock * (float) $i->unit_cost, 2, '.', ''),
            $i->getStatusLabel(),
        ]);

        return $this->streamCsv(
            'inventory-' . now()->format('Y-m-d') . '.csv',
            ['Item', 'Category', 'Size', 'Unit', 'Current Stock', 'Minimum Stock', 'Unit Cost', 'Stock Value', 'Status'],
            $rows
        );
    }

    public function exportMovements(Request $request)
    {
        $query = InventoryMovement::with(['item', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            })->orWhere('reference', 'like', '%' . $search . '%')
                ->orWhere('remarks', 'like', '%' . $search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $rows = $query->get()->map(fn (InventoryMovement $m) => [
            $m->created_at->format('Y-m-d H:i'),
            $m->item?->name,
            $m->item?->category,
            $m->getTypeLabel(),
            $m->quantity,
            $m->item?->unit,
            $m->reference,
            $m->user?->name,
            $m->remarks,
        ]);

        return $this->streamCsv(
            'stock-movements-' . now()->format('Y-m-d') . '.csv',
            ['Date', 'Item', 'Category', 'Type', 'Quantity', 'Unit', 'Reference', 'By', 'Remarks'],
            $rows
        );
    }
}
