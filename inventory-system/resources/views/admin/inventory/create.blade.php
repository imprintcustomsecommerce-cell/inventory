@extends('shared.layouts.app')

@section('title', 'Add Item')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Add new item</h1>
        <p class="mt-1 text-sm text-zinc-500">Create a new material or stock item.</p>
    </div>

    <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            @if($warehouses->isNotEmpty())
                <div>
                    <label class="label">Warehouse</label>
                    <select name="warehouse_id" required class="select">
                        <option value="">Select warehouse</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif(auth()->user()->warehouse)
                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                    <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    <span class="text-zinc-600">Adding to</span>
                    <span class="badge badge-zinc">{{ auth()->user()->warehouse->name }}</span>
                </div>
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    You aren't assigned to a warehouse yet. Ask an admin to assign you one before adding items.
                </div>
            @endif
            <div>
                <label class="label">Item name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Esports Jersey (Black)" class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="label">Category</label>
                    <select name="category" class="select">
                        <option value="">Select category</option>
                        @foreach(\App\Models\InventoryItem::CATEGORIES as $opt)
                            <option value="{{ $opt }}" {{ old('category') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Size</label>
                    <select name="size" class="select">
                        <option value="">—</option>
                        @foreach(\App\Models\InventoryItem::SIZES as $opt)
                            <option value="{{ $opt }}" {{ old('size') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Unit</label>
                    <select name="unit" required class="select @error('unit') input-error @enderror">
                        @foreach(['pcs','sets','packs','boxes'] as $opt)
                            <option value="{{ $opt }}" {{ old('unit') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('unit') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Beginning stock</label>
                    <input type="number" name="current_stock" value="{{ old('current_stock', 0) }}" min="0" step="1" required class="input @error('current_stock') input-error @enderror">
                    @error('current_stock') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Minimum stock alert</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" min="0" step="1" required class="input @error('minimum_stock') input-error @enderror">
                    @error('minimum_stock') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label">Unit cost <span class="font-normal text-zinc-400">(₱ per {{ old('unit', 'unit') }}, optional)</span></label>
                <input type="number" name="unit_cost" value="{{ old('unit_cost', 0) }}" min="0" step="0.01" class="input @error('unit_cost') input-error @enderror">
                <p class="mt-1.5 text-xs text-zinc-400">Used to compute material cost on projects.</p>
                @error('unit_cost') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Photo <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="file" name="image" accept="image/*"
                       class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800 @error('image') text-red-600 @enderror">
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" placeholder="Notes about this item…" class="textarea">{{ old('remarks') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create item</button>
        </div>
    </form>
</div>

@endsection
