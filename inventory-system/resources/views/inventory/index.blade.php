@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-4xl font-bold text-gray-900">Inventory Management</h2>
            <p class="text-gray-600 mt-2">{{ $items->total() }} item/s in inventory</p>
        </div>
        <a href="{{ route('inventory.create') }}" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
            Add New Item
        </a>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Items</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_items'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Low Stock</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $stats['low_stock_items'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Out of Stock</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['out_of_stock_items'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 105.5 13m5.5-4.5h2.25A2.25 2.25 0 0110 5.75V3.5m0 9.75h-2.25A2.25 2.25 0 0110 14.25V17m0-9.75A2.25 2.25 0 1010 5.75v8.5z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Stock Movements</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['total_movements'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2 4a1 1 0 011-1h6a1 1 0 011 1v12a1 1 0 11-2 0V5H3a1 1 0 01-1-1zm12-2a1 1 0 01.967.25l4 5a1 1 0 11-1.934.5L17.5 5.5 14.967 8.75a1 1 0 01-1.934-.5l4-5a1 1 0 011.934 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('inventory.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item, category..."
                       class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition">

                <select name="category"
                        class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-3 rounded-lg transition">Search</button>
                    <a href="{{ route('inventory.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold py-3 rounded-lg text-center transition">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($items->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold">Item Name</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Category</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Current Stock</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Minimum Level</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                    @if($item->remarks)
                                        <p class="text-sm text-gray-600">{{ substr($item->remarks, 0, 50) }}...</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $item->category ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-lg text-gray-900">{{ $item->current_stock }}</span>
                                    <span class="text-gray-600 text-sm">{{ $item->unit }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $item->minimum_stock }} {{ $item->unit }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap {{ $item->getStatusBadgeClass() }}">
                                        {{ $item->getStatusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2 flex-wrap">
                                        <a href="{{ route('inventory.stockInForm', $item->id) }}" class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-1 px-2 rounded text-xs transition" title="Stock In">
                                            ➕
                                        </a>
                                        <a href="{{ route('inventory.stockOutForm', $item->id) }}" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-1 px-2 rounded text-xs transition" title="Stock Out">
                                            ➖
                                        </a>
                                        <a href="{{ route('inventory.adjustForm', $item->id) }}" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-bold py-1 px-2 rounded text-xs transition" title="Adjust">
                                            ⚙️
                                        </a>
                                        <a href="{{ route('inventory.movements', $item->id) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1 px-2 rounded text-xs transition" title="History">
                                            📜
                                        </a>
                                        <a href="{{ route('inventory.edit', $item->id) }}" class="bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-1 px-2 rounded text-xs transition" title="Edit">
                                            ✏️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $items->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
                <p class="text-gray-600 text-lg mb-4">No inventory items yet.</p>
                <a href="{{ route('inventory.create') }}" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-2 px-4 rounded-lg inline-block transition">
                    Create First Item
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
