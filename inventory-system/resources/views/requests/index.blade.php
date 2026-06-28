@extends('layouts.app')

@section('title', 'Stock Requests')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Stock requests</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $canFulfill ? 'Requests from stores to fulfill from Inventory.' : 'Request stock from the Inventory stockroom.' }}</p>
    </div>
    @unless($canFulfill)
        <form action="{{ route('requests.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Request
            </button>
        </form>
    @endunless
</div>

<div class="card overflow-hidden">
    @if($requests->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Location</th>
                    <th>Items</th>
                    <th>Requested by</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                    <tr>
                        <td class="font-medium text-zinc-900">#{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td><span class="badge badge-zinc">{{ $req->warehouse?->name ?? '—' }}</span></td>
                        <td class="text-zinc-500">{{ $req->items_count }}</td>
                        <td class="text-zinc-500">{{ $req->requestedBy?->name ?? '—' }}</td>
                        <td><span class="badge {{ $req->getStatusBadgeClass() }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="text-zinc-500">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="text-right"><a href="{{ route('requests.show', $req) }}" class="btn btn-ghost btn-sm">Open</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($requests->hasPages())<div class="border-t border-zinc-200 px-5 py-3">{{ $requests->links() }}</div>@endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No requests yet</p>
            <p class="mt-1 text-sm text-zinc-500">{{ $canFulfill ? 'Store requests will appear here.' : 'Start a request to get stock from Inventory.' }}</p>
        </div>
    @endif
</div>

@endsection
