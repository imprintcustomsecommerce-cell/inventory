@extends('layouts.app')

@section('title', 'Adjust Stock')

@section('content')

<div class="page-actions">
    <div>
        <h2>Adjust Stock</h2>
        <p>{{ $item->name }} — Current stock: {{ $item->current_stock }} {{ $item->unit }}</p>
    </div>

    <a href="{{ route('inventory.index') }}" class="btn btn-primary">Back to Inventory</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please check the form:</strong>
        <ul style="margin: 8px 0 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('inventory.adjustStock', $item->id) }}" method="POST">
    @csrf

    <div class="form-group">
        <label>Actual Stock Count</label>
        <input 
            type="number" 
            name="actual_stock" 
            min="0" 
            value="{{ old('actual_stock', $item->current_stock) }}"
            required
        >
        <small>Enter the correct actual stock after manual counting.</small>
    </div>

    <div class="form-group">
        <label>Reference</label>
        <input 
            type="text" 
            name="reference" 
            placeholder="Example: Manual count, audit, correction"
            value="{{ old('reference') }}"
        >
    </div>

    <div class="form-group">
        <label>Remarks</label>
        <textarea name="remarks" placeholder="Reason for adjustment">{{ old('remarks') }}</textarea>
    </div>

    <button type="submit" class="btn btn-warning">Save Adjustment</button>
</form>

@endsection