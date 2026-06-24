@extends('layouts.app')

@section('title', 'Import Inventory')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Import / restore inventory</h1>
        <p class="mt-1 text-sm text-zinc-500">Upload an Excel (.xlsx) or CSV file to add or update items in bulk. Works with files produced by Export Excel — a quick backup &amp; restore.</p>
    </div>

    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-900">
        <p class="font-semibold">Expected columns</p>
        <p class="mt-1">Warehouse, Item, Category, Size, Unit, Current Stock, Minimum Stock, Unit Cost</p>
        <p class="mt-2 text-blue-800">Rows are matched by <strong>Warehouse + Item + Size</strong>: existing items are updated, new ones are created (and missing warehouses are added). Stock values are set exactly as in the file, so this doubles as a restore.</p>
        <a href="{{ route('inventory.export') }}" class="mt-3 inline-flex items-center gap-1.5 font-semibold text-blue-700 hover:text-blue-900">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Download a sample (current export)
        </a>
    </div>

    <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Excel or CSV file</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv,text/csv" required
                       class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800 @error('file') text-red-600 @enderror">
                @error('file') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Import file</button>
        </div>
    </form>
</div>

@endsection
