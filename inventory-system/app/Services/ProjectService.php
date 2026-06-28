<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectStatusLog;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Move a project into production, deducting its bill of materials from
     * inventory. Guarded against double deduction via materials_deducted.
     *
     * @return array{ok: bool, shortages: array<int, array{name: string, needed: int, available: int}>}
     */
    public function startProduction(Project $project): array
    {
        if ($project->materials_deducted) {
            return ['ok' => true, 'shortages' => []];
        }

        $project->load('materials.inventoryItem');

        // Pre-flight: make sure every material has enough stock before we touch anything.
        $shortages = [];
        foreach ($project->materials as $material) {
            $needed = (float) $material->quantity_needed;
            $available = (float) $material->inventoryItem->current_stock;
            if ($needed > $available) {
                $shortages[] = [
                    'name' => $material->inventoryItem->name,
                    'needed' => $needed,
                    'available' => $available,
                ];
            }
        }

        if (!empty($shortages)) {
            return ['ok' => false, 'shortages' => $shortages];
        }

        DB::transaction(function () use ($project) {
            $from = $project->status;

            foreach ($project->materials as $material) {
                $needed = (float) $material->quantity_needed;

                if ($needed > 0) {
                    $this->inventory->stockOut(
                        $material->inventoryItem,
                        $needed,
                        "Project #{$project->id}",
                        "Materials for {$project->project_name}"
                    );
                }

                $material->update(['quantity_used' => $needed]);
            }

            $project->update([
                'status' => 'For Production',
                'materials_deducted' => true,
                'started_production_at' => now(),
            ]);

            $this->log($project, $from, 'For Production', 'Materials deducted from inventory');
        });

        return ['ok' => true, 'shortages' => []];
    }

    public function markCompleted(Project $project): void
    {
        $this->changeStatus($project, 'Completed');
    }

    /**
     * Central status transition: logs the change and applies side effects
     * (completion timestamp, returning materials when cancelled).
     */
    public function changeStatus(Project $project, string $newStatus, ?string $note = null): void
    {
        $from = $project->status;

        if ($from === $newStatus) {
            return;
        }

        DB::transaction(function () use ($project, $from, $newStatus, $note) {
            if ($newStatus === 'Cancelled' && $project->materials_deducted) {
                $this->returnMaterials($project);
                $note = $note ?? 'Materials returned to inventory';
            }

            $updates = ['status' => $newStatus];
            if ($newStatus === 'Completed') {
                $updates['completed_at'] = now();
            }
            $project->update($updates);

            $this->log($project, $from, $newStatus, $note);
        });
    }

    /**
     * Reverse a deducted bill of materials back into stock.
     */
    private function returnMaterials(Project $project): void
    {
        $project->loadMissing('materials.inventoryItem');

        foreach ($project->materials as $material) {
            $used = (float) $material->quantity_used;
            if ($used > 0) {
                $this->inventory->stockIn(
                    $material->inventoryItem,
                    $used,
                    "Project #{$project->id} (cancelled)",
                    "Returned materials from {$project->project_name}"
                );
            }
            $material->update(['quantity_used' => 0]);
        }

        $project->update(['materials_deducted' => false]);
    }

    public function logCreated(Project $project): void
    {
        $this->log($project, null, $project->status, 'Project created');
    }

    private function log(Project $project, ?string $from, string $to, ?string $note = null): void
    {
        ProjectStatusLog::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }

    public function getStatistics(): array
    {
        return [
            'total' => Project::count(),
            'pending' => Project::where('status', 'Pending')->count(),
            'production' => Project::where('status', 'For Production')->count(),
            'completed' => Project::where('status', 'Completed')->count(),
        ];
    }
}
