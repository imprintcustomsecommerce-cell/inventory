@extends('layouts.app')

@section('title', 'Stock Report')

@section('content')

<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">📊 Stock Movement Report</h2>
            <p class="text-gray-600 mt-2">{{ $movements->total() }} movement/s found</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">← Back</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('inventory.allMovements') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                   class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">

            <select name="type" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400">
                <option value="">All Types</option>
                <option value="stock_in" {{ request('type') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                <option value="stock_out" {{ request('type') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-3 border border-gray-300 rounded-lg">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-3 border border-gray-300 rounded-lg">

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold rounded-lg">Filter</button>
                <a href="{{ route('inventory.allMovements') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold rounded-lg text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($movements->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold">Date</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Item</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Quantity</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $movement->item->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $movement->getTypeBadgeClass() }}">
                                        {{ $movement->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $movement->quantity }} {{ $movement->item->unit }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $movement->reference ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $movements->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-600">
                No stock movements found.
            </div>
        @endif
    </div>
</div>

@endsection
