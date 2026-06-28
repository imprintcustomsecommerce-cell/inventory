<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    /**
     * Generate the next quote number, e.g. QT-2026-0007. Numbers reset per year.
     */
    public function nextNumber(): string
    {
        $year = now()->year;
        $prefix = "QT-{$year}-";

        $last = Quote::withTrashed()
            ->where('quote_number', 'like', $prefix . '%')
            ->orderByDesc('quote_number')
            ->value('quote_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recompute subtotal and total from the quote's line items, keeping the
     * stored discount. Persists the new figures.
     */
    public function recalcTotals(Quote $quote): void
    {
        $subtotal = (float) $quote->items()->sum('total');
        $discount = (float) $quote->discount;
        $total = max(0, $subtotal - $discount);

        $quote->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    /**
     * Change the quote status. Recorded inline (quotes have no separate log).
     */
    public function changeStatus(Quote $quote, string $newStatus): void
    {
        if ($quote->status === $newStatus) {
            return;
        }

        $quote->update(['status' => $newStatus]);
    }

    /**
     * Turn an approved quote into a production project, linking the two so the
     * job order and the quote stay connected. Guarded against double conversion.
     */
    public function convertToProject(Quote $quote): Project
    {
        if ($quote->project_id) {
            return $quote->project;
        }

        return DB::transaction(function () use ($quote) {
            $quote->loadMissing('customer', 'items');

            $quantity = (int) max(1, round((float) $quote->items->sum('quantity')));

            $project = Project::create([
                'project_name' => $quote->title,
                'customer_id' => $quote->customer_id,
                'customer_name' => $quote->customer?->name,
                'quantity' => $quantity,
                'quoted_price' => $quote->total,
                'status' => 'Pending',
                'remarks' => "Created from quote {$quote->quote_number}.",
            ]);

            $quote->update([
                'status' => 'Converted',
                'project_id' => $project->id,
                'converted_at' => now(),
            ]);

            return $project;
        });
    }

    public function getStatistics(): array
    {
        return [
            'total' => Quote::count(),
            'sent' => Quote::where('status', 'Sent')->count(),
            'approved' => Quote::where('status', 'Approved')->count(),
            'value' => (float) Quote::whereIn('status', ['Sent', 'Approved'])->sum('total'),
        ];
    }
}
