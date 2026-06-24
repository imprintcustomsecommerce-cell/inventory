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
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use ExportsCsv;

    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /** Block staff from touching items outside their warehouse. */
    private function guard(InventoryItem $item): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->warehouse_id && $item->warehouse_id !== $user->warehouse_id) {
            abort(403, 'This item belongs to another warehouse.');
        }
    }

    private function statsFor($user): array
    {
        $base = fn () => InventoryItem::query()->visibleTo($user);

        return [
            'total_items' => $base()->count(),
            'low_stock_items' => $base()->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0)->count(),
            'out_of_stock_items' => $base()->where('current_stock', '<=', 0)->count(),
            'total_movements' => InventoryMovement::whereHas('item', fn ($q) => $q->visibleTo($user))->count(),
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = InventoryItem::query()->visibleTo($user)->with('warehouse');

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

        // Admins can additionally filter by warehouse.
        if ($user->isAdmin() && $request->filled('warehouse')) {
            $query->where('warehouse_id', $request->input('warehouse'));
        }

        $items = $query->orderBy('name')->paginate(50)->withQueryString();
        $categories = InventoryItem::query()->visibleTo($user)->whereNotNull('category')->distinct()->pluck('category')->sort();
        $warehouses = $user->isAdmin() ? Warehouse::orderBy('name')->get() : collect();
        $stats = $this->statsFor($user);

        return view('inventory.index', compact('items', 'categories', 'stats', 'warehouses'));
    }

    public function create()
    {
        $warehouses = auth()->user()->isAdmin() ? Warehouse::orderBy('name')->get() : collect();

        return view('inventory.create', compact('warehouses'));
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // Staff create in their own warehouse; admins choose one.
        $data['warehouse_id'] = $user->isAdmin()
            ? ($data['warehouse_id'] ?? null)
            : $user->warehouse_id;

        if (!$data['warehouse_id']) {
            return back()->withInput()->with('error', 'Please choose a warehouse for this item.');
        }

        InventoryItem::create($data);

        return redirect()->route('inventory.index')->with('success', 'Item created successfully.');
    }

    public function edit(InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);
        $warehouses = auth()->user()->isAdmin() ? Warehouse::orderBy('name')->get() : collect();

        return view('inventory.edit', ['item' => $inventoryItem, 'warehouses' => $warehouses]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);
        $data = $request->validated();

        if (!$request->user()->isAdmin()) {
            unset($data['warehouse_id']);
        }

        $inventoryItem->update($data);

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);
        $inventoryItem->delete();

        return redirect()->route('inventory.index')->with('success', 'Item deleted successfully.');
    }

    public function stockInForm(InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

        return view('inventory.stock-in', ['item' => $inventoryItem]);
    }

    public function stockIn(StockInRequest $request, InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

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
        $this->guard($inventoryItem);

        return view('inventory.stock-out', ['item' => $inventoryItem]);
    }

    public function stockOut(StockOutRequest $request, InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

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
        $this->guard($inventoryItem);

        return view('inventory.adjust', ['item' => $inventoryItem]);
    }

    public function adjustStock(AdjustStockRequest $request, InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

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
        $this->guard($inventoryItem);
        $movements = $inventoryItem->movements()->with('user')->latest()->paginate(50);

        return view('inventory.movements', ['item' => $inventoryItem, 'movements' => $movements]);
    }

    public function transferForm(InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);
        $warehouses = Warehouse::where('id', '!=', $inventoryItem->warehouse_id)->orderBy('name')->get();

        return view('inventory.transfer', ['item' => $inventoryItem, 'warehouses' => $warehouses]);
    }

    public function transfer(Request $request, InventoryItem $inventoryItem)
    {
        $this->guard($inventoryItem);

        $data = $request->validate([
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01|max:' . $inventoryItem->current_stock,
            'remarks' => 'nullable|string|max:255',
        ]);

        if ((int) $data['destination_warehouse_id'] === (int) $inventoryItem->warehouse_id) {
            return back()->with('error', 'Choose a different destination warehouse.');
        }

        $destination = Warehouse::findOrFail($data['destination_warehouse_id']);
        $ok = $this->inventoryService->transfer($inventoryItem, $destination, (float) $data['quantity'], $data['remarks'] ?? null);

        if (!$ok) {
            return back()->with('error', 'Transfer failed — check the available stock.');
        }

        return redirect()->route('inventory.index')
            ->with('success', "Transferred {$data['quantity']} to {$destination->name}.");
    }

    public function importForm()
    {
        return view('inventory.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'The file appears to be empty.');
        }
        // Strip a UTF-8 BOM from the first header cell, if present.
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $col = array_flip(array_map('trim', $header));

        $get = fn (array $row, string $name) => isset($col[$name]) && isset($row[$col[$name]]) ? trim($row[$col[$name]]) : null;

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($handle, $get, &$created, &$updated, &$skipped) {
            while (($row = fgetcsv($handle)) !== false) {
                $name = $get($row, 'Item');
                if (!$name) {
                    $skipped++;
                    continue;
                }

                $warehouseName = $get($row, 'Warehouse');
                $warehouseId = $warehouseName
                    ? Warehouse::firstOrCreate(['name' => $warehouseName])->id
                    : null;

                $item = InventoryItem::updateOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'name' => $name,
                        'size' => $get($row, 'Size') ?: null,
                    ],
                    [
                        'category' => $get($row, 'Category') ?: null,
                        'unit' => $get($row, 'Unit') ?: 'pcs',
                        'current_stock' => (float) ($get($row, 'Current Stock') ?? 0),
                        'minimum_stock' => (float) ($get($row, 'Minimum Stock') ?? 0),
                        'unit_cost' => (float) ($get($row, 'Unit Cost') ?? 0),
                        'status' => 'active',
                    ]
                );

                $item->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        fclose($handle);

        return redirect()->route('inventory.index')
            ->with('success', "Import complete: {$created} added, {$updated} updated" . ($skipped ? ", {$skipped} skipped." : '.'));
    }

    public function allMovements(Request $request)
    {
        $user = $request->user();
        $query = InventoryMovement::with(['item.warehouse', 'user'])
            ->whereHas('item', fn ($q) => $q->visibleTo($user))
            ->latest();

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
        $user = $request->user();
        $query = InventoryItem::query()->visibleTo($user)->with('warehouse')
            ->whereColumn('current_stock', '<=', 'minimum_stock');

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

        $items = $query->orderBy('current_stock')->paginate(50)->withQueryString();

        return view('inventory.low-stock', compact('items'));
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $query = InventoryItem::query()->visibleTo($user)->with('warehouse');

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

        if ($user->isAdmin() && $request->filled('warehouse')) {
            $query->where('warehouse_id', $request->input('warehouse'));
        }

        $items = $query->orderBy('name')->get();

        $rows = $items->map(fn (InventoryItem $i) => [
            $i->warehouse?->name,
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
            ['Warehouse', 'Item', 'Category', 'Size', 'Unit', 'Current Stock', 'Minimum Stock', 'Unit Cost', 'Stock Value', 'Status'],
            $rows
        );
    }

    public function exportMovements(Request $request)
    {
        $user = $request->user();
        $query = InventoryMovement::with(['item.warehouse', 'user'])
            ->whereHas('item', fn ($q) => $q->visibleTo($user))
            ->latest();

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
            $m->item?->warehouse?->name,
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
            ['Date', 'Warehouse', 'Item', 'Category', 'Type', 'Quantity', 'Unit', 'Reference', 'By', 'Remarks'],
            $rows
        );
    }
}
