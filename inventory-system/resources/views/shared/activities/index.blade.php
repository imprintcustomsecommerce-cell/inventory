@extends('shared.layouts.app')

@section('title', 'Activity')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
            Activity
        </h1>
        <p class="mt-1 text-sm text-zinc-500">
            Recent events across orders, production, deliveries, quality, and feedback.
        </p>
    </div>

    <form method="GET" action="{{ route('activity.index') }}">
        <select
            name="type"
            onchange="this.form.submit()"
            class="select sm:w-52"
        >
            <option value="">All activity</option>

            @foreach([
                'order' => 'Online orders',
                'project' => 'Projects',
                'proof' => 'Proofs',
                'delivery' => 'Deliveries',
                'quality' => 'Quality',
                'feedback' => 'Feedback'
            ] as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="overflow-hidden card">

    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
        <div>
            <h2 class="text-sm font-semibold text-zinc-950">
                Recent Activity
            </h2>
            <p class="mt-0.5 text-xs text-zinc-500">
                Latest system movements and updates.
            </p>
        </div>

        <div class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-700">
            {{ $activities->total() ?? $activities->count() }} records
        </div>
    </div>

    @if($activities->count() > 0)
        <ul class="divide-y divide-zinc-100">
            @foreach($activities as $activity)
                <li class="group flex items-start gap-4 px-5 py-4 transition hover:bg-zinc-50">

                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold uppercase {{ $activity->iconColor() }}">
                        {{ substr($activity->type, 0, 1) }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p class="truncate text-sm font-medium text-zinc-950">
                                @if($activity->url)
                                    <a href="{{ $activity->url }}" class="hover:text-yellow-600">
                                        {{ $activity->title }}
                                    </a>
                                @else
                                    {{ $activity->title }}
                                @endif
                            </p>

                            <p class="shrink-0 text-xs text-zinc-400">
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>

                        @if($activity->description)
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $activity->description }}
                            </p>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600">
                                {{ ucfirst($activity->type) }}
                            </span>

                            @if($activity->user)
                                <span class="text-xs text-zinc-400">
                                    by {{ $activity->user->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($activity->url)
                        <svg class="mt-2 h-4 w-4 shrink-0 text-zinc-300 transition group-hover:text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    @endif

                </li>
            @endforeach
        </ul>

        @if($activities->hasPages())
            <div class="border-t border-zinc-100 px-5 py-4">
                {{ $activities->links() }}
            </div>
        @endif
    @else
        <div class="px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2A10 10 0 112 12a10 10 0 0120 0z"/>
                </svg>
            </div>

            <p class="text-sm font-medium text-zinc-950">
                No activity yet
            </p>

            <p class="mt-1 text-sm text-zinc-500">
                Events will appear here as orders, proofs, deliveries, quality checks, and feedback happen.
            </p>
        </div>
    @endif
</div>

@endsection