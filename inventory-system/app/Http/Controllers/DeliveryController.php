<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectDeliveryRequest;
use App\Models\Project;
use App\Models\ProjectDelivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectDelivery::with('project');

        $status = $request->input('status', 'open');

        if ($status === 'open') {
            $query->whereIn('status', ['Scheduled', 'Out for Delivery']);
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('project', fn ($p) => $p->where('project_name', 'like', "%{$search}%"));
            });
        }

        $deliveries = $query->latest()->paginate(50)->withQueryString();

        $stats = [
            'scheduled' => ProjectDelivery::where('status', 'Scheduled')->count(),
            'in_transit' => ProjectDelivery::where('status', 'Out for Delivery')->count(),
            'delivered' => ProjectDelivery::where('status', 'Delivered')->count(),
            'failed' => ProjectDelivery::whereIn('status', ['Failed', 'Returned'])->count(),
        ];

        return view('deliveries.index', compact('deliveries', 'stats', 'status'));
    }

    public function store(StoreProjectDeliveryRequest $request, Project $project)
    {
        $data = $request->validated();

        // Default the recipient/address from the linked customer when blank.
        if (empty($data['recipient_name'])) {
            $data['recipient_name'] = $project->customer?->name ?? $project->customer_name;
        }

        $data['created_by'] = auth()->id();
        $data['status'] = 'Scheduled';

        $project->deliveries()->create($data);

        return back()->with('success', 'Delivery scheduled.');
    }

    public function updateStatus(Request $request, Project $project, ProjectDelivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', ProjectDelivery::STATUSES),
            'received_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'Out for Delivery' && !$delivery->dispatched_at) {
            $updates['dispatched_at'] = now();
        }

        if ($validated['status'] === 'Delivered') {
            $updates['delivered_at'] = now();
            if (!empty($validated['received_by'])) {
                $updates['received_by'] = $validated['received_by'];
            }
        }

        if (!empty($validated['remarks'])) {
            $updates['remarks'] = $validated['remarks'];
        }

        $delivery->update($updates);

        if ($validated['status'] === 'Delivered') {
            \App\Models\Activity::log('delivery', 'Order delivered', $project->project_name, route('projects.show', $project));
        }

        return back()->with('success', "Delivery marked as {$validated['status']}.");
    }

    public function destroy(Project $project, ProjectDelivery $delivery)
    {
        $delivery->delete();

        return back()->with('success', 'Delivery removed.');
    }
}
