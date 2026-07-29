@extends('shared.layouts.app')

@section('title', 'Import Products')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('products.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to products
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Import products</h1>
        <p class="mt-1 text-sm text-zinc-500">Upload your products Excel/CSV — one row per product, with sizes in the attributes column.</p>
    </div>

    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-900">
        <p class="font-semibold">Two formats are accepted</p>
        <p class="mt-2"><span class="font-medium">1. Standard:</span> Product name, Product ID, Categories, Brand name, Product attributes (e.g. <em>SIZE: S, M, L</em>), Retail price, Imported price, Material, Description. One row per product.</p>
        <p class="mt-2"><span class="font-medium">2. Imprint SUMMARY export:</span> IMAGE, CATEGORY, ITEM NAME (e.g. <em>NEKROS SHORT - XS</em>), REMAINING, SRP. One row per size; the product name and size are split at the last “ - ”, stock comes from REMAINING, price from SRP, and images are downloaded from the IMAGE link.</p>
        <p class="mt-2 text-blue-800">Downloading images from the SUMMARY export needs an internet connection during the import; once saved they work offline.</p>
    </div>

    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            @if($warehouses->count() === 1)
                {{-- Scoped staff import into their own stockroom; no need to ask. --}}
                <input type="hidden" name="warehouse_id" value="{{ $warehouses->first()->id }}">
                <div>
                    <label class="label">Stockroom</label>
                    <p class="text-sm font-medium text-zinc-700">{{ $warehouses->first()->name }}</p>
                </div>
            @else
                <div>
                    <label class="label">Warehouse</label>
                    <select name="warehouse_id" required class="select">
                        <option value="">Select warehouse</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            @endif
            <div>
                <label class="label">Excel or CSV file</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800 @error('file') text-red-600 @enderror">
                @error('file') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('products.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Import products</button>
        </div>
    </form>
</div>

@endsection
