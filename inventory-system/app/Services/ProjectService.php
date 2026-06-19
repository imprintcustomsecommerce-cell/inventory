<?php

namespace App\Services;

use App\Models\Project;
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
            $needed = (int) ceil((float) $material->quantity_needed);
            $available = (int) $material->inventoryItem->current_stock;
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
            foreach ($project->materials as $material) {
                $needed = (int) ceil((float) $material->quantity_needed);

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
        });

        return ['ok' => true, 'shortages' => []];
    }

    public function markCompleted(Project $project): void
    {
        $project->update([
            'status' => 'Completed',
            'completed_at' => now(),
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
