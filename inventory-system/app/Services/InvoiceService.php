<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate the next invoice number, e.g. INV-2026-0007. Resets per year.
     */
    public function nextNumber(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        $last = Invoice::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recompute subtotal/total from line items (keeping the stored discount)
     * and refresh the paid amount + status from recorded payments.
     */
    public function recalc(Invoice $invoice): void
    {
        $subtotal = (float) $invoice->items()->sum('total');
        $discount = (float) $invoice->discount;
        $total = max(0, $subtotal - $discount);
        $paid = (float) $invoice->payments()->sum('amount');

        $invoice->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'amount_paid' => $paid,
            'status' => $this->deriveStatus($invoice->status, $total, $paid),
        ]);
    }

    /**
     * Settlement status from totals. A manually cancelled invoice stays cancelled.
     */
    private function deriveStatus(string $current, float $total, float $paid): string
    {
        if ($current === 'Cancelled') {
            return 'Cancelled';
        }

        if ($paid <= 0) {
            return 'Unpaid';
        }

        return $paid >= $total ? 'Paid' : 'Partial';
    }

    /**
     * Build an invoice from a quote, copying its line items and customer.
     * Guarded so a quote yields at most one invoice.
     */
    public function createFromQuote(Quote $quote): Invoice
    {
        $existing = Invoice::where('quote_id', $quote->id)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($quote) {
            $quote->loadMissing('items');

            $invoice = Invoice::create([
                'invoice_number' => $this->nextNumber(),
                'customer_id' => $quote->customer_id,
                'quote_id' => $quote->id,
                'project_id' => $quote->project_id,
                'user_id' => auth()->id(),
                'title' => $quote->title,
                'status' => 'Unpaid',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'discount' => $quote->discount,
                'terms' => $quote->terms,
            ]);

            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            $this->recalc($invoice);

            return $invoice;
        });
    }

    public function getStatistics(): array
    {
        $open = Invoice::whereIn('status', ['Unpaid', 'Partial']);

        return [
            'total' => Invoice::count(),
            'unpaid' => Invoice::where('status', 'Unpaid')->count(),
            'overdue' => Invoice::whereIn('status', ['Unpaid', 'Partial'])
                ->whereNotNull('due_date')->whereDate('due_date', '<', today())->count(),
            'receivables' => (float) $open->sum(DB::raw('total - amount_paid')),
        ];
    }
}
