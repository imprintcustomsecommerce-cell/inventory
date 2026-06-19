@extends('layouts.app')

@section('title', 'Stock In')

@section('content')

<div class="max-w-2xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">📦 Stock In</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} (Current: {{ $item->current_stock }} {{ $item->unit }})</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">← Back</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
            <p class="text-green-800 font-semibold">{{ $item->name }}</p>
            <p class="text-green-700 text-sm">Current Stock: {{ $item->current_stock }} {{ $item->unit }}</p>
        </div>

        <form action="{{ route('inventory.stockIn', $item->id) }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Quantity to Add *</label>
                <input type="number" name="quantity" min="1" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400 @error('quantity') border-red-500 @enderror">
                @error('quantity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Reference</label>
                <input type="text" name="reference" placeholder="e.g., Invoice #1001"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400"></textarea>
            </div>

            <div class="flex gap-4 pt-4 border-t">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-105">
                    ✅ Add Stock
                </button>
                <a href="{{ route('inventory.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-3 rounded-lg text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
