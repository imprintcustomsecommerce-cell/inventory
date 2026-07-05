<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount(['quotes', 'projects'])->orderBy('name')->paginate(50);

        $stats = [
            'total' => Customer::count(),
            'with_quotes' => Customer::has('quotes')->count(),
            'with_projects' => Customer::has('projects')->count(),
        ];

        return role_view('store.customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return role_view('store.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer added.');
    }

    public function show(Customer $customer)
    {
        $customer->load('quotes', 'projects');

        return role_view('store.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return role_view('store.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted.');
    }

    public function statement(Customer $customer)
    {
        return role_view('store.customers.statement', $this->buildStatement($customer));
    }

    public function statementPdf(Customer $customer)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('store.customers.statement-pdf', $this->buildStatement($customer))
            ->setPaper('a4');

        return $pdf->stream("statement-{$customer->id}.pdf");
    }

    /**
     * Assemble a statement of account: every invoice (a charge) and payment
     * (a credit) on a single timeline with a running balance, plus totals.
     *
     * @return array{customer: Customer, ledger: \Illuminate\Support\Collection, summary: array}
     */
    private function buildStatement(Customer $customer): array
    {
        $invoices = $customer->invoices()
            ->where('status', '!=', 'Cancelled')
            ->with('payments')
            ->get();

        $entries = collect();

        foreach ($invoices as $invoice) {
            $entries->push([
                'date' => $invoice->issue_date,
                'type' => 'Invoice',
                'reference' => $invoice->invoice_number,
                'detail' => $invoice->title,
                'charge' => (float) $invoice->total,
                'payment' => 0.0,
            ]);

            foreach ($invoice->payments as $payment) {
                $entries->push([
                    'date' => $payment->paid_at,
                    'type' => 'Payment',
                    'reference' => $invoice->invoice_number,
                    'detail' => $payment->method . ($payment->reference ? " · {$payment->reference}" : ''),
                    'charge' => 0.0,
                    'payment' => (float) $payment->amount,
                ]);
            }
        }

        // Oldest first, with a running balance carried down the ledger.
        $balance = 0.0;
        $ledger = $entries
            ->sortBy([['date', 'asc'], ['type', 'desc']])
            ->values()
            ->map(function ($entry) use (&$balance) {
                $balance += $entry['charge'] - $entry['payment'];
                $entry['balance'] = $balance;

                return $entry;
            });

        $billed = (float) $invoices->sum('total');
        $paid = (float) $invoices->sum(fn (Invoice $i) => (float) $i->amount_paid);

        $summary = [
            'billed' => $billed,
            'paid' => $paid,
            'outstanding' => $billed - $paid,
            'overdue' => (float) $invoices->filter(fn (Invoice $i) => $i->isOverdue())
                ->sum(fn (Invoice $i) => $i->balance()),
            'invoice_count' => $invoices->count(),
        ];

        return compact('customer', 'ledger', 'summary');
    }

    public function export(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $rows = $query->withCount(['quotes', 'projects'])->orderBy('name')->get()->map(fn (Customer $c) => [
            $c->name,
            $c->company,
            $c->email,
            $c->phone,
            $c->address,
            $c->quotes_count,
            $c->projects_count,
        ]);

        return $this->streamXlsx(
            'customers-' . now()->format('Y-m-d') . '.xlsx',
            ['Name', 'Company', 'Email', 'Phone', 'Address', 'Quotes', 'Projects'],
            $rows
        );
    }
}
