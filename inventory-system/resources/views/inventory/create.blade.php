@extends('layouts.app')

@section('title', 'Add Inventory Item')

@section('content')

<div class="page-actions">
    <div>
        <h2>Add Inventory Item</h2>
        <p>Create new material or stock item</p>
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

<form action="{{ route('inventory.store') }}" method="POST">
    @csrf

    <div class="form-group">
        <label>Item Name</label>
        <input 
            type="text" 
            name="name" 
            value="{{ old('name') }}" 
            placeholder="Example: Aircool Fabric, Zipper, Thread"
            required
        >
    </div>

    <div class="form-group">
        <label>Category</label>
        <select name="category">
            <option value="">Select Category</option>
            <option value="Fabric" {{ old('category') == 'Fabric' ? 'selected' : '' }}>Fabric</option>
            <option value="Zipper" {{ old('category') == 'Zipper' ? 'selected' : '' }}>Zipper</option>
            <option value="Thread" {{ old('category') == 'Thread' ? 'selected' : '' }}>Thread</option>
            <option value="Collar" {{ old('category') == 'Collar' ? 'selected' : '' }}>Collar</option>
            <option value="Cuffs" {{ old('category') == 'Cuffs' ? 'selected' : '' }}>Cuffs</option>
            <option value="Label" {{ old('category') == 'Label' ? 'selected' : '' }}>Label</option>
            <option value="Packaging" {{ old('category') == 'Packaging' ? 'selected' : '' }}>Packaging</option>
            <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

    <div class="form-group">
        <label>Unit</label>
        <select name="unit" required>
            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
            <option value="yards" {{ old('unit') == 'yards' ? 'selected' : '' }}>yards</option>
            <option value="meters" {{ old('unit') == 'meters' ? 'selected' : '' }}>meters</option>
            <option value="rolls" {{ old('unit') == 'rolls' ? 'selected' : '' }}>rolls</option>
            <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>packs</option>
            <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>boxes</option>
        </select>
    </div>

    <div class="form-group">
        <label>Beginning Stock</label>
        <input 
            type="number" 
            name="current_stock" 
            value="{{ old('current_stock', 0) }}" 
            min="0"
            required
        >
    </div>

    <div class="form-group">
        <label>Minimum Stock Alert</label>
        <input 
            type="number" 
            name="minimum_stock" 
            value="{{ old('minimum_stock', 0) }}" 
            min="0"
            required
        >
    </div>

    <div class="form-group">
        <label>Remarks</label>
        <textarea name="remarks" placeholder="Optional notes">{{ old('remarks') }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Save Item</button>
</form>

@endsection