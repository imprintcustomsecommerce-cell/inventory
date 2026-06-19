@extends('layouts.app')

@section('title', 'Adjust Stock')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Adjust stock</h1>
            <p class="text-sm text-zinc-500">Correct the count for {{ $item->name }} after a physical check.</p>
        </div>
    </div>

    <div class="mb-5 flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4">
        <span class="text-sm font-medium text-zinc-500">System stock</span>
        <span class="text-lg font-semibold text-zinc-900">{{ $item->current_stock }} <span class="text-sm font-normal text-zinc-400">{{ $item->unit }}</span></span>
    </div>

    <form action="{{ route('inventory.adjustStock', $item->id) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Actual stock count</label>
                <div class="relative">
                    <input type="number" name="actual_stock" min="0" required value="{{ old('actual_stock', $item->current_stock) }}" class="input pr-16 @error('actual_stock') input-error @enderror">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $item->unit }}</span>
                </div>
                <p class="mt-1.5 text-xs text-zinc-400">Enter the verified count. The difference is logged automatically.</p>
                @error('actual_stock') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Reference <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Physical count, audit" class="input">
            </div>
            <div>
                <label class="label">Reason <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" placeholder="Why is the stock being adjusted?" class="textarea">{{ old('remarks') }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Confirm adjustment</button>
        </div>
    </form>
</div>

@endsection
