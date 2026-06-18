@extends('layouts.app')

@section('title', 'Stock Movement Report')

@section('content')

<div class="page-actions">
    <div>
        <h2>Stock Movement Report</h2>
        <p>{{ $movements->count() }} movement/s found</p>
    </div>

    <a href="{{ route('inventory.index') }}" class="btn btn-primary">Back to Inventory</a>
</div>

<form method="GET" action="{{ route('inventory.allMovements') }}" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    <input 
        type="text" 
        name="search" 
        value="{{ request('search') }}" 
        placeholder="Search item, reference, remarks..."
        style="flex:1; min-width:220px;"
    >

    <select name="type" style="width:180px;">
        <option value="">All Types</option>
        <option value="stock_in" {{ request('type') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
        <option value="stock_out" {{ request('type') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}" style="width:170px;">
    <input type="date" name="date_to" value="{{ request('date_to') }}" style="width:170px;">

    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('inventory.allMovements') }}" class="btn btn-warning">Reset</a>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Item</th>
            <th>Category</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Reference</th>
            <th>Remarks</th>
        </tr>
    </thead>

    <tbody>
        @forelse($movements as $movement)
            <tr>
                <td>{{ \Carbon\Carbon::parse($movement->created_at)->format('M d, Y h:i A') }}</td>

                <td>
                    <strong>{{ $movement->item_name }}</strong>
                </td>

                <td>{{ $movement->item_category ?? '-' }}</td>

                <td>
                    @if($movement->type == 'stock_in')
                        <span class="badge badge-green">Stock In</span>
                    @elseif($movement->type == 'stock_out')
                        <span class="badge badge-red">Stock Out</span>
                    @else
                        <span class="badge badge-orange">Adjustment</span>
                    @endif
                </td>

                <td>{{ $movement->quantity }} {{ $movement->item_unit }}</td>

                <td>{{ $movement->reference ?? '-' }}</td>

                <td>{{ $movement->remarks ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:30px;">
                    No stock movements found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection