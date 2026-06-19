@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')

<div class="max-w-2xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Edit Item</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
        <form action="{{ route('inventory.update', $item->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Item Name *</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">
                        <option value="">Select Category</option>
                        <option value="Fabric" {{ old('category', $item->category) == 'Fabric' ? 'selected' : '' }}>Fabric</option>
                        <option value="Zipper" {{ old('category', $item->category) == 'Zipper' ? 'selected' : '' }}>Zipper</option>
                        <option value="Thread" {{ old('category', $item->category) == 'Thread' ? 'selected' : '' }}>Thread</option>
                        <option value="Collar" {{ old('category', $item->category) == 'Collar' ? 'selected' : '' }}>Collar</option>
                        <option value="Cuffs" {{ old('category', $item->category) == 'Cuffs' ? 'selected' : '' }}>Cuffs</option>
                        <option value="Label" {{ old('category', $item->category) == 'Label' ? 'selected' : '' }}>Label</option>
                        <option value="Packaging" {{ old('category', $item->category) == 'Packaging' ? 'selected' : '' }}>Packaging</option>
                        <option value="Other" {{ old('category', $item->category) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Unit *</label>
                    <select name="unit" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">
                        <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                        <option value="yards" {{ old('unit', $item->unit) == 'yards' ? 'selected' : '' }}>yards</option>
                        <option value="meters" {{ old('unit', $item->unit) == 'meters' ? 'selected' : '' }}>meters</option>
                        <option value="rolls" {{ old('unit', $item->unit) == 'rolls' ? 'selected' : '' }}>rolls</option>
                        <option value="packs" {{ old('unit', $item->unit) == 'packs' ? 'selected' : '' }}>packs</option>
                        <option value="boxes" {{ old('unit', $item->unit) == 'boxes' ? 'selected' : '' }}>boxes</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Current Stock</label>
                    <input type="number" value="{{ $item->current_stock }}" disabled
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                    <p class="text-gray-600 text-xs mt-1">Use Stock In/Out to change</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Minimum Stock Alert *</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">{{ old('remarks', $item->remarks) }}</textarea>
            </div>

            <div class="flex gap-4 pt-4 border-t">
                <button type="submit" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-3 rounded-lg transition transform hover:scale-105">
                    Update Item
                </button>
                <a href="{{ route('inventory.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-3 rounded-lg text-center transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Delete Section -->
    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-8">
        <h3 class="text-lg font-bold text-red-900 mb-3">⚠️ Danger Zone</h3>
        <p class="text-red-700 mb-4">This action cannot be undone. All inventory history for this item will be lost.</p>
        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you absolutely sure?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition">
                🗑️ Delete Item Permanently
            </button>
        </form>
    </div>
</div>

@endsection
