@extends('layouts.app')

@section('title', 'Stock Movement Report')

@section('content')

<div>
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Stock Movement Report</h2>
            <p class="text-gray-600 mt-2">{{ $movements->total() }} movement/s found</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('inventory.allMovements') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item, reference..."
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="stock_in" {{ request('type') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                    <option value="stock_out" {{ request('type') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        Filter
                    </button>
                    <a href="{{ route('inventory.allMovements') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg text-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($movements->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Item</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Quantity</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                {{ $movement->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $movement->item->name }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $movement->item->category ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $movement->getTypeBadgeClass() }}">
                                    {{ $movement->getTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $movement->quantity }} {{ $movement->item->unit }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $movement->reference ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $movements->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <p class="text-gray-600 text-lg">No stock movements found.</p>
            </div>
        @endif
    </div>
</div>

@endsection
