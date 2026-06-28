<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Models\Material;
use App\Models\Warehouse;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    use ExportsCsv;

    public function __construct(private MaterialService $materials)
    {
    }

    private function guard(Material $material): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->warehouse_id && $material->warehouse_id !== $user->warehouse_id) {
            abort(403, 'This material belongs to another warehouse.');
        }
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category' => ['nullable', Rule::in(Material::CATEGORIES)],
            'unit' => ['required', Rule::in(Material::UNITS)],
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Material::query()->visibleTo($user)->with('warehouse');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('category', 'like', "%{$s}%")
                ->orWhere('supplier', 'like', "%{$s}%"));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('status') && $request->input('status') === 'low') {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        }
        if ($user->isAdmin() && $request->filled('warehouse')) {
            $query->where('warehouse_id', $request->input('warehouse'));
        }

        $materials = $query->orderBy('name')->paginate(50)->withQueryString();
        $categories = Material::CATEGORIES;
        $warehouses = $user->isAdmin() ? Warehouse::orderBy('name')->get() : collect();
        $lowCount = Material::query()->visibleTo($user)->whereColumn('current_stock', '<=', 'minimum_stock')->count();

        return view('materials.index', compact('materials', 'categories', 'warehouses', 'lowCount'));
    }

    public function create()
    {
        abort_unless(auth()->user()->canCreateItems(), 403, 'This warehouse can only receive stock via transfer.');

        return view('materials.form', [
            'material' => new Material(),
            'warehouses' => auth()->user()->isAdmin() ? Warehouse::stockrooms()->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canCreateItems(), 403);
        $data = $request->validate($this->rules());

        $user = $request->user();
        $data['warehouse_id'] = $user->isAdmin() ? ($data['warehouse_id'] ?? null) : $user->warehouse_id;
        if (!$data['warehouse_id'] || !Warehouse::whereKey($data['warehouse_id'])->where('can_create_items', true)->exists()) {
            return back()->withInput()->with('error', 'Materials can only be added to a stockroom.');
        }

        Material::create($data);

        return redirect()->route('materials.index')->with('success', 'Material added.');
    }

    public function edit(Material $material)
    {
        $this->guard($material);

        return view('materials.form', [
            'material' => $material,
            'warehouses' => auth()->user()->isAdmin() ? Warehouse::orderBy('name')->get() : collect(),
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $this->guard($material);
        $data = $request->validate($this->rules());

        if (!$request->user()->isAdmin()) {
            unset($data['warehouse_id']);
        }
        // Current stock is changed through movements, not the edit form.
        unset($data['current_stock']);

        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Material updated.');
    }

    public function destroy(Material $material)
    {
        $this->guard($material);
        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Material moved to trash.');
    }

    public function movementForm(Material $material)
    {
        $this->guard($material);

        return view('materials.movement', compact('material'));
    }

    public function recordMovement(Request $request, Material $material)
    {
        $this->guard($material);
        $data = $request->validate([
            'type' => ['required', Rule::in(['stock_in', 'stock_out', 'adjustment'])],
            'quantity' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($data['type'] !== 'adjustment' && $data['quantity'] <= 0) {
            return back()->withInput()->with('error', 'Quantity must be greater than zero.');
        }

        $result = $this->materials->record($material, $data['type'], (float) $data['quantity'], $data['reference'] ?? null, $data['remarks'] ?? null);

        if ($result === false) {
            return back()->withInput()->with('error', 'Not enough stock available.');
        }

        return redirect()->route('materials.index')->with('success', 'Stock updated.');
    }

    public function movements(Material $material)
    {
        $this->guard($material);
        $movements = $material->movements()->with('user')->latest()->paginate(50);

        return view('materials.movements', compact('material', 'movements'));
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $query = Material::query()->visibleTo($user)->with('warehouse');
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($user->isAdmin() && $request->filled('warehouse')) {
            $query->where('warehouse_id', $request->input('warehouse'));
        }

        $rows = $query->orderBy('name')->get()->map(fn (Material $m) => [
            $m->warehouse?->name, $m->name, $m->category, $m->unit,
            $m->current_stock, $m->minimum_stock,
            number_format((float) $m->unit_cost, 2, '.', ''),
            number_format($m->current_stock * (float) $m->unit_cost, 2, '.', ''),
            $m->supplier, $m->getStatusLabel(),
        ]);

        return $this->streamXlsx(
            'materials-' . now()->format('Y-m-d') . '.xlsx',
            ['Warehouse', 'Material', 'Category', 'Unit', 'Stock', 'Minimum', 'Unit Cost', 'Stock Value', 'Supplier', 'Status'],
            $rows
        );
    }
}
