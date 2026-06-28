@extends('layouts.app')

@section('title', $material->exists ? 'Edit Material' : 'Add Material')

@section('content')

@php $editing = $material->exists; @endphp

<div class="mx-auto max-w-2xl">
    <a href="{{ route('materials.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to materials
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $editing ? 'Edit material' : 'Add material' }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $editing ? $material->name : 'Track a raw material or supply.' }}</p>
    </div>

    <form action="{{ $editing ? route('materials.update', $material) : route('materials.store') }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="space-y-5 p-6">
            @if($warehouses->isNotEmpty())
                <div>
                    <label class="label">Warehouse</label>
                    <select name="warehouse_id" required class="select">
                        @unless($editing)<option value="">Select warehouse</option>@endunless
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id', $material->warehouse_id) == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif(!$editing && auth()->user()->warehouse)
                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                    <span class="text-zinc-600">Adding to</span><span class="badge badge-zinc">{{ auth()->user()->warehouse->name }}</span>
                </div>
            @endif

            <div>
                <label class="label">Material name</label>
                <input type="text" name="name" value="{{ old('name', $material->name) }}" required placeholder="e.g. Aircool Fabric (Navy)" class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Category</label>
                    <select name="category" class="select">
                        <option value="">Select category</option>
                        @foreach(\App\Models\Material::CATEGORIES as $c)
                            <option value="{{ $c }}" {{ old('category', $material->category) == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Unit</label>
                    <select name="unit" required class="select">
                        @foreach(\App\Models\Material::UNITS as $u)
                            <option value="{{ $u }}" {{ old('unit', $material->unit ?: 'pcs') == $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="label">{{ $editing ? 'Current stock' : 'Beginning stock' }}</label>
                    <input type="number" name="current_stock" value="{{ old('current_stock', $editing ? $material->current_stock : 0) }}" min="0" step="0.01" {{ $editing ? 'disabled' : 'required' }} class="input {{ $editing ? 'cursor-not-allowed bg-zinc-100 text-zinc-500' : '' }}">
                    @if($editing)<p class="mt-1.5 text-xs text-zinc-400">Change via stock movement.</p>@endif
                </div>
                <div>
                    <label class="label">Minimum stock</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $material->minimum_stock ?: 0) }}" min="0" step="0.01" required class="input">
                </div>
                <div>
                    <label class="label">Unit cost (₱)</label>
                    <input type="number" name="unit_cost" value="{{ old('unit_cost', $material->unit_cost ?: 0) }}" min="0" step="0.01" class="input">
                </div>
            </div>

            <div>
                <label class="label">Supplier <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="supplier" value="{{ old('supplier', $material->supplier) }}" class="input">
            </div>
            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" class="textarea">{{ old('remarks', $material->remarks) }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-between gap-3 bg-zinc-50 px-6 py-4">
            <div>
                @if($editing && auth()->user()->isAdmin())
                    <button form="delete-material" class="btn btn-danger btn-sm">Delete</button>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('materials.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add material' }}</button>
            </div>
        </div>
    </form>

    @if($editing && auth()->user()->isAdmin())
        <form id="delete-material" action="{{ route('materials.destroy', $material) }}" method="POST" onsubmit="return confirm('Move “{{ $material->name }}” to trash?');" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif
</div>

@endsection
