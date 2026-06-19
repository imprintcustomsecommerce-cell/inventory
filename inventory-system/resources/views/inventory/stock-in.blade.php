@extends('layouts.app')

@section('title', 'Stock In')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Stock In</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} — Current: {{ $item->current_stock }} {{ $item->unit }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <form action="{{ route('inventory.stockIn', $item->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800"><strong>Item:</strong> {{ $item->name }}</p>
                <p class="text-blue-800"><strong>Unit:</strong> {{ $item->unit }}</p>
                <p class="text-blue-800"><strong>Current Stock:</strong> {{ $item->current_stock }} {{ $item->unit }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity to Add *</label>
                <input type="number" name="quantity" min="1" placeholder="Enter quantity"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('quantity') border-red-500 @enderror">
                @error('quantity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reference</label>
                <input type="text" name="reference" placeholder="e.g., Invoice #1001, Supplier delivery"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
                <textarea name="remarks" rows="4" placeholder="Optional notes"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg">
                    Add Stock
                </button>
                <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
