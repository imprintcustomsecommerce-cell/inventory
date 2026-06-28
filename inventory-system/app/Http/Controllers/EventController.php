<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Warehouse::events()->orderByDesc('event_date')->orderBy('name')->get()
            ->map(function ($e) {
                $e->stock_total = InventoryItem::where('warehouse_id', $e->id)->sum('current_stock');
                $e->revenue = Sale::where('warehouse_id', $e->id)->sum('total');

                return $e;
            });

        return view('events.index', compact('events'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('events.create');
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
        ]);

        Warehouse::create([
            'name' => $data['name'],
            'type' => 'event',
            'location' => $data['location'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'can_create_items' => false,
        ]);

        return redirect()->route('events.index')->with('success', 'Event created. Pull stock to it with a transfer from Inventory.');
    }

    public function show(Warehouse $event)
    {
        abort_unless($event->isEvent(), 404);

        $items = InventoryItem::where('warehouse_id', $event->id)
            ->where('current_stock', '>', 0)
            ->with('product')
            ->orderBy('name')->get();

        $revenue = Sale::where('warehouse_id', $event->id)->sum('total');

        return view('events.show', compact('event', 'items', 'revenue'));
    }
}
