@extends('layouts.app')

@section('title', 'Low Stock Alert')

@section('content')

<div class="page-actions">
    <div>
        <h2>Low Stock Alert</h2>
        <p>{{ $items->count() }} item/s need attention</p>
    </div>

    <a href="{{ route('inventory.index') }}" class="btn btn-primary">Back to Inventory</a>
</div>

<form method="GET" action="{{ route('inventory.lowStock') }}" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    <input 
        type="text" 
        name="search" 
        value="{{ request('search') }}" 
        placeholder="Search item, category, remarks..."
        style="flex:1; min-width:220px;"
    >

    <select name="status" style="width:200px;">
        <option value="">All Low Stock</option>
        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock Only</option>
        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock Only</option>
    </select>

    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('inventory.lowStock') }}" class="btn btn-warning">Reset</a>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Item</th>
            <th>Category</th>
            <th>Current Stock</th>
            <th>Minimum Stock</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($items as $item)
            <tr>
                <td>
                    <strong>{{ $item->name }}</strong>
                    @if(!empty($item->remarks))
                        <br>
                        <small>{{ $item->remarks }}</small>
                    @endif
                </td>

                <td>{{ $item->category ?? '-' }}</td>

                <td>
                    <strong>{{ $item->current_stock }}</strong> {{ $item->unit }}
                </td>

                <td>{{ $item->minimum_stock }} {{ $item->unit }}</td>

                <td>
                    @if($item->current_stock <= 0)
                        <span class="badge badge-red">Out of Stock</span>
                    @else
                        <span class="badge badge-orange">Low Stock</span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('inventory.stockInForm', $item->id) }}" class="btn btn-sm btn-success">
                        Restock
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:30px;">
                    No low stock items. Inventory looks good.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection