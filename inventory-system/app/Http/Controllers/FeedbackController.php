<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFeedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectFeedback::with('project', 'customer');

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        $feedback = $query->latest('submitted_at')->paginate(50)->withQueryString();

        $all = ProjectFeedback::all();
        $stats = [
            'count' => $all->count(),
            'average' => round((float) $all->avg('rating'), 2),
            'recommend_pct' => $all->count() > 0 ? round($all->where('would_recommend', true)->count() / $all->count() * 100) : 0,
            'distribution' => collect(range(5, 1))->mapWithKeys(fn ($r) => [$r => $all->where('rating', $r)->count()]),
        ];

        return view('feedback.index', compact('feedback', 'stats'));
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'would_recommend' => 'nullable|boolean',
            'comment' => 'nullable|string|max:1000',
            'reviewer_name' => 'nullable|string|max:255',
            'submitted_at' => 'required|date',
        ]);

        $data['would_recommend'] = $request->boolean('would_recommend');
        $data['customer_id'] = $project->customer_id;
        $data['reviewer_name'] = $data['reviewer_name'] ?: ($project->customer->name ?? $project->customer_name);

        $project->feedback()->create($data);

        \App\Models\Activity::log('feedback', "{$data['rating']}★ feedback received", $project->project_name, route('projects.show', $project));

        return back()->with('success', 'Feedback recorded.');
    }

    public function destroy(Project $project, ProjectFeedback $feedback)
    {
        $feedback->delete();

        return back()->with('success', 'Feedback removed.');
    }
}
