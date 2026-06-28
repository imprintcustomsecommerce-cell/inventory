<?php

namespace App\Http\Controllers;

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
            ->with(['warehouse', 'requestedBy'])->withCount('items')->latest()->paginate(50);

        return view('requests.index', ['requests' => $requests, 'canFulfill' => $this->canFulfill()]);
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

    public function show(StockRequest $stockRequest)
    {
        $this->guard($stockRequest);
        $stockRequest->load('items.inventoryItem.warehouse', 'warehouse', 'requestedBy', 'handledBy');

        // Items available to request: stock held in a stockroom.
        $available = InventoryItem::whereHas('warehouse', fn ($q) => $q->where('can_create_items', true))
            ->where('current_stock', '>', 0)
            ->orderBy('name')->get();

        return view('requests.show', [
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

        $result = $this->requests->fulfill($stockRequest);

        $msg = "Request fulfilled — {$result['moved']} item(s) transferred.";
        if (!empty($result['short'])) {
            $msg .= ' Short: ' . implode('; ', $result['short']) . '.';
        }

        return redirect()->route('requests.index')->with('success', $msg);
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
