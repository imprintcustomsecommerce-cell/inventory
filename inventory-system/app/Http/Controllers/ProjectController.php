<?php

namespace App\Http\Controllers;

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

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created. Add the materials it needs below.');
    }

    public function show(Project $project)
    {
        $project->load('materials.inventoryItem');
        $items = InventoryItem::orderBy('name')->get();

        return view('projects.show', compact('project', 'items'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

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
}
