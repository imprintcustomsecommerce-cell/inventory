@extends('layouts.app')

@section('title', 'Stock Out')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Stock Out</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} — Current: {{ $item->current_stock }} {{ $item->unit }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <form action="{{ route('inventory.stockOut', $item->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-800"><strong>Item:</strong> {{ $item->name }}</p>
                <p class="text-red-800"><strong>Unit:</strong> {{ $item->unit }}</p>
                <p class="text-red-800"><strong>Available Stock:</strong> {{ $item->current_stock }} {{ $item->unit }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity to Deduct *</label>
                <input type="number" name="quantity" min="1" max="{{ $item->current_stock }}" placeholder="Enter quantity"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('quantity') border-red-500 @enderror">
                <p class="text-gray-600 text-sm mt-2">Max available: {{ $item->current_stock }} {{ $item->unit }}</p>
                @error('quantity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reference</label>
                <input type="text" name="reference" placeholder="e.g., Order #1001, Production use"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
                <textarea name="remarks" rows="4" placeholder="Optional notes"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg">
                    Deduct Stock
                </button>
                <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
