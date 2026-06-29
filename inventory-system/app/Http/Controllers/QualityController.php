<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectIssueRequest;
use App\Models\Project;
use App\Models\ProjectIssue;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectIssue::with('project', 'reporter');

        $status = $request->input('status', 'open');

        if ($status === 'open') {
            $query->whereIn('status', ['Open', 'In Progress']);
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('project', fn ($p) => $p->where('project_name', 'like', "%{$search}%"));
            });
        }

        $issues = $query->latest()->paginate(50)->withQueryString();

        $stats = [
            'open' => ProjectIssue::whereIn('status', ['Open', 'In Progress'])->count(),
            'reprints' => ProjectIssue::where('type', 'Reprint')->count(),
            'returns' => ProjectIssue::where('type', 'Return')->count(),
            'rework_cost' => (float) ProjectIssue::sum('rework_cost'),
        ];

        return view('quality.index', compact('issues', 'stats', 'status'));
    }

    public function store(StoreProjectIssueRequest $request, Project $project)
    {
        $data = $request->validated();
        $data['reported_by'] = auth()->id();
        $data['status'] = 'Open';

        $issue = $project->issues()->create($data);

        \App\Models\Activity::log('quality', "{$issue->type} logged", "{$project->project_name}" . ($issue->reason ? " · {$issue->reason}" : ''), route('projects.show', $project));

        return back()->with('success', 'Issue logged.');
    }

    public function updateStatus(Request $request, Project $project, ProjectIssue $issue)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', ProjectIssue::STATUSES),
        ]);

        $updates = ['status' => $validated['status']];

        if (in_array($validated['status'], ['Resolved', 'Rejected'])) {
            $updates['resolved_by'] = auth()->id();
            $updates['resolved_at'] = now();
        } else {
            $updates['resolved_by'] = null;
            $updates['resolved_at'] = null;
        }

        $issue->update($updates);

        return back()->with('success', "Issue marked as {$validated['status']}.");
    }

    public function destroy(Project $project, ProjectIssue $issue)
    {
        $issue->delete();

        return back()->with('success', 'Issue removed.');
    }
}
