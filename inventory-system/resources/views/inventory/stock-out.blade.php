@extends('layouts.app')

@section('title', 'Stock Out')

@section('content')

<div class="page-actions">
    <div>
        <h2>Stock Out</h2>
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

<form action="{{ route('inventory.stockOut', $item->id) }}" method="POST">
    @csrf

    <div class="form-group">
        <label>Quantity to Deduct</label>
        <input 
            type="number" 
            name="quantity" 
            min="1" 
            max="{{ $item->current_stock }}" 
            placeholder="Enter quantity" 
            required
        >
    </div>

    <div class="form-group">
        <label>Reference</label>
        <input 
            type="text" 
            name="reference" 
            placeholder="Example: Used for Order #1001, production, sample"
        >
    </div>

    <div class="form-group">
        <label>Remarks</label>
        <textarea name="remarks" placeholder="Optional notes"></textarea>
    </div>

    <button type="submit" class="btn btn-danger">Save Stock Out</button>
</form>

@endsection