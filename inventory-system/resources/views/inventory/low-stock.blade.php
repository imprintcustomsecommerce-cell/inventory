@extends('layouts.app')

@section('title', 'Low Stock Alert')

@section('content')

<div>
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Low Stock Alert</h2>
            <p class="text-gray-600 mt-2">{{ $items->total() }} item/s need attention</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('inventory.lowStock') }}" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item, category..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">

            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                <option value="">All Low Stock</option>
                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock Only</option>
                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock Only</option>
            </select>

            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
            <a href="{{ route('inventory.lowStock') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">Reset</a>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($items->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Item</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Current Stock</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Minimum Level</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Action</th>
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
                            <td class="px-6 py-4 text-gray-600">
                                {{ $item->category ?? '-' }}
                            </td>
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
                                <a href="{{ route('inventory.stockInForm', $item->id) }}" class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-1 px-3 rounded text-sm">
                                    Restock
                                </a>
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
                <p class="text-gray-600 text-lg">✓ No low stock items. Inventory looks good!</p>
            </div>
        @endif
    </div>
</div>

@endsection
