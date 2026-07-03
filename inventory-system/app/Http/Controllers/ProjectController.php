<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreProjectMaterialRequest;
use App\Http\Requests\StoreProjectProofRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ProjectProof;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ExportsCsv;

    public function __construct(private ProjectService $projects)
    {
    }

    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('product_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query->withCount('materials')->latest()->paginate(50);
        $stats = $this->projects->getStatistics();

        return view('projects.index', compact('projects', 'stats'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('projects.create', compact('customers'));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());
        $this->projects->logCreated($project);
        \App\Models\Activity::log('project', 'New project created', $project->project_name, route('projects.show', $project));

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created. Add the materials it needs below.');
    }

    public function show(Project $project)
    {
        $project->load('materials.inventoryItem', 'proofs.uploader', 'proofs.decider', 'deliveries.creator', 'issues.reporter', 'issues.resolver', 'statusLogs.user', 'customer');
        $items = InventoryItem::query()->visibleTo(auth()->user())->orderBy('name')->get();

        return view('projects.show', compact('project', 'items'));
    }

    public function edit(Project $project)
    {
        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('projects.edit', compact('project', 'customers'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        $newStatus = $data['status'];
        unset($data['status']);

        $project->update($data);

        // Route any status change through the service so it is logged and
        // side effects (e.g. returning materials on cancel) are applied.
        if ($project->status !== $newStatus) {
            $this->projects->changeStatus($project, $newStatus);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function addMaterial(StoreProjectMaterialRequest $request, Project $project)
    {
        if ($project->materials_deducted) {
            return back()->with('error', 'Materials are locked once production has started.');
        }

        $project->materials()->create($request->validated());

        return back()->with('success', 'Material added to the project.');
    }

    public function removeMaterial(Project $project, ProjectMaterial $material)
    {
        if ($project->materials_deducted) {
            return back()->with('error', 'Materials are locked once production has started.');
        }

        $material->delete();

        return back()->with('success', 'Material removed.');
    }

    public function uploadProof(StoreProjectProofRequest $request, Project $project)
    {
        $file = $request->file('file');
        $nextVersion = (int) $project->proofs()->max('version') + 1;

        $project->proofs()->create([
            'version' => $nextVersion,
            'file_path' => $file->store('project-proofs', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'Pending',
            'uploaded_by' => auth()->id(),
            'feedback' => $request->input('feedback'),
        ]);

        // Send the job to the approval stage if it isn't already further along.
        if (!in_array($project->status, ['For Production', 'Completed', 'Cancelled'])) {
            $this->projects->changeStatus($project, 'For Approval', "Proof v{$nextVersion} sent for approval");
        }

        return back()->with('success', "Proof v{$nextVersion} uploaded and sent for approval.");
    }

    public function approveProof(Request $request, Project $project, ProjectProof $proof)
    {
        $proof->update([
            'status' => 'Approved',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
            'feedback' => $request->input('feedback') ?: $proof->feedback,
        ]);

        if (!in_array($project->status, ['For Production', 'Completed', 'Cancelled'])) {
            $this->projects->changeStatus($project, 'For Production', "Proof v{$proof->version} approved");
        }

        \App\Models\Activity::log('proof', "Proof v{$proof->version} approved", $project->project_name, route('projects.show', $project));

        return back()->with('success', "Proof v{$proof->version} approved — ready for production.");
    }

    public function rejectProof(Request $request, Project $project, ProjectProof $proof)
    {
        $request->validate(['feedback' => 'required|string|max:500'], [
            'feedback.required' => 'Add a note describing the revision needed.',
        ]);

        $proof->update([
            'status' => 'Revision Requested',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
            'feedback' => $request->input('feedback'),
        ]);

        if (!in_array($project->status, ['Completed', 'Cancelled'])) {
            $this->projects->changeStatus($project, 'For Design', "Revision requested on proof v{$proof->version}");
        }

        return back()->with('success', "Revision requested on proof v{$proof->version}.");
    }

    public function deleteProof(Project $project, ProjectProof $proof)
    {
        Storage::disk('public')->delete($proof->file_path);
        $proof->delete();

        return back()->with('success', 'Proof deleted.');
    }

    public function startProduction(Project $project)
    {
        if ($project->materials->isEmpty() && $project->materials()->count() === 0) {
            return back()->with('error', 'Add at least one material before starting production.');
        }

        $result = $this->projects->startProduction($project);

        if (!$result['ok']) {
            $lines = collect($result['shortages'])
                ->map(fn ($s) => "{$s['name']} (need {$s['needed']}, have {$s['available']})")
                ->implode('; ');

            return back()->with('error', "Not enough stock: {$lines}.");
        }

        return back()->with('success', 'Production started — materials deducted from inventory.');
    }

    public function share(Project $project)
    {
        // Regenerate if one exists, otherwise create the first token.
        $project->update(['public_token' => \Illuminate\Support\Str::random(48)]);

        return back()->with('success', 'Customer portal link ready.');
    }

    public function markCompleted(Project $project)
    {
        $this->projects->markCompleted($project);

        return back()->with('success', 'Project marked as completed.');
    }

    public function export(Request $request)
    {
        $query = Project::with('materials.inventoryItem');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('product_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $rows = $query->latest()->get()->map(function (Project $p) {
            $materialCost = $p->materialsCost();
            $margin = $p->margin();

            return [
                $p->project_name,
                $p->customer_name,
                $p->product_type,
                $p->quantity,
                $p->status,
                $p->due_date?->format('Y-m-d'),
                $p->quoted_price !== null ? number_format((float) $p->quoted_price, 2, '.', '') : '',
                number_format($materialCost, 2, '.', ''),
                $margin !== null ? number_format($margin, 2, '.', '') : '',
                $p->materials_deducted ? 'Yes' : 'No',
            ];
        });

        return $this->streamXlsx(
            'projects-' . now()->format('Y-m-d') . '.xlsx',
            ['Project', 'Customer', 'Product', 'Quantity', 'Status', 'Due Date', 'Quoted Price', 'Material Cost', 'Margin', 'Materials Deducted'],
            $rows
        );
    }

    public function pdf(Project $project)
    {
        $project->load('materials.inventoryItem');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('projects.pdf', compact('project'))
            ->setPaper('a4');

        return $pdf->stream("project-{$project->id}-job-order.pdf");
    }
}
