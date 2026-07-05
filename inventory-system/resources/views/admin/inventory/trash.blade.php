@extends('shared.layouts.app')

@section('title', 'Trash')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('inventory.index') }}" class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Back to inventory
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Trash</h1>
        <p class="mt-1 text-sm text-zinc-500">Deleted items stay here until you restore or permanently delete them.</p>
    </div>
</div>

<div class="card overflow-hidden">
    @if($items->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Warehouse</th>
                        <th>Size</th>
                        <th>Stock</th>
                        <th>Deleted</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $item->name }}</td>
                            <td><span class="badge badge-zinc">{{ $item->warehouse?->name ?? '—' }}</span></td>
                            <td>{{ $item->size ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $item->current_stock }} {{ $item->unit }}</td>
                            <td class="text-zinc-500">{{ $item->deleted_at?->diffForHumans() }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('inventory.restore', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                                            Restore
                                        </button>
                                    </form>
                                    <form action="{{ route('inventory.forceDelete', $item->id) }}" method="POST" onsubmit="return confirm('Permanently delete “{{ $item->name }}”? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Delete permanently">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $items->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">Trash is empty</p>
            <p class="mt-1 text-sm text-zinc-500">Deleted items will appear here.</p>
        </div>
    @endif
</div>

@endsection
