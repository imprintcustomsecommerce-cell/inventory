@extends('layouts.app')

@section('title', 'Inventory Movements')

@section('content')

<div class="page-actions">
    <div>
        <h2>Stock History</h2>
        <p>{{ $item->name }} — {{ $item->current_stock }} {{ $item->unit }} current stock</p>
    </div>

    <a href="{{ route('inventory.index') }}" class="btn btn-primary">Back to Inventory</a>
</div>

@if($movements->isEmpty())
    <p>No stock movement yet.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Reference</th>
                <th>Remarks</th>
            </tr>
        </thead>

        <tbody>
            @foreach($movements as $movement)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement->created_at)->format('M d, Y h:i A') }}</td>
                    <td>
                        @if($movement->type == 'stock_in')
                            <span class="badge badge-green">Stock In</span>
                        @elseif($movement->type == 'stock_out')
                            <span class="badge badge-red">Stock Out</span>
                        @else
                            <span class="badge badge-orange">Adjustment</span>
                        @endif
                    </td>
                    <td>{{ $movement->quantity }} {{ $item->unit }}</td>
                    <td>{{ $movement->reference ?? '-' }}</td>
                    <td>{{ $movement->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection