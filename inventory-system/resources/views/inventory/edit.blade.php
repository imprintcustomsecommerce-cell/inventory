@extends('layouts.app')

@section('title', 'Edit Inventory Item')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Edit Item</h2>
            <p class="text-gray-600 mt-2">Update item details</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8 mb-6">
        <form action="{{ route('inventory.update', $item->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Item Name *</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                <select name="category"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                <label class="block text-sm font-semibold text-gray-700 mb-2">Unit *</label>
                <select name="unit" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('unit') border-red-500 @enderror">
                    <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>pcs (pieces)</option>
                    <option value="yards" {{ old('unit', $item->unit) == 'yards' ? 'selected' : '' }}>yards</option>
                    <option value="meters" {{ old('unit', $item->unit) == 'meters' ? 'selected' : '' }}>meters</option>
                    <option value="rolls" {{ old('unit', $item->unit) == 'rolls' ? 'selected' : '' }}>rolls</option>
                    <option value="packs" {{ old('unit', $item->unit) == 'packs' ? 'selected' : '' }}>packs</option>
                    <option value="boxes" {{ old('unit', $item->unit) == 'boxes' ? 'selected' : '' }}>boxes</option>
                </select>
                @error('unit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Current Stock</label>
                <input type="number" value="{{ $item->current_stock }}" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                <p class="text-gray-600 text-sm mt-2">Stock can only be changed using Stock In / Stock Out.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Stock Alert *</label>
                <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('minimum_stock') border-red-500 @enderror">
                @error('minimum_stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Remarks</label>
                <textarea name="remarks" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('remarks', $item->remarks) }}</textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                    Update Item
                </button>
                <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Delete Section -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-900 mb-4">Danger Zone</h3>
        <p class="text-red-700 mb-4">Delete this item and all its history. This action cannot be undone.</p>
        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg">
                Delete Item Permanently
            </button>
        </form>
    </div>
</div>

@endsection
