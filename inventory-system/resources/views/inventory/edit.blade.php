@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Edit item</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $item->name }}</p>
    </div>

    <form action="{{ route('inventory.update', $item->id) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Item name</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Category</label>
                    <select name="category" class="select">
                        <option value="">Select category</option>
                        @foreach(['Fabric','Zipper','Thread','Collar','Cuffs','Label','Packaging','Other'] as $opt)
                            <option value="{{ $opt }}" {{ old('category', $item->category) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Unit</label>
                    <select name="unit" required class="select @error('unit') input-error @enderror">
                        @foreach(['pcs','yards','meters','rolls','packs','boxes'] as $opt)
                            <option value="{{ $opt }}" {{ old('unit', $item->unit) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Current stock</label>
                    <input type="number" value="{{ $item->current_stock }}" disabled class="input cursor-not-allowed bg-zinc-100 text-zinc-500">
                    <p class="mt-1.5 text-xs text-zinc-400">Change via Stock In / Stock Out.</p>
                </div>
                <div>
                    <label class="label">Minimum stock alert</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0" required class="input @error('minimum_stock') input-error @enderror">
                    @error('minimum_stock') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label">Unit cost <span class="font-normal text-zinc-400">(₱ per {{ $item->unit }}, optional)</span></label>
                <input type="number" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost) }}" min="0" step="0.01" class="input @error('unit_cost') input-error @enderror">
                <p class="mt-1.5 text-xs text-zinc-400">Used to compute material cost on projects.</p>
                @error('unit_cost') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" class="textarea">{{ old('remarks', $item->remarks) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>

    <!-- Danger zone -->
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-6">
        <h3 class="text-sm font-semibold text-red-900">Delete this item</h3>
        <p class="mt-1 text-sm text-red-700">Permanently removes the item and its entire movement history. This cannot be undone.</p>
        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete “{{ $item->name }}” and all its history? This cannot be undone.');" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete item</button>
        </form>
    </div>
</div>

@endsection
