<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->withCount('purchaseOrders')->orderBy('name')->paginate(50);

        return role_view('warehouse.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return role_view('warehouse.suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('purchaseOrders');

        return role_view('warehouse.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return role_view('warehouse.suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted.');
    }

    public function export(Request $request)
    {
        $rows = Supplier::withCount('purchaseOrders')->orderBy('name')->get()->map(fn (Supplier $s) => [
            $s->name,
            $s->contact_person,
            $s->email,
            $s->phone,
            $s->lead_time,
            $s->purchase_orders_count,
        ]);

        return $this->streamXlsx(
            'suppliers-' . now()->format('Y-m-d') . '.xlsx',
            ['Name', 'Contact', 'Email', 'Phone', 'Lead Time', 'Purchase Orders'],
            $rows
        );
    }
}
