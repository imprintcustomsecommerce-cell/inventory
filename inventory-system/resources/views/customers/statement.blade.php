@extends('layouts.app')

@section('title', 'Statement · ' . $customer->name)

@section('content')

<a href="{{ route('customers.show', $customer) }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to customer
</a>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Statement of account</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $customer->displayName() }} · as of {{ now()->format('M d, Y') }}</p>
    </div>
    <a href="{{ route('customers.statement.pdf', $customer) }}" target="_blank" class="btn btn-ghost">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        PDF
    </a>
</div>

<!-- Summary -->
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $cards = [
            ['label' => 'Total billed', 'value' => '₱' . number_format($summary['billed'], 2), 'accent' => 'text-zinc-900'],
            ['label' => 'Total paid', 'value' => '₱' . number_format($summary['paid'], 2), 'accent' => 'text-emerald-600'],
            ['label' => 'Outstanding', 'value' => '₱' . number_format($summary['outstanding'], 2), 'accent' => $summary['outstanding'] > 0 ? 'text-red-600' : 'text-zinc-900'],
            ['label' => 'Overdue', 'value' => '₱' . number_format($summary['overdue'], 2), 'accent' => $summary['overdue'] > 0 ? 'text-red-600' : 'text-zinc-900'],
        ];
    @endphp
    @foreach($cards as $c)
        <div class="card p-5">
            <p class="text-sm font-medium text-zinc-500">{{ $c['label'] }}</p>
            <p class="mt-2 text-2xl font-bold {{ $c['accent'] }}">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="card overflow-hidden">
    <div class="border-b border-zinc-200 px-6 py-4">
        <h2 class="text-sm font-semibold text-zinc-900">Account activity</h2>
        <p class="text-xs text-zinc-500">{{ $summary['invoice_count'] }} invoice{{ $summary['invoice_count'] === 1 ? '' : 's' }} · charges and payments over time</p>
    </div>
    @if($ledger->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Detail</th>
                        <th class="text-right">Charge</th>
                        <th class="text-right">Payment</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledger as $entry)
                        <tr>
                            <td class="whitespace-nowrap text-zinc-500">{{ $entry['date']?->format('M d, Y') }}</td>
                            <td><span class="badge {{ $entry['type'] === 'Payment' ? 'badge-green' : 'badge-zinc' }}">{{ $entry['type'] }}</span></td>
                            <td class="font-medium text-zinc-900">{{ $entry['reference'] }}</td>
                            <td class="text-zinc-500">{{ $entry['detail'] }}</td>
                            <td class="text-right text-zinc-700">{{ $entry['charge'] > 0 ? '₱' . number_format($entry['charge'], 2) : '—' }}</td>
                            <td class="text-right text-emerald-600">{{ $entry['payment'] > 0 ? '₱' . number_format($entry['payment'], 2) : '—' }}</td>
                            <td class="text-right font-medium text-zinc-900">₱{{ number_format($entry['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-zinc-200 bg-zinc-50">
                        <td colspan="6" class="font-semibold text-zinc-900">Balance due</td>
                        <td class="text-right text-lg font-bold {{ $summary['outstanding'] > 0 ? 'text-red-600' : 'text-emerald-600' }}">₱{{ number_format($summary['outstanding'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <p class="px-6 py-12 text-center text-sm text-zinc-500">No invoices for this customer yet.</p>
    @endif
</div>

@endsection
