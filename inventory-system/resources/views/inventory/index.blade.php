@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<div class="mb-8">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Inventory Management</h2>
            <p class="text-gray-600 mt-2">Manage your materials and stock levels</p>
        </div>
        <a href="{{ route('inventory.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
            + Add New Item
        </a>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-medium">Total Items</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_items'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm font-medium">Low Stock</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $stats['low_stock_items'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <p class="text-gray-600 text-sm font-medium">Out of Stock</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['out_of_stock_items'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-medium">Movements</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['total_movements'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item, category..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Search</button>
            <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">Reset</a>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($items->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Item Name</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Current Stock</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Minimum Level</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                @if($item->remarks)
                                    <p class="text-sm text-gray-600">{{ $item->remarks }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $item->category ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $item->current_stock }} {{ $item->unit }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $item->minimum_stock }} {{ $item->unit }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->getStatusBadgeClass() }}">
                                    {{ $item->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2 flex-wrap">
                                    <a href="{{ route('inventory.stockInForm', $item->id) }}" class="text-xs bg-green-100 hover:bg-green-200 text-green-800 font-bold py-1 px-2 rounded">
                                        Stock In
                                    </a>
                                    <a href="{{ route('inventory.stockOutForm', $item->id) }}" class="text-xs bg-red-100 hover:bg-red-200 text-red-800 font-bold py-1 px-2 rounded">
                                        Stock Out
                                    </a>
                                    <a href="{{ route('inventory.adjustForm', $item->id) }}" class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-bold py-1 px-2 rounded">
                                        Adjust
                                    </a>
                                    <a href="{{ route('inventory.movements', $item->id) }}" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1 px-2 rounded">
                                        History
                                    </a>
                                    <a href="{{ route('inventory.edit', $item->id) }}" class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-1 px-2 rounded">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $items->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">No inventory items yet.</p>
                <a href="{{ route('inventory.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                    Add First Item
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
