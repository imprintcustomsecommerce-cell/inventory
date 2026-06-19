<?php

namespace App\Http\Controllers;

use App\Models\BomTemplate;
use App\Models\InventoryItem;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BomTemplateController extends Controller
{
    public function index()
    {
        $templates = BomTemplate::with('inventoryItem')
            ->get()
            ->groupBy('product_type');

        $items = InventoryItem::orderBy('name')->get();
        $productTypes = ['Jersey', 'Polo Shirt', 'Round Neck Shirt', 'V-Neck Shirt', 'Jacket / Hoodie'];

        return view('bom-templates.index', compact('templates', 'items', 'productTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', Rule::in(['Jersey', 'Polo Shirt', 'Round Neck Shirt', 'V-Neck Shirt', 'Jacket / Hoodie'])],
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_per_unit' => 'required|numeric|min:0.01',
        ]);

        BomTemplate::updateOrCreate(
            [
                'product_type' => $data['product_type'],
                'inventory_item_id' => $data['inventory_item_id'],
            ],
            ['quantity_per_unit' => $data['quantity_per_unit']]
        );

        return back()->with('success', 'Template material saved.');
    }

    public function destroy(BomTemplate $bomTemplate)
    {
        $bomTemplate->delete();

        return back()->with('success', 'Template material removed.');
    }
}
