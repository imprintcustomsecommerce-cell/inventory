@extends('layouts.app')

@section('title', 'Stock History')

@section('content')

<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">📜 Stock History</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} — Current: {{ $item->current_stock }} {{ $item->unit }}</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">← Back</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($movements->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold">Date</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Quantity</th>
                            <th class="px-6 py-4 text-left text-sm font-bold">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $movement->getTypeBadgeClass() }}">
                                        {{ $movement->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $movement->quantity }} {{ $item->unit }}</td>
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
                No stock movements yet for this item.
            </div>
        @endif
    </div>
</div>

@endsection
