@extends('shared.layouts.app')

@section('title', 'Edit ' . $invoice->invoice_number)

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('invoices.show', $invoice) }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to invoice
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Edit invoice</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $invoice->invoice_number }}</p>
    </div>

    <form action="{{ route('invoices.update', $invoice) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        @include('store.invoices.partials.form-fields', ['showStatus' => true])
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>

    @if(auth()->user()->isAdmin())
        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="mt-6 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-5 py-4" onsubmit="return confirm('Delete this invoice and its payments?');">
            @csrf
            @method('DELETE')
            <div>
                <p class="text-sm font-medium text-red-800">Delete invoice</p>
                <p class="text-xs text-red-600">This removes the invoice, its items, and payment records.</p>
            </div>
            <button type="submit" class="btn btn-ghost text-red-600 hover:bg-red-100">Delete</button>
        </form>
    @endif
</div>

@endsection
