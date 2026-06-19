@extends('layouts.app')

@section('title', 'Stock History')

@section('content')

<div>
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Stock History</h2>
            <p class="text-gray-600 mt-2">{{ $item->name }} — {{ $item->current_stock }} {{ $item->unit }} current stock</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($movements->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Date & Time</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Quantity</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Reference</th>
                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $movement->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $movement->getTypeBadgeClass() }}">
                                    {{ $movement->getTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $movement->quantity }} {{ $item->unit }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $movement->reference ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm">
                                {{ $movement->remarks ?? '-' }}
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
                <p class="text-gray-600 text-lg">No stock movements yet.</p>
            </div>
        @endif
    </div>
</div>

@endsection
