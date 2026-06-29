<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\ProjectProof;
use App\Services\ProjectService;
use Illuminate\Http\Request;

/**
 * Public, token-authenticated customer portal. No login — the unguessable
 * project token in the URL is the credential.
 */
class PortalController extends Controller
{
    public function __construct(private ProjectService $projects)
    {
    }

    public function show(string $token)
    {
        $project = $this->resolve($token);
        $project->load('proofs.uploader', 'deliveries');

        return view('portal.show', compact('project'));
    }

    public function approveProof(string $token, ProjectProof $proof)
    {
        $project = $this->resolve($token);
        abort_unless($proof->project_id === $project->id, 404);

        if ($proof->isPending()) {
            $proof->update([
                'status' => 'Approved',
                'decided_at' => now(),
                'feedback' => 'Approved by customer via portal',
            ]);

            if (!in_array($project->status, ['For Production', 'Completed', 'Cancelled'])) {
                $this->projects->changeStatus($project, 'For Production', "Proof v{$proof->version} approved by customer");
            }

            Activity::log('proof', "Proof v{$proof->version} approved by customer", $project->project_name, route('projects.show', $project));
        }

        return redirect()->route('portal.show', $token)->with('success', 'Thanks! Your approval has been recorded.');
    }

    public function rejectProof(Request $request, string $token, ProjectProof $proof)
    {
        $project = $this->resolve($token);
        abort_unless($proof->project_id === $project->id, 404);

        $request->validate(['feedback' => 'required|string|max:500'], [
            'feedback.required' => 'Please tell us what to change.',
        ]);

        if ($proof->isPending()) {
            $proof->update([
                'status' => 'Revision Requested',
                'decided_at' => now(),
                'feedback' => $request->input('feedback'),
            ]);

            if (!in_array($project->status, ['Completed', 'Cancelled'])) {
                $this->projects->changeStatus($project, 'For Design', "Revision requested by customer on proof v{$proof->version}");
            }

            Activity::log('proof', "Customer requested a revision (v{$proof->version})", $project->project_name, route('projects.show', $project));
        }

        return redirect()->route('portal.show', $token)->with('success', 'Thanks! We\'ll revise and send a new proof.');
    }

    private function resolve(string $token): Project
    {
        return Project::where('public_token', $token)->firstOrFail();
    }
}
