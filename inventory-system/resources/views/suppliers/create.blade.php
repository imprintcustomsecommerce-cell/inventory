@extends('layouts.app')

@section('title', 'New Supplier')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('suppliers.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to suppliers
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">New supplier</h1>
        <p class="mt-1 text-sm text-zinc-500">Save a supplier to use on purchase orders.</p>
    </div>

    <form action="{{ route('suppliers.store') }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @include('suppliers.partials.form-fields')
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Add supplier</button>
        </div>
    </form>
</div>

@endsection
