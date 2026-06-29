@extends('layouts.app')

@section('title', $customer->name)

@section('content')

<a href="{{ route('customers.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to customers
</a>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $customer->name }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $customer->company ?? 'Individual customer' }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('quotes.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">New quote</a>
        <a href="{{ route('customers.statement', $customer) }}" class="btn btn-ghost">Statement</a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost">Edit</a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Contact -->
    <div class="space-y-6 lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Contact</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Email</dt>
                    <dd class="text-zinc-900">{{ $customer->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Phone</dt>
                    <dd class="text-zinc-900">{{ $customer->phone ?? '—' }}</dd>
                </div>
                <div class="border-t border-zinc-100 pt-3">
                    <dt class="text-zinc-500">Address</dt>
                    <dd class="mt-1 text-zinc-900">{{ $customer->address ?? '—' }}</dd>
                </div>
            </dl>
            @if($customer->notes)
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">Notes</p>
                    <p class="mt-1 text-sm text-zinc-700">{{ $customer->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quotes + Projects -->
    <div class="space-y-6 lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <h2 class="text-sm font-semibold text-zinc-900">Quotes</h2>
                <a href="{{ route('quotes.create', ['customer_id' => $customer->id]) }}" class="btn btn-ghost btn-sm">New quote</a>
            </div>
            @if($customer->quotes->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Quote #</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->quotes as $quote)
                            <tr class="cursor-pointer hover:bg-zinc-50" onclick="window.location='{{ route('quotes.show', $quote) }}'">
                                <td class="font-medium text-zinc-900">{{ $quote->quote_number }}</td>
                                <td class="text-zinc-500">{{ $quote->title }}</td>
                                <td><span class="badge {{ $quote->getStatusBadgeClass() }}">{{ $quote->status }}</span></td>
                                <td class="text-right text-zinc-700">₱{{ number_format($quote->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="px-6 py-10 text-center text-sm text-zinc-500">No quotes yet.</p>
            @endif
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h2 class="text-sm font-semibold text-zinc-900">Projects</h2>
            </div>
            @if($customer->projects->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->projects as $project)
                            <tr class="cursor-pointer hover:bg-zinc-50" onclick="window.location='{{ route('projects.show', $project) }}'">
                                <td class="font-medium text-zinc-900">{{ $project->project_name }}</td>
                                <td><span class="badge {{ $project->getStatusBadgeClass() }}">{{ $project->status }}</span></td>
                                <td class="text-zinc-500">{{ $project->due_date ? $project->due_date->format('M d, Y') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="px-6 py-10 text-center text-sm text-zinc-500">No projects yet.</p>
            @endif
        </div>
    </div>
</div>

@endsection
