@extends('layouts.app')

@section('title', 'Stock Out')

@section('content')

<div class="max-w-2xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">📤 Stock Out</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} (Available: {{ $item->current_stock }} {{ $item->unit }})</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">← Back</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
            <p class="text-red-800 font-semibold">{{ $item->name }}</p>
            <p class="text-red-700 text-sm">Available Stock: {{ $item->current_stock }} {{ $item->unit }}</p>
        </div>

        <form action="{{ route('inventory.stockOut', $item->id) }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Quantity to Deduct *</label>
                <input type="number" name="quantity" min="1" max="{{ $item->current_stock }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 @error('quantity') border-red-500 @enderror">
                <p class="text-gray-600 text-xs mt-1">Max: {{ $item->current_stock }} {{ $item->unit }}</p>
                @error('quantity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Reference</label>
                <input type="text" name="reference" placeholder="e.g., Order #1001"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400"></textarea>
            </div>

            <div class="flex gap-4 pt-4 border-t">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-105">
                    ✂️ Deduct Stock
                </button>
                <a href="{{ route('inventory.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-3 rounded-lg text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
