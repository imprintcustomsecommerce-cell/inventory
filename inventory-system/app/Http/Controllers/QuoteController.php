<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreQuoteItemRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\InvoiceService;
use App\Services\QuoteService;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    use ExportsCsv;

    public function __construct(private QuoteService $quotes)
    {
    }

    public function index(Request $request)
    {
        $query = Quote::with('customer');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $quotes = $query->latest()->paginate(50);
        $stats = $this->quotes->getStatistics();

        return view('quotes.index', compact('quotes', 'stats'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $nextNumber = $this->quotes->nextNumber();

        return view('quotes.create', compact('customers', 'nextNumber'));
    }

    public function store(StoreQuoteRequest $request)
    {
        $data = $request->validated();
        $data['quote_number'] = $this->quotes->nextNumber();
        $data['user_id'] = auth()->id();
        $data['status'] = 'Draft';
        $data['discount'] = $data['discount'] ?? 0;

        $quote = Quote::create($data);
        $this->quotes->recalcTotals($quote);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote created. Add line items below.');
    }

    public function show(Quote $quote)
    {
        $quote->load('items', 'customer', 'user', 'project');

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $customers = Customer::orderBy('name')->get();

        return view('quotes.edit', compact('quote', 'customers'));
    }

    public function update(UpdateQuoteRequest $request, Quote $quote)
    {
        $data = $request->validated();
        $data['discount'] = $data['discount'] ?? 0;

        $quote->update($data);
        $this->quotes->recalcTotals($quote);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote updated.');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Quote deleted.');
    }

    public function addItem(StoreQuoteItemRequest $request, Quote $quote)
    {
        if (!$quote->isEditable()) {
            return back()->with('error', 'A converted quote can no longer be edited.');
        }

        $data = $request->validated();
        $data['total'] = (float) $data['quantity'] * (float) $data['unit_price'];

        $quote->items()->create($data);
        $this->quotes->recalcTotals($quote);

        return back()->with('success', 'Line item added.');
    }

    public function removeItem(Quote $quote, QuoteItem $item)
    {
        if (!$quote->isEditable()) {
            return back()->with('error', 'A converted quote can no longer be edited.');
        }

        $item->delete();
        $this->quotes->recalcTotals($quote);

        return back()->with('success', 'Line item removed.');
    }

    public function changeStatus(Request $request, Quote $quote)
    {
        $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(Quote::STATUSES)],
        ]);

        $this->quotes->changeStatus($quote, $request->input('status'));

        return back()->with('success', "Quote marked as {$request->input('status')}.");
    }

    public function convert(Quote $quote)
    {
        if ($quote->status !== 'Approved') {
            return back()->with('error', 'Only an approved quote can be converted to a project.');
        }

        if ($quote->items()->count() === 0) {
            return back()->with('error', 'Add at least one line item before converting.');
        }

        $project = $this->quotes->convertToProject($quote);

        return redirect()->route('projects.show', $project)
            ->with('success', "Quote {$quote->quote_number} converted to a project.");
    }

    public function createInvoice(Quote $quote, InvoiceService $invoices)
    {
        if (!in_array($quote->status, ['Approved', 'Converted'], true)) {
            return back()->with('error', 'Only an approved quote can be invoiced.');
        }

        if ($quote->items()->count() === 0) {
            return back()->with('error', 'Add at least one line item before invoicing.');
        }

        $invoice = $invoices->createFromQuote($quote);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created from {$quote->quote_number}.");
    }

    public function pdf(Quote $quote)
    {
        $quote->load('items', 'customer', 'user');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotes.pdf', compact('quote'))
            ->setPaper('a4');

        return $pdf->stream("quote-{$quote->quote_number}.pdf");
    }

    public function export(Request $request)
    {
        $query = Quote::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $rows = $query->latest()->get()->map(fn (Quote $q) => [
            $q->quote_number,
            $q->title,
            $q->customer?->name,
            $q->status,
            $q->valid_until?->format('Y-m-d'),
            number_format((float) $q->subtotal, 2, '.', ''),
            number_format((float) $q->discount, 2, '.', ''),
            number_format((float) $q->total, 2, '.', ''),
        ]);

        return $this->streamXlsx(
            'quotes-' . now()->format('Y-m-d') . '.xlsx',
            ['Quote #', 'Title', 'Customer', 'Status', 'Valid Until', 'Subtotal', 'Discount', 'Total'],
            $rows
        );
    }
}
