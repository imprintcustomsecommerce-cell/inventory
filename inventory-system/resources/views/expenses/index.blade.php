@extends('layouts.app')

@section('title', 'Expenses')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Expenses</h1>
        <p class="mt-1 text-sm text-zinc-500">Shop overhead and operating costs.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('expenses.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Expense
        </a>
    </div>
</div>

<!-- Summary -->
<div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="card p-6 lg:col-span-1">
        <p class="text-sm font-medium text-zinc-500">Total — {{ \Illuminate\Support\Carbon::parse($month . '-01')->format('F Y') }}</p>
        <p class="mt-2 text-3xl font-bold text-zinc-900">₱{{ number_format($monthTotal, 2) }}</p>
        <p class="mt-1 text-xs text-zinc-400">{{ $expenses->total() }} expense{{ $expenses->total() === 1 ? '' : 's' }} this period</p>
    </div>
    <div class="card p-6 lg:col-span-2">
        <p class="text-sm font-semibold text-zinc-900">By category</p>
        @if($byCategory->count() > 0)
            <div class="mt-4 space-y-3">
                @foreach($byCategory as $row)
                    @php $pct = $monthTotal > 0 ? round($row->total / $monthTotal * 100) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600">{{ $row->category }}</span>
                            <span class="font-medium text-zinc-900">₱{{ number_format($row->total, 2) }} <span class="text-zinc-400">({{ $pct }}%)</span></span>
                        </div>
                        <div class="mt-1 h-1.5 w-full rounded-full bg-zinc-100">
                            <div class="h-1.5 rounded-full bg-brand-400" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-3 text-sm text-zinc-500">No expenses in this period.</p>
        @endif
    </div>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description or reference…" class="input pl-9">
        </div>
        <input type="month" name="month" value="{{ $month }}" class="input sm:w-44">
        <select name="category" class="select sm:w-48">
            <option value="">All categories</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" {{ request('category') == $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request('search') || request('category'))
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($expenses->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Supplier</th>
                        <th>Method</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td class="whitespace-nowrap text-zinc-500">{{ $expense->expense_date?->format('M d, Y') }}</td>
                            <td><span class="badge badge-zinc">{{ $expense->category }}</span></td>
                            <td class="font-medium text-zinc-900">{{ $expense->description }}</td>
                            <td class="text-zinc-500">{{ $expense->supplier?->name ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $expense->payment_method ?? '—' }}</td>
                            <td class="text-right font-medium text-zinc-900">₱{{ number_format($expense->amount, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $expenses->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No expenses found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') || request('category') ? 'Try adjusting your filters.' : 'Record your first expense for this period.' }}</p>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary mt-4">New Expense</a>
        </div>
    @endif
</div>

@endsection
