@extends('shared.layouts.app')

@section('title', 'Material History')

@section('content')

<a href="{{ route('materials.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to materials
</a>

<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Stock history</h1>
    <p class="mt-1 text-sm text-zinc-500">{{ $material->name }} — {{ $material->current_stock }} {{ $material->unit }} on hand</p>
</div>

<div class="card overflow-hidden">
    @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Quantity</th><th>By</th><th>Reference</th><th>Remarks</th></tr>
                </thead>
                <tbody>
                    @foreach($movements as $mv)
                        <tr>
                            <td class="whitespace-nowrap text-zinc-500">{{ $mv->created_at->format('M d, Y · h:i A') }}</td>
                            <td><span class="badge {{ $mv->getTypeBadgeClass() }}">{{ $mv->getTypeLabel() }}</span></td>
                            <td class="font-semibold text-zinc-900">{{ $mv->quantity }} {{ $material->unit }}</td>
                            <td class="text-zinc-500">{{ $mv->user?->name ?? 'System' }}</td>
                            <td class="text-zinc-500">{{ $mv->reference ?? '—' }}</td>
                            <td class="max-w-xs truncate text-zinc-500">{{ $mv->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())<div class="border-t border-zinc-200 px-5 py-3">{{ $movements->links() }}</div>@endif
    @else
        <div class="px-6 py-12 text-center text-sm text-zinc-500">No movements yet for this material.</div>
    @endif
</div>

@endsection
