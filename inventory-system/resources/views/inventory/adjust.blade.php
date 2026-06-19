@extends('layouts.app')

@section('title', 'Adjust Stock')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Adjust Stock</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} — Current: {{ $item->current_stock }} {{ $item->unit }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <form action="{{ route('inventory.adjustStock', $item->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800"><strong>Item:</strong> {{ $item->name }}</p>
                <p class="text-yellow-800"><strong>Unit:</strong> {{ $item->unit }}</p>
                <p class="text-yellow-800"><strong>System Stock:</strong> {{ $item->current_stock }} {{ $item->unit }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Actual Stock Count *</label>
                <input type="number" name="actual_stock" min="0" value="{{ old('actual_stock', $item->current_stock) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('actual_stock') border-red-500 @enderror">
                <p class="text-gray-600 text-sm mt-2">Enter the correct count after manual verification.</p>
                @error('actual_stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reference</label>
                <input type="text" name="reference" value="{{ old('reference') }}"
                       placeholder="e.g., Physical count, Audit, Correction"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Adjustment</label>
                <textarea name="remarks" rows="4" placeholder="Why is the stock being adjusted?"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('remarks') }}</textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded-lg">
                    Confirm Adjustment
                </button>
                <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
