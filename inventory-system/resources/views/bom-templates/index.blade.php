@extends('layouts.app')

@section('title', 'BOM Templates')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">BOM templates</h1>
    <p class="mt-1 text-sm text-zinc-500">Default materials per product type. Applied to a project multiplies each by its quantity.</p>
</div>

<!-- Add form -->
<div class="card mb-6 p-4">
    <form action="{{ route('bomTemplates.store') }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
        @csrf
        <div>
            <label class="label">Product type</label>
            <select name="product_type" required class="select">
                @foreach($productTypes as $type)
                    <option value="{{ $type }}" {{ old('product_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Material</label>
            <select name="inventory_item_id" required class="select">
                <option value="">Select item…</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->displayName() }} ({{ $item->unit }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Qty per piece</label>
            <input type="number" name="quantity_per_unit" min="0.01" step="0.01" required class="input">
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn btn-primary w-full">Add to template</button>
        </div>
    </form>
</div>

@forelse($templates as $productType => $rows)
    <div class="card mb-4 overflow-hidden">
        <div class="border-b border-zinc-200 px-5 py-3">
            <h2 class="text-sm font-semibold text-zinc-900">{{ $productType }}</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Qty per piece</th>
                    <th class="text-right">Remove</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td class="font-medium text-zinc-900">{{ $row->inventoryItem->displayName() }}</td>
                        <td class="text-zinc-700">{{ rtrim(rtrim(number_format($row->quantity_per_unit, 2), '0'), '.') }} {{ $row->inventoryItem->unit }}</td>
                        <td class="text-right">
                            <form action="{{ route('bomTemplates.destroy', $row) }}" method="POST" onsubmit="return confirm('Remove this template material?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
        <p class="text-sm font-medium text-zinc-900">No templates yet</p>
        <p class="mt-1 text-sm text-zinc-500">Add default materials above so new projects can auto-fill their bill of materials.</p>
    </div>
@endforelse

@endsection
