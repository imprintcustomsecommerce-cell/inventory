@extends('layouts.app')

@section('title', 'Add Inventory Item')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Add New Item</h2>
            <p class="text-gray-600 mt-2">Create a new inventory item</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8">
        <form action="{{ route('inventory.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Item Name *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="e.g., Aircool Fabric, Zipper #5"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                <select name="category"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Category</option>
                    <option value="Fabric" {{ old('category') == 'Fabric' ? 'selected' : '' }}>Fabric</option>
                    <option value="Zipper" {{ old('category') == 'Zipper' ? 'selected' : '' }}>Zipper</option>
                    <option value="Thread" {{ old('category') == 'Thread' ? 'selected' : '' }}>Thread</option>
                    <option value="Collar" {{ old('category') == 'Collar' ? 'selected' : '' }}>Collar</option>
                    <option value="Cuffs" {{ old('category') == 'Cuffs' ? 'selected' : '' }}>Cuffs</option>
                    <option value="Label" {{ old('category') == 'Label' ? 'selected' : '' }}>Label</option>
                    <option value="Packaging" {{ old('category') == 'Packaging' ? 'selected' : '' }}>Packaging</option>
                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Unit *</label>
                <select name="unit" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('unit') border-red-500 @enderror">
                    <option value="">Select Unit</option>
                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs (pieces)</option>
                    <option value="yards" {{ old('unit') == 'yards' ? 'selected' : '' }}>yards</option>
                    <option value="meters" {{ old('unit') == 'meters' ? 'selected' : '' }}>meters</option>
                    <option value="rolls" {{ old('unit') == 'rolls' ? 'selected' : '' }}>rolls</option>
                    <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>packs</option>
                    <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>boxes</option>
                </select>
                @error('unit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Beginning Stock *</label>
                <input type="number" name="current_stock" value="{{ old('current_stock', 0) }}" min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('current_stock') border-red-500 @enderror">
                @error('current_stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Stock Alert *</label>
                <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('minimum_stock') border-red-500 @enderror">
                @error('minimum_stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
                <textarea name="remarks" rows="4"
                          placeholder="Optional notes about this item"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('remarks') }}</textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg">
                    Create Item
                </button>
                <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
