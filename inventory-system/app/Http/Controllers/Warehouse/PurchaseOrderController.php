<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StorePurchaseOrderItemRequest;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use ExportsCsv;

    public function __construct(private PurchaseService $purchases)
    {
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(50);
        $stats = $this->purchases->getStatistics();

        return role_view('warehouse.purchases.index', compact('orders', 'stats'));
    }

    public function create()
    {
        return role_view('warehouse.purchases.create', [
            'nextNumber' => $this->purchases->nextNumber(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'warehouses' => Warehouse::stockrooms()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $data = $request->validated();
        $data['po_number'] = $this->purchases->nextNumber();
        $data['user_id'] = auth()->id();
        $data['status'] = 'Draft';

        $order = PurchaseOrder::create($data);

        return redirect()->route('purchases.show', $order)
            ->with('success', 'Purchase order created. Add the items to order below.');
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load('items.material', 'supplier', 'warehouse', 'user');
        $materials = Material::orderBy('name')->get();

        return role_view('warehouse.purchases.show', compact('purchase', 'materials'));
    }

    public function edit(PurchaseOrder $purchase)
    {
        return role_view('warehouse.purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::orderBy('name')->get(),
            'warehouses' => Warehouse::stockrooms()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchase)
    {
        $data = $request->validated();
        $requestedStatus = $data['status'];
        unset($data['status']);

        $purchase->update($data);

        // Allow explicit cancel / reopen; otherwise let receiving drive status.
        if ($requestedStatus === 'Cancelled') {
            $purchase->update(['status' => 'Cancelled']);
        } else {
            $this->purchases->refreshStatus($purchase->fresh('items'));
        }

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchase)
    {
        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase order deleted.');
    }

    public function addItem(StorePurchaseOrderItemRequest $request, PurchaseOrder $purchase)
    {
        if (!$purchase->isEditable()) {
            return back()->with('error', 'This purchase order can no longer be edited.');
        }

        $data = $request->validated();
        $data['total'] = (float) $data['quantity_ordered'] * (float) $data['unit_cost'];

        // Default the description from the linked material when left blank.
        if (empty($data['description']) && !empty($data['material_id'])) {
            $data['description'] = optional(Material::find($data['material_id']))->name ?? 'Item';
        }

        $purchase->items()->create($data);
        $this->purchases->recalcTotals($purchase);

        return back()->with('success', 'Item added to the order.');
    }

    public function removeItem(PurchaseOrder $purchase, PurchaseOrderItem $item)
    {
        if (!$purchase->isEditable()) {
            return back()->with('error', 'This purchase order can no longer be edited.');
        }

        $item->delete();
        $this->purchases->recalcTotals($purchase);

        return back()->with('success', 'Item removed.');
    }

    public function markOrdered(PurchaseOrder $purchase)
    {
        if ($purchase->items()->count() === 0) {
            return back()->with('error', 'Add at least one item before marking the order as placed.');
        }

        $this->purchases->changeStatus($purchase, 'Ordered');

        return back()->with('success', 'Purchase order marked as placed.');
    }

    public function cancel(PurchaseOrder $purchase)
    {
        $this->purchases->changeStatus($purchase, 'Cancelled');

        return back()->with('success', 'Purchase order cancelled.');
    }

    public function receiveItem(Request $request, PurchaseOrder $purchase, PurchaseOrderItem $item)
    {
        if (!$purchase->canReceive()) {
            return back()->with('error', 'Mark the order as placed before receiving stock.');
        }

        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $this->purchases->receiveItem($item, (float) $data['quantity']);

        return back()->with('success', 'Stock received and added to inventory.');
    }

    public function receiveAll(PurchaseOrder $purchase)
    {
        if (!$purchase->canReceive()) {
            return back()->with('error', 'Mark the order as placed before receiving stock.');
        }

        $this->purchases->receiveAll($purchase);

        return back()->with('success', 'All outstanding items received.');
    }

    public function pdf(PurchaseOrder $purchase)
    {
        $purchase->load('items.material', 'supplier', 'warehouse', 'user');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('warehouse.purchases.pdf', ['order' => $purchase])
            ->setPaper('a4');

        return $pdf->stream("purchase-order-{$purchase->po_number}.pdf");
    }

    public function export(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $rows = $query->latest()->get()->map(fn (PurchaseOrder $o) => [
            $o->po_number,
            $o->supplier?->name,
            $o->status,
            $o->order_date?->format('Y-m-d'),
            $o->expected_date?->format('Y-m-d'),
            number_format((float) $o->total, 2, '.', ''),
        ]);

        return $this->streamXlsx(
            'purchase-orders-' . now()->format('Y-m-d') . '.xlsx',
            ['PO #', 'Supplier', 'Status', 'Ordered', 'Expected', 'Total'],
            $rows
        );
    }
}
