@extends('layouts.app')

@section('title', 'Record Sale')

@php $defaultPrice = old('unit_price', $item->product?->retail_price ?? $item->unit_cost ?? 0); @endphp

@section('content')

<div class="mx-auto max-w-2xl" x-data="{ qty: {{ old('quantity', 1) }}, price: {{ (float) $defaultPrice }} }">
    <a href="{{ url()->previous() }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back
    </a>

    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Record sale</h1>
            <p class="text-sm text-zinc-500">{{ $item->name }}{{ $item->size ? " ({$item->size})" : '' }} · {{ $item->warehouse?->name }}</p>
        </div>
    </div>

    <div class="mb-5 flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4">
        <span class="text-sm font-medium text-zinc-500">Available</span>
        <span class="text-lg font-semibold text-zinc-900">{{ $item->current_stock }} {{ $item->unit }}</span>
    </div>

    <form action="{{ route('sales.store', $item->id) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Quantity</label>
                    <input type="number" name="quantity" x-model.number="qty" min="1" step="1" max="{{ $item->current_stock }}" required class="input @error('quantity') input-error @enderror">
                    @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Unit price (₱)</label>
                    <input type="number" name="unit_price" x-model.number="price" min="0" step="0.01" required class="input @error('unit_price') input-error @enderror">
                    @error('unit_price') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3">
                <span class="text-sm font-medium text-zinc-600">Total</span>
                <span class="text-lg font-bold text-zinc-900">₱<span x-text="(qty * price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">0.00</span></span>
            </div>
            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="remarks" value="{{ old('remarks') }}" placeholder="e.g. customer name, receipt #" class="input">
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ url()->previous() }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Record sale</button>
        </div>
    </form>
</div>

@endsection
