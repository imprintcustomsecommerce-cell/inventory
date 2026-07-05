@extends('shared.layouts.app')

@section('title', 'Stock Out')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Stock out</h1>
            <p class="text-sm text-zinc-500">Deduct stock for {{ $item->displayName() }}.</p>
        </div>
    </div>

    <div class="mb-5 flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4">
        <span class="text-sm font-medium text-zinc-500">Available stock</span>
        <span class="text-lg font-semibold text-zinc-900">{{ $item->current_stock }} <span class="text-sm font-normal text-zinc-400">{{ $item->unit }}</span></span>
    </div>

    <form action="{{ route('inventory.stockOut', $item->id) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Quantity to deduct</label>
                <div class="relative">
                    <input type="number" name="quantity" min="1" step="1" max="{{ $item->current_stock }}" required value="{{ old('quantity') }}" class="input pr-16 @error('quantity') input-error @enderror">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $item->unit }}</span>
                </div>
                <p class="mt-1.5 text-xs text-zinc-400">Maximum {{ $item->current_stock }} {{ $item->unit }} available.</p>
                @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Reference <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Order #1001, production use" class="input">
            </div>
            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" class="textarea">{{ old('remarks') }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-danger">Deduct stock</button>
        </div>
    </form>
</div>

@endsection
