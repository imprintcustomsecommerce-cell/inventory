@extends('layouts.app')

@section('title', 'Stock In')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Stock in</h1>
            <p class="text-sm text-zinc-500">Add incoming stock for {{ $item->displayName() }}.</p>
        </div>
    </div>

    <div class="mb-5 flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4">
        <span class="text-sm font-medium text-zinc-500">Current stock</span>
        <span class="text-lg font-semibold text-zinc-900">{{ $item->current_stock }} <span class="text-sm font-normal text-zinc-400">{{ $item->unit }}</span></span>
    </div>

    <form action="{{ route('inventory.stockIn', $item->id) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Quantity to add</label>
                <div class="relative">
                    <input type="number" name="quantity" min="1" step="1" required value="{{ old('quantity') }}" class="input pr-16 @error('quantity') input-error @enderror">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $item->unit }}</span>
                </div>
                @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Reference <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Invoice #1001, supplier delivery" class="input">
            </div>
            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" class="textarea">{{ old('remarks') }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Add stock</button>
        </div>
    </form>
</div>

@endsection
