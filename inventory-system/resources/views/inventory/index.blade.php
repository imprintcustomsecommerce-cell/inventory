@extends('layouts.app')

@section('title', 'Inventory')
@section('subtitle', 'Manage materials, supplies, and stock levels')

@section('content')

<div class="page-actions">
    <div>
        <h2>Inventory</h2>
        <p>{{ $items->count() }} item/s found</p>
    </div>

    <div style="display:flex; gap:10px;">
    <a href="{{ route('inventory.lowStock') }}" class="btn btn-danger">Low Stock</a>
    <a href="{{ route('inventory.allMovements') }}" class="btn btn-warning">Stock Report</a>
    <a href="{{ route('inventory.create') }}" class="btn btn-primary">+ Add Item</a>
</div>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    <div style="background:#f9fafb; padding:18px; border-radius:12px;">
        <p style="margin:0; color:#6b7280;">Total Items</p>
        <h2 style="margin:8px 0 0;">{{ $totalItems }}</h2>
    </div>

    <div style="background:#fff7ed; padding:18px; border-radius:12px;">
        <p style="margin:0; color:#9a3412;">Low Stock</p>
        <h2 style="margin:8px 0 0;">{{ $lowStockItems }}</h2>
    </div>

    <div style="background:#fee2e2; padding:18px; border-radius:12px;">
        <p style="margin:0; color:#991b1b;">Out of Stock</p>
        <h2 style="margin:8px 0 0;">{{ $outOfStockItems }}</h2>
    </div>

    <div style="background:#ecfdf5; padding:18px; border-radius:12px;">
        <p style="margin:0; color:#166534;">Stock Movements</p>
        <h2 style="margin:8px 0 0;">{{ $totalMovements }}</h2>
    </div>
</div>

<form method="GET" action="{{ route('inventory.index') }}" style="display:flex; gap:10px; margin-bottom:20px;">
    <input 
        type="text" 
        name="search" 
        value="{{ request('search') }}" 
        placeholder="Search item, category, remarks..."
        style="flex:1;"
    >

    <select name="category" style="width:220px;">
        <option value="">All Categories</option>

        @foreach($categories as $category)
            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                {{ $category }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-primary">Search</button>

    <a href="{{ route('inventory.index') }}" class="btn btn-warning">Reset</a>
</form>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<table class="table">
    <thead>
        <tr>
            <th>Item</th>
            <th>Category</th>
            <th>Current Stock</th>
            <th>Minimum Stock</th>
            <th>Status</th>
            <th>Actions</th>
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

                <td>
                    {{ $item->minimum_stock }} {{ $item->unit }}
                </td>

                <td>
                    @if($item->current_stock <= 0)
                        <span class="badge badge-red">Out of Stock</span>
                    @elseif($item->current_stock <= $item->minimum_stock)
                        <span class="badge badge-orange">Low Stock</span>
                    @else
                        <span class="badge badge-green">In Stock</span>
                    @endif
                </td>

                <td>
                    <div class="actions">
    <a href="{{ route('inventory.stockInForm', $item->id) }}" class="btn btn-sm btn-success">
        Stock In
    </a>

    <a href="{{ route('inventory.stockOutForm', $item->id) }}" class="btn btn-sm btn-danger">
        Stock Out
    </a>

    <a href="{{ route('inventory.adjustForm', $item->id) }}" class="btn btn-sm btn-warning">
        Adjust
    </a>

    <a href="{{ route('inventory.movements', $item->id) }}" class="btn btn-sm btn-primary">
        History
    </a>

    <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-sm btn-warning">
        Edit
    </a>
</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px;">
                    No inventory items yet.
                    <br><br>
                    <a href="{{ route('inventory.create') }}" class="btn btn-primary">Add First Item</a>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection