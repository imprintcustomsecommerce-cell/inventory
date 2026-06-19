@extends('layouts.app')

@section('title', 'Add Inventory Item')

@section('content')

<div class="max-w-2xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Add New Item</h2>
            <p class="text-gray-600 mt-2">Create a new inventory item</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <form action="{{ route('inventory.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Item Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 transition">
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
                    <label class="block text-sm font-bold text-gray-900 mb-2">Unit *</label>
                    <select name="unit" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 @error('unit') border-red-500 @enderror">
                        <option value="pcs">pcs (pieces)</option>
                        <option value="yards" {{ old('unit') == 'yards' ? 'selected' : '' }}>yards</option>
                        <option value="meters" {{ old('unit') == 'meters' ? 'selected' : '' }}>meters</option>
                        <option value="rolls" {{ old('unit') == 'rolls' ? 'selected' : '' }}>rolls</option>
                        <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>packs</option>
                        <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>boxes</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Beginning Stock *</label>
                    <input type="number" name="current_stock" value="{{ old('current_stock', 0) }}" min="0" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 @error('current_stock') border-red-500 @enderror">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Minimum Stock Alert *</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" min="0" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 @error('minimum_stock') border-red-500 @enderror">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 transition">{{ old('remarks') }}</textarea>
            </div>

            <div class="flex gap-4 pt-4 border-t">
                <button type="submit" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-3 rounded-lg transition transform hover:scale-105">
                    Create Item
                </button>
                <a href="{{ route('inventory.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-3 rounded-lg text-center transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
