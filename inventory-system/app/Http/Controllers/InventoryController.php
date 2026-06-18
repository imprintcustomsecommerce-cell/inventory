<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
{
    $query = DB::table('inventory_items');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('category', 'like', '%' . $request->search . '%')
              ->orWhere('remarks', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    $items = $query->orderBy('name')->get();

    $totalItems = DB::table('inventory_items')->count();

    $lowStockItems = DB::table('inventory_items')
        ->whereColumn('current_stock', '<=', 'minimum_stock')
        ->where('current_stock', '>', 0)
        ->count();

    $outOfStockItems = DB::table('inventory_items')
        ->where('current_stock', '<=', 0)
        ->count();

    $totalMovements = DB::table('inventory_movements')->count();

    $categories = DB::table('inventory_items')
        ->whereNotNull('category')
        ->select('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    return view('inventory.index', compact(
        'items',
        'totalItems',
        'lowStockItems',
        'outOfStockItems',
        'totalMovements',
        'categories'
    ));
}

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        DB::table('inventory_items')->insert([
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit,
            'current_stock' => $request->current_stock,
            'minimum_stock' => $request->minimum_stock,
            'status' => 'active',
            'remarks' => $request->remarks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/inventory')->with('success', 'Inventory item added successfully.');
    }

    public function edit($id)
    {
        $item = DB::table('inventory_items')->where('id', $id)->first();

        if (!$item) {
            abort(404);
        }

        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        DB::table('inventory_items')->where('id', $id)->update([
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit,
            'minimum_stock' => $request->minimum_stock,
            'remarks' => $request->remarks,
            'updated_at' => now(),
        ]);

        return redirect('/inventory')->with('success', 'Inventory item updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('inventory_items')->where('id', $id)->delete();

        return redirect('/inventory')->with('success', 'Inventory item deleted successfully.');
    }

    public function stockIn(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $item = DB::table('inventory_items')->where('id', $id)->first();

        if (!$item) {
            abort(404);
        }

        DB::table('inventory_movements')->insert([
            'inventory_item_id' => $id,
            'type' => 'stock_in',
            'quantity' => $request->quantity,
            'reference' => $request->reference,
            'remarks' => $request->remarks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_items')->where('id', $id)->update([
            'current_stock' => $item->current_stock + $request->quantity,
            'updated_at' => now(),
        ]);

        return redirect('/inventory')->with('success', 'Stock added successfully.');
    }

    public function stockOut(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $item = DB::table('inventory_items')->where('id', $id)->first();

        if (!$item) {
            abort(404);
        }

        if ($request->quantity > $item->current_stock) {
            return redirect('/inventory')->with('error', 'Not enough stock available.');
        }

        DB::table('inventory_movements')->insert([
            'inventory_item_id' => $id,
            'type' => 'stock_out',
            'quantity' => $request->quantity,
            'reference' => $request->reference,
            'remarks' => $request->remarks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_items')->where('id', $id)->update([
            'current_stock' => $item->current_stock - $request->quantity,
            'updated_at' => now(),
        ]);

        return redirect('/inventory')->with('success', 'Stock deducted successfully.');
    }

    public function movements($id)
    {
        $item = DB::table('inventory_items')->where('id', $id)->first();

        if (!$item) {
            abort(404);
        }

        $movements = DB::table('inventory_movements')
            ->where('inventory_item_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return view('inventory.movements', compact('item', 'movements'));
    }

    public function stockInForm($id)
{
    $item = DB::table('inventory_items')->where('id', $id)->first();

    if (!$item) {
        abort(404);
    }

    return view('inventory.stock-in', compact('item'));
}

public function stockOutForm($id)
{
    $item = DB::table('inventory_items')->where('id', $id)->first();

    if (!$item) {
        abort(404);
    }

    return view('inventory.stock-out', compact('item'));
}

public function allMovements(Request $request)
{
    $query = DB::table('inventory_movements')
        ->join('inventory_items', 'inventory_movements.inventory_item_id', '=', 'inventory_items.id')
        ->select(
            'inventory_movements.*',
            'inventory_items.name as item_name',
            'inventory_items.unit as item_unit',
            'inventory_items.category as item_category'
        );

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('inventory_items.name', 'like', '%' . $request->search . '%')
              ->orWhere('inventory_items.category', 'like', '%' . $request->search . '%')
              ->orWhere('inventory_movements.reference', 'like', '%' . $request->search . '%')
              ->orWhere('inventory_movements.remarks', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('type')) {
        $query->where('inventory_movements.type', $request->type);
    }

    if ($request->filled('date_from')) {
        $query->whereDate('inventory_movements.created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('inventory_movements.created_at', '<=', $request->date_to);
    }

    $movements = $query
        ->orderByDesc('inventory_movements.created_at')
        ->get();

    return view('inventory.all-movements', compact('movements'));
}

public function lowStock(Request $request)
{
    $query = DB::table('inventory_items')
        ->whereColumn('current_stock', '<=', 'minimum_stock');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('category', 'like', '%' . $request->search . '%')
              ->orWhere('remarks', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('status')) {
        if ($request->status === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        }

        if ($request->status === 'low_stock') {
            $query->where('current_stock', '>', 0)
                  ->whereColumn('current_stock', '<=', 'minimum_stock');
        }
    }

    $items = $query->orderBy('current_stock')->get();

    return view('inventory.low-stock', compact('items'));
}

public function adjustForm($id)
{
    $item = DB::table('inventory_items')->where('id', $id)->first();

    if (!$item) {
        abort(404);
    }

    return view('inventory.adjust', compact('item'));
}

public function adjustStock(Request $request, $id)
{
    $request->validate([
        'actual_stock' => 'required|integer|min:0',
        'reference' => 'nullable|string|max:255',
        'remarks' => 'nullable|string',
    ]);

    $item = DB::table('inventory_items')->where('id', $id)->first();

    if (!$item) {
        abort(404);
    }

    DB::table('inventory_movements')->insert([
        'inventory_item_id' => $id,
        'type' => 'adjustment',
        'quantity' => $request->actual_stock,
        'reference' => $request->reference,
        'remarks' => 'Adjusted from ' . $item->current_stock . ' to ' . $request->actual_stock . '. ' . $request->remarks,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('inventory_items')->where('id', $id)->update([
        'current_stock' => $request->actual_stock,
        'updated_at' => now(),
    ]);

    return redirect('/inventory')->with('success', 'Stock adjusted successfully.');
}


}