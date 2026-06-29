<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OnlineOrder;
use App\Models\Project;
use App\Models\Sale;
use App\Models\SalesChannel;
use App\Support\MockOrderFactory;
use Illuminate\Http\Request;

class OnlineOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = OnlineOrder::with('channel');

        $status = $request->input('status', 'New');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('channel')) {
            $query->where('sales_channel_id', $request->input('channel'));
        }

        $orders = $query->latest('ordered_at')->paginate(50)->withQueryString();

        $stats = [
            'new' => OnlineOrder::where('status', 'New')->count(),
            'routed' => OnlineOrder::where('status', 'Routed')->count(),
            'revenue' => (float) OnlineOrder::where('status', 'Routed')->sum('amount'),
        ];

        $channels = SalesChannel::orderBy('name')->get();

        return view('online-orders.index', compact('orders', 'stats', 'status', 'channels'));
    }

    /** Pull a sample order from any connected channel (mock). */
    public function simulate()
    {
        $channel = SalesChannel::where('status', 'connected')->inRandomOrder()->first();

        if (!$channel) {
            return back()->with('error', 'Connect a channel first to simulate an order.');
        }

        MockOrderFactory::generateForChannel($channel, 1);

        return back()->with('success', "New sample order received from {$channel->name}.");
    }

    /** Turn an online order into a Sale (stock) or Project (custom). */
    public function route(OnlineOrder $onlineOrder)
    {
        if (!$onlineOrder->isNew()) {
            return back()->with('error', 'This order has already been handled.');
        }

        // Match an existing customer by name, or create one from the buyer.
        $customer = Customer::firstOrCreate(
            ['name' => $onlineOrder->buyer_name],
            ['phone' => $onlineOrder->buyer_contact, 'notes' => "Created from {$onlineOrder->channel->name} online order."]
        );

        $tag = "{$onlineOrder->channel->name} #{$onlineOrder->external_ref}";

        if ($onlineOrder->order_type === 'custom') {
            $project = Project::create([
                'project_name' => $onlineOrder->item_label,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'product_type' => $onlineOrder->item_label,
                'quantity' => $onlineOrder->quantity,
                'quoted_price' => $onlineOrder->amount,
                'status' => 'Pending',
                'remarks' => "Online order from {$tag}.",
            ]);

            $onlineOrder->update([
                'status' => 'Routed',
                'routed_type' => 'project',
                'routed_id' => $project->id,
            ]);

            return redirect()->route('projects.show', $project)
                ->with('success', "Created project from {$tag}.");
        }

        // Stock order → record a sale line (no inventory deduction in the mock).
        $sale = Sale::create([
            'item_label' => $onlineOrder->item_label,
            'quantity' => $onlineOrder->quantity,
            'unit_price' => $onlineOrder->quantity > 0 ? $onlineOrder->amount / $onlineOrder->quantity : $onlineOrder->amount,
            'total' => $onlineOrder->amount,
            'user_id' => auth()->id(),
            'remarks' => "Online order from {$tag}.",
        ]);

        $onlineOrder->update([
            'status' => 'Routed',
            'routed_type' => 'sale',
            'routed_id' => $sale->id,
        ]);

        return back()->with('success', "Recorded sale from {$tag}.");
    }

    public function ignore(OnlineOrder $onlineOrder)
    {
        $onlineOrder->update(['status' => 'Ignored']);

        return back()->with('success', 'Order ignored.');
    }

    public function destroy(OnlineOrder $onlineOrder)
    {
        $onlineOrder->delete();

        return back()->with('success', 'Order removed.');
    }
}
