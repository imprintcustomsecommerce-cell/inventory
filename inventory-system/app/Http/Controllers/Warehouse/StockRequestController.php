<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;

use App\Models\InventoryItem;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Services\StockRequestService;
use Illuminate\Http\Request;

class StockRequestController extends Controller
{
    public function __construct(private StockRequestService $requests)
    {
    }

    /** Stockroom staff / admins fulfill requests. */
    private function canFulfill(): bool
    {
        return auth()->user()->canCreateItems();
    }

    private function guard(StockRequest $req): void
    {
        $user = auth()->user();
        $isStockroom = $user->canCreateItems();
        if (!$isStockroom && $req->warehouse_id !== $user->warehouse_id) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $requests = StockRequest::query()->visibleTo($request->user())
            ->with(['warehouse', 'requestedBy', 'items'])->withCount('items')->latest()->paginate(50);

        return role_view('warehouse.requests.index', ['requests' => $requests, 'canFulfill' => $this->canFulfill()]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->warehouse_id) {
            return back()->with('error', 'You must belong to a location to request stock.');
        }

        $req = StockRequest::create([
            'warehouse_id' => $user->warehouse_id,
            'requested_by_id' => $user->id,
            'status' => 'pending',
            'note' => $request->input('note'),
        ]);

        return redirect()->route('requests.show', $req)->with('success', 'Request started — add the items you need.');
    }

    /** Start a request pre-filled with the location's low-stock items. */
    public function restockLow(Request $request)
    {
        $user = $request->user();
        if (!$user->warehouse_id) {
            return back()->with('error', 'You must belong to a location to request stock.');
        }

        $low = InventoryItem::where('warehouse_id', $user->warehouse_id)
            ->whereColumn('current_stock', '<=', 'minimum_stock')->get();

        if ($low->isEmpty()) {
            return back()->with('error', 'Nothing is low on stock right now.');
        }

        $req = StockRequest::create([
            'warehouse_id' => $user->warehouse_id,
            'requested_by_id' => $user->id,
            'status' => 'pending',
            'note' => 'Restock of low-stock items',
        ]);

        $added = 0;
        foreach ($low as $it) {
            // Prefer a matching stockroom item to pull from; fall back to the
            // item itself so EVERY low item is requested (not just ones the
            // stockroom currently has in stock).
            $source = InventoryItem::whereHas('warehouse', fn ($q) => $q->where('can_create_items', true))
                ->where('name', $it->name)
                ->where(fn ($q) => $it->size ? $q->where('size', $it->size) : $q->whereNull('size'))
                ->orderByDesc('current_stock')->first();

            $need = max(1, (int) ceil((float) $it->minimum_stock - (float) $it->current_stock));
            $req->items()->create([
                'inventory_item_id' => $source?->id ?? $it->id,
                'item_label' => trim($it->name . ($it->size ? " ({$it->size})" : '')),
                'quantity' => $need,
            ]);
            $added++;
        }

        return redirect()->route('requests.show', $req)
            ->with('success', "Draft request created for {$added} low item(s) — review the quantities and submit.");
    }

    /**
     * The per-item action on the low-stock list. Stockroom/warehouse staff
     * restock directly (they hold the stock); store/event staff raise a
     * request to pull it from the stockroom.
     */
    public function restockItem(Request $request, InventoryItem $item)
    {
        $user = auth()->user();

        // Warehouse/stockroom side: restock in place instead of requesting.
        if ($user->canCreateItems()) {
            return redirect()->route('inventory.stockInForm', $item->id)
                ->with('success', 'Restock ' . $item->displayName() . ' below.');
        }

        if (!$user->warehouse_id) {
            return back()->with('error', 'You must belong to a location to request stock.');
        }

        // Validate the requested quantity from the form
        $data = $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        // Find matching stock held in a stockroom to pull from.
        $source = InventoryItem::whereHas('warehouse', fn ($q) => $q->where('can_create_items', true))
            ->where('name', $item->name)
            ->where(fn ($q) => $item->size ? $q->where('size', $item->size) : $q->whereNull('size'))
            ->orderByDesc('current_stock')
            ->first();

        // Append to an open pending request for this location, or start one.
        $req = StockRequest::where('warehouse_id', $user->warehouse_id)
            ->where('status', 'pending')->latest()->first()
            ?? StockRequest::create([
                'warehouse_id' => $user->warehouse_id,
                'requested_by_id' => $user->id,
                'status' => 'pending',
                'note' => 'Low-stock restock',
            ]);

        $need = (float) $data['quantity'];

        $req->items()->create([
            'inventory_item_id' => $source?->id ?? $item->id,
            'item_label' => trim($item->name . ($item->size ? " ({$item->size})" : '')),
            'quantity' => $need,
        ]);

        return redirect()->route('requests.show', $req)
            ->with('success', $item->displayName() . ' (' . (int) $need . ') added to your stock request.');
    }

    public function show(StockRequest $stockRequest)
    {
        $this->guard($stockRequest);
        $stockRequest->load('items.inventoryItem.warehouse', 'warehouse', 'requestedBy', 'handledBy');

        // Items available to request: stock held in a stockroom.
        $available = InventoryItem::whereHas('warehouse', fn ($q) => $q->where('can_create_items', true))
            ->where('current_stock', '>', 0)
            ->orderBy('name')->get();

        return role_view('warehouse.requests.show', [
            'request' => $stockRequest,
            'available' => $available,
            'canFulfill' => $this->canFulfill(),
        ]);
    }

    public function addItem(Request $request, StockRequest $stockRequest)
    {
        $this->guard($stockRequest);
        abort_unless($stockRequest->isPending(), 403);

        $data = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $source = InventoryItem::with('warehouse')->findOrFail($data['inventory_item_id']);
        $label = trim($source->name . ($source->size ? " ({$source->size})" : ''));

        $stockRequest->items()->create([
            'inventory_item_id' => $source->id,
            'item_label' => $label,
            'quantity' => $data['quantity'],
        ]);

        return back()->with('success', 'Item added to request.');
    }

    public function removeItem(StockRequest $stockRequest, StockRequestItem $item)
    {
        $this->guard($stockRequest);
        abort_unless($stockRequest->isPending(), 403);
        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    public function fulfill(StockRequest $stockRequest)
    {
        abort_unless($this->canFulfill(), 403);
        abort_unless($stockRequest->isPending(), 403);

        if ($stockRequest->items()->count() === 0) {
            return back()->with('error', 'This request has no items.');
        }

        // A request may only be fulfilled when every line is fully in stock.
        $short = $this->requests->shortages($stockRequest);
        if (!empty($short)) {
            return back()->with('error',
                'Not enough stock to fulfill this request — ' . implode('; ', $short)
                . '. Restock the stockroom or adjust the requested quantities first.');
        }

        try {
            $result = $this->requests->fulfill($stockRequest);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!empty($result['short'])) {
            return back()->with('error',
                'Not enough stock to fulfill this request — ' . implode('; ', $result['short']) . '.');
        }

        return redirect()->route('requests.index')
            ->with('success', "Request fulfilled — {$result['moved']} item(s) transferred.");
    }

    public function reject(StockRequest $stockRequest)
    {
        abort_unless($this->canFulfill(), 403);
        $stockRequest->update(['status' => 'rejected', 'handled_by_id' => auth()->id(), 'handled_at' => now()]);

        return redirect()->route('requests.index')->with('success', 'Request rejected.');
    }

    public function cancel(StockRequest $stockRequest)
    {
        $this->guard($stockRequest);
        abort_unless($stockRequest->isPending(), 403);
        $stockRequest->update(['status' => 'cancelled']);

        return redirect()->route('requests.index')->with('success', 'Request cancelled.');
    }
}
