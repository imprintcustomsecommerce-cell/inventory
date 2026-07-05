@extends('shared.layouts.app')

@section('title', 'Transfer Stock')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Transfer stock</h1>
            <p class="text-sm text-zinc-500">Move {{ $item->displayName() }} to another warehouse.</p>
        </div>
    </div>

    <div class="mb-5 flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4">
        <div>
            <span class="text-sm font-medium text-zinc-500">From</span>
            <p class="font-semibold text-zinc-900">{{ $item->warehouse?->name ?? '—' }}</p>
        </div>
        <span class="text-lg font-semibold text-zinc-900">{{ $item->current_stock }} <span class="text-sm font-normal text-zinc-400">{{ $item->unit }}</span></span>
    </div>

    @if($warehouses->isEmpty())
        <div class="card p-6 text-sm text-zinc-500">There is no other warehouse to transfer to.</div>
    @else
        <form action="{{ route('inventory.transfer', $item->id) }}" method="POST" class="card divide-y divide-zinc-200">
            @csrf
            <div class="space-y-5 p-6">
                <div>
                    <label class="label">Destination warehouse</label>
                    <select name="destination_warehouse_id" required class="select @error('destination_warehouse_id') input-error @enderror">
                        <option value="">Select warehouse</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('destination_warehouse_id', $preselect ?? '') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('destination_warehouse_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Quantity to transfer</label>
                    <div class="relative">
                        <input type="number" name="quantity" min="1" step="1" max="{{ $item->current_stock }}" required value="{{ old('quantity') }}" class="input pr-16 @error('quantity') input-error @enderror">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $item->unit }}</span>
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-400">Maximum {{ $item->current_stock }} {{ $item->unit }} available.</p>
                    @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                    <textarea name="remarks" rows="3" placeholder="Reason for transfer…" class="textarea">{{ old('remarks') }}</textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Transfer stock</button>
            </div>
        </form>
    @endif
</div>

@endsection
