@extends('layouts.app')

@section('title', 'Production Calendar')

@section('content')

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Production Calendar</h1>
        <p class="mt-1 text-sm text-zinc-500">Project due dates and scheduled deliveries at a glance.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" class="btn btn-ghost" title="Previous month">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <span class="min-w-[10rem] text-center text-sm font-semibold text-zinc-900">{{ $month->format('F Y') }}</span>
        <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="btn btn-ghost" title="Next month">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </a>
        <a href="{{ route('calendar.index') }}" class="btn btn-dark">Today</a>
    </div>
</div>

<!-- Legend -->
<div class="mb-4 flex flex-wrap items-center gap-4 text-xs text-zinc-500">
    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-brand-400"></span> Project due</span>
    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> Overdue</span>
    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span> Delivery</span>
</div>

<div class="card overflow-hidden">
    <!-- Weekday headers -->
    <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">
        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow)
            <div class="px-2 py-2">{{ $dow }}</div>
        @endforeach
    </div>

    <!-- Day grid -->
    <div class="grid grid-cols-7">
        @foreach($days as $day)
            <div class="min-h-[7rem] border-b border-r border-zinc-100 p-1.5 {{ $day['in_month'] ? '' : 'bg-zinc-50/60' }}">
                <div class="mb-1 flex justify-end">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium
                        {{ $day['is_today'] ? 'bg-brand-400 text-zinc-900 font-bold' : ($day['in_month'] ? 'text-zinc-700' : 'text-zinc-300') }}">
                        {{ $day['date']->day }}
                    </span>
                </div>

                <div class="space-y-1">
                    @foreach($day['events'] as $event)
                        @php
                            $color = $event['type'] === 'delivery'
                                ? 'border-sky-200 bg-sky-50 text-sky-800'
                                : ($event['overdue'] ? 'border-red-200 bg-red-50 text-red-700' : 'border-brand-200 bg-brand-50 text-zinc-800');
                        @endphp
                        <a href="{{ $event['url'] }}" title="{{ $event['label'] }} · {{ $event['status'] }}"
                           class="block truncate rounded border px-1.5 py-0.5 text-[11px] leading-tight {{ $color }} hover:opacity-80">
                            {{ $event['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
