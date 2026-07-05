@extends('shared.layouts.app')

@section('title', $expense->exists ? 'Edit Expense' : 'New Expense')

@section('content')

@php $editing = $expense->exists; @endphp

<div class="mx-auto max-w-2xl">
    <a href="{{ route('expenses.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to expenses
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $editing ? 'Edit expense' : 'New expense' }}</h1>
        <p class="mt-1 text-sm text-zinc-500">Track shop overhead and operating costs.</p>
    </div>

    <form action="{{ $editing ? route('expenses.update', $expense) : route('expenses.store') }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Category</label>
                    <select name="category" required class="select @error('category') input-error @enderror">
                        <option value="">Select category</option>
                        @foreach(\App\Models\Expense::CATEGORIES as $c)
                            <option value="{{ $c }}" {{ old('category', $expense->category) == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Amount (₱)</label>
                    <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" min="0.01" step="0.01" required class="input @error('amount') input-error @enderror">
                    @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label">Description</label>
                <input type="text" name="description" value="{{ old('description', $expense->description) }}" required placeholder="e.g. June electricity bill" class="input @error('description') input-error @enderror">
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Date</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="input @error('expense_date') input-error @enderror">
                    @error('expense_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Payment method <span class="font-normal text-zinc-400">(optional)</span></label>
                    <select name="payment_method" class="select">
                        <option value="">—</option>
                        @foreach(\App\Models\Expense::PAYMENT_METHODS as $m)
                            <option value="{{ $m }}" {{ old('payment_method', $expense->payment_method) == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Supplier <span class="font-normal text-zinc-400">(optional)</span></label>
                    <select name="supplier_id" class="select">
                        <option value="">—</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ (string) old('supplier_id', $expense->supplier_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Reference <span class="font-normal text-zinc-400">(optional)</span></label>
                    <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}" placeholder="OR / invoice #" class="input">
                </div>
            </div>

            <div>
                <label class="label">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="notes" rows="3" class="textarea">{{ old('notes', $expense->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 bg-zinc-50 px-6 py-4">
            <div>
                @if($editing)
                    <button form="delete-expense" class="btn btn-ghost text-red-600 hover:bg-red-50">Delete</button>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Record expense' }}</button>
            </div>
        </div>
    </form>

    @if($editing)
        <form id="delete-expense" action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Delete this expense?');" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif
</div>

@endsection
