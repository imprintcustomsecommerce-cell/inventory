@extends('layouts.app')

@section('title', 'Events')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Events</h1>
        <p class="mt-1 text-sm text-zinc-500">Pop-up / booth locations. Pull stock in with a transfer from Inventory.</p>
    </div>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('events.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Event
        </a>
    @endif
</div>

@if($events->count() > 0)
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($events as $event)
            <a href="{{ route('events.show', $event) }}" class="card p-5 transition hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-zinc-900">{{ $event->name }}</h3>
                        @if($event->location)<p class="text-sm text-zinc-500">{{ $event->location }}</p>@endif
                    </div>
                    @if($event->event_date)<span class="badge badge-amber">{{ $event->event_date->format('M d') }}</span>@endif
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-3 text-sm">
                    <span class="text-zinc-500">{{ rtrim(rtrim(number_format($event->stock_total, 2), '0'), '.') }} in stock</span>
                    <span class="font-semibold text-emerald-600">₱{{ number_format($event->revenue, 2) }}</span>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
        <p class="text-sm font-medium text-zinc-900">No events yet</p>
        <p class="mt-1 text-sm text-zinc-500">Create an event, then transfer stock from Inventory to it.</p>
        @if(auth()->user()->isAdmin())<a href="{{ route('events.create') }}" class="btn btn-primary mt-4">New Event</a>@endif
    </div>
@endif

@endsection
