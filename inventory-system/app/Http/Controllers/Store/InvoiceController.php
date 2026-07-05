<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ExportsCsv;

    public function __construct(private InvoiceService $invoices)
    {
    }

    public function index(Request $request)
    {
        $query = Invoice::with('customer');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest()->paginate(50);
        $stats = $this->invoices->getStatistics();

        return role_view('store.invoices.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $nextNumber = $this->invoices->nextNumber();

        return role_view('store.invoices.create', compact('customers', 'nextNumber'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $data['invoice_number'] = $this->invoices->nextNumber();
        $data['user_id'] = auth()->id();
        $data['status'] = 'Unpaid';
        $data['discount'] = $data['discount'] ?? 0;

        $invoice = Invoice::create($data);
        $this->invoices->recalc($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created. Add line items below.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('items', 'payments.user', 'customer', 'user', 'quote', 'project');

        return role_view('store.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $customers = Customer::orderBy('name')->get();

        return role_view('store.invoices.edit', compact('invoice', 'customers'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $data['discount'] = $data['discount'] ?? 0;

        // Let the service settle the final status from payments unless the
        // invoice is being explicitly cancelled / re-opened.
        $requestedStatus = $data['status'];
        unset($data['status']);

        $invoice->update($data);

        if ($requestedStatus === 'Cancelled') {
            $invoice->update(['status' => 'Cancelled']);
        } elseif ($invoice->status === 'Cancelled') {
            $invoice->update(['status' => 'Unpaid']);
        }

        $this->invoices->recalc($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted.');
    }

    public function addItem(StoreInvoiceItemRequest $request, Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return back()->with('error', 'A cancelled invoice can no longer be edited.');
        }

        $data = $request->validated();
        $data['total'] = (float) $data['quantity'] * (float) $data['unit_price'];

        $invoice->items()->create($data);
        $this->invoices->recalc($invoice);

        return back()->with('success', 'Line item added.');
    }

    public function removeItem(Invoice $invoice, InvoiceItem $item)
    {
        if (!$invoice->isEditable()) {
            return back()->with('error', 'A cancelled invoice can no longer be edited.');
        }

        $item->delete();
        $this->invoices->recalc($invoice);

        return back()->with('success', 'Line item removed.');
    }

    public function addPayment(StorePaymentRequest $request, Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return back()->with('error', 'Cannot record a payment on a cancelled invoice.');
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $invoice->payments()->create($data);
        $this->invoices->recalc($invoice);

        return back()->with('success', 'Payment recorded.');
    }

    public function removePayment(Invoice $invoice, Payment $payment)
    {
        $payment->delete();
        $this->invoices->recalc($invoice);

        return back()->with('success', 'Payment removed.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('items', 'payments', 'customer', 'user');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store.invoices.pdf', compact('invoice'))
            ->setPaper('a4');

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    public function export(Request $request)
    {
        $query = Invoice::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $rows = $query->latest()->get()->map(fn (Invoice $i) => [
            $i->invoice_number,
            $i->title,
            $i->customer?->name,
            $i->status,
            $i->issue_date?->format('Y-m-d'),
            $i->due_date?->format('Y-m-d'),
            number_format((float) $i->total, 2, '.', ''),
            number_format((float) $i->amount_paid, 2, '.', ''),
            number_format($i->balance(), 2, '.', ''),
        ]);

        return $this->streamXlsx(
            'invoices-' . now()->format('Y-m-d') . '.xlsx',
            ['Invoice #', 'Title', 'Customer', 'Status', 'Issued', 'Due', 'Total', 'Paid', 'Balance'],
            $rows
        );
    }
}
