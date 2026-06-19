@extends('layouts.app')

@section('title', 'Low Stock Alert')

@section('content')

<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">⚠️ Low Stock Alert</h2>
            <p class="text-gray-600 mt-2">{{ $items->total() }} item/s need attention</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">← Back</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('inventory.lowStock') }}" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400">

            <select name="status" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400">
                <option value="">All Low Stock</option>
                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock Only</option>
                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock Only</option>
            </select>

            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
            <a href="{{ route('inventory.lowStock') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-2 px-4 rounded-lg">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($items->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold">Item</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Category</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Current</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Minimum</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $item->category ?? '-' }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $item->current_stock }} {{ $item->unit }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $item->getStatusBadgeClass() }}">
                                        {{ $item->getStatusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('inventory.stockInForm', $item->id) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $items->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-600">
                ✓ No low stock items. Inventory looks great!
            </div>
        @endif
    </div>
</div>

@endsection
