<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreProjectMaterialRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\ProjectMaterial;
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
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());
        $this->projects->logCreated($project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created. Add the materials it needs below.');
    }

    public function show(Project $project)
    {
        $project->load('materials.inventoryItem', 'statusLogs.user');
        $items = InventoryItem::orderBy('name')->get();
        $hasTemplate = $project->product_type
            && \App\Models\BomTemplate::where('product_type', $project->product_type)->exists();

        return view('projects.show', compact('project', 'items', 'hasTemplate'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
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

    public function markCompleted(Project $project)
    {
        $this->projects->markCompleted($project);

        return back()->with('success', 'Project marked as completed.');
    }

    public function applyTemplate(Project $project)
    {
        if ($project->materials_deducted) {
            return back()->with('error', 'Materials are locked once production has started.');
        }

        $added = $this->projects->applyTemplate($project);

        if ($added === 0) {
            return back()->with('error', 'No new materials to add from the template.');
        }

        return back()->with('success', "Added {$added} material(s) from the {$project->product_type} template.");
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
            $cost = $p->materialsCost();
            $margin = $p->margin();

            return [
                $p->project_name,
                $p->customer_name,
                $p->product_type,
                $p->quantity,
                $p->status,
                $p->due_date?->format('Y-m-d'),
                $p->quoted_price !== null ? number_format((float) $p->quoted_price, 2, '.', '') : '',
                number_format($cost, 2, '.', ''),
                $margin !== null ? number_format($margin, 2, '.', '') : '',
                $p->materials_deducted ? 'Yes' : 'No',
            ];
        });

        return $this->streamCsv(
            'projects-' . now()->format('Y-m-d') . '.csv',
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
