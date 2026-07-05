@extends('shared.layouts.app')

@section('title', 'Material Stock')

@section('content')

<div class="mx-auto max-w-2xl" x-data="{ type: '{{ old('type', 'stock_in') }}' }">
    <a href="{{ route('materials.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to materials
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Stock movement</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $material->name }} — {{ $material->current_stock }} {{ $material->unit }} on hand</p>
    </div>

    <form action="{{ route('materials.recordMovement', $material) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Movement type</label>
                <select name="type" x-model="type" class="select">
                    <option value="stock_in">Stock In (add)</option>
                    <option value="stock_out">Stock Out (deduct)</option>
                    <option value="adjustment">Adjustment (set actual count)</option>
                </select>
            </div>
            <div>
                <label class="label"><span x-text="type === 'adjustment' ? 'Actual counted stock' : 'Quantity'"></span></label>
                <div class="relative">
                    <input type="number" name="quantity" min="0" step="1" required value="{{ old('quantity') }}" class="input pr-16 @error('quantity') input-error @enderror">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $material->unit }}</span>
                </div>
                @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Reference <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Supplier delivery, production use" class="input">
            </div>
            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" class="textarea">{{ old('remarks') }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('materials.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save movement</button>
        </div>
    </form>
</div>

@endsection
