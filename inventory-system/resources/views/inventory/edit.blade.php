@extends('layouts.app')

@section('title', 'Edit Inventory Item')

@section('content')

<div class="page-actions">
    <div>
        <h2>Edit Inventory Item</h2>
        <p>Update material or stock item details</p>
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

<form action="{{ route('inventory.update', $item->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label>Item Name</label>
        <input 
            type="text" 
            name="name" 
            value="{{ old('name', $item->name) }}" 
            required
        >
    </div>

    <div class="form-group">
        <label>Category</label>
        <select name="category">
            <option value="">Select Category</option>
            <option value="Fabric" {{ old('category', $item->category) == 'Fabric' ? 'selected' : '' }}>Fabric</option>
            <option value="Zipper" {{ old('category', $item->category) == 'Zipper' ? 'selected' : '' }}>Zipper</option>
            <option value="Thread" {{ old('category', $item->category) == 'Thread' ? 'selected' : '' }}>Thread</option>
            <option value="Collar" {{ old('category', $item->category) == 'Collar' ? 'selected' : '' }}>Collar</option>
            <option value="Cuffs" {{ old('category', $item->category) == 'Cuffs' ? 'selected' : '' }}>Cuffs</option>
            <option value="Label" {{ old('category', $item->category) == 'Label' ? 'selected' : '' }}>Label</option>
            <option value="Packaging" {{ old('category', $item->category) == 'Packaging' ? 'selected' : '' }}>Packaging</option>
            <option value="Other" {{ old('category', $item->category) == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

    <div class="form-group">
        <label>Unit</label>
        <select name="unit" required>
            <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
            <option value="yards" {{ old('unit', $item->unit) == 'yards' ? 'selected' : '' }}>yards</option>
            <option value="meters" {{ old('unit', $item->unit) == 'meters' ? 'selected' : '' }}>meters</option>
            <option value="rolls" {{ old('unit', $item->unit) == 'rolls' ? 'selected' : '' }}>rolls</option>
            <option value="packs" {{ old('unit', $item->unit) == 'packs' ? 'selected' : '' }}>packs</option>
            <option value="boxes" {{ old('unit', $item->unit) == 'boxes' ? 'selected' : '' }}>boxes</option>
        </select>
    </div>

    <div class="form-group">
        <label>Current Stock</label>
        <input 
            type="number" 
            value="{{ $item->current_stock }}" 
            disabled
        >
        <small>Current stock can only be changed using Stock In / Stock Out.</small>
    </div>

    <div class="form-group">
        <label>Minimum Stock Alert</label>
        <input 
            type="number" 
            name="minimum_stock" 
            value="{{ old('minimum_stock', $item->minimum_stock) }}" 
            min="0"
            required
        >
    </div>

    <div class="form-group">
        <label>Remarks</label>
        <textarea name="remarks">{{ old('remarks', $item->remarks) }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Update Item</button>
</form>

<hr style="margin: 24px 0;">

<form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
    @csrf
    @method('DELETE')

    <button type="submit" class="btn btn-danger">Delete Item</button>
</form>

@endsection