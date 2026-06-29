@extends('layouts.app')

@section('title', 'Activity')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Activity</h1>
    <p class="mt-1 text-sm text-zinc-500">Recent events across orders, production, deliveries, and feedback.</p>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('activity.index') }}" class="flex gap-3 border-b border-zinc-200 p-4">
        <select name="type" class="select sm:w-48" onchange="this.form.submit()">
            <option value="">All activity</option>
            @foreach(['order' => 'Online orders', 'project' => 'Projects', 'proof' => 'Proofs', 'delivery' => 'Deliveries', 'quality' => 'Quality', 'feedback' => 'Feedback'] as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    @if($activities->count() > 0)
        <ul class="divide-y divide-zinc-100">
            @foreach($activities as $activity)
                <li class="flex items-start gap-3 px-6 py-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold uppercase {{ $activity->iconColor() }}">
                        {{ substr($activity->type, 0, 1) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-zinc-900">
                            @if($activity->url)
                                <a href="{{ $activity->url }}" class="hover:underline">{{ $activity->title }}</a>
                            @else
                                {{ $activity->title }}
                            @endif
                        </p>
                        @if($activity->description)<p class="text-sm text-zinc-500">{{ $activity->description }}</p>@endif
                        <p class="mt-0.5 text-xs text-zinc-400">
                            {{ $activity->created_at->diffForHumans() }}{{ $activity->user ? ' · ' . $activity->user->name : '' }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
        @if($activities->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $activities->links() }}</div>
        @endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No activity yet</p>
            <p class="mt-1 text-sm text-zinc-500">Events will appear here as orders, proofs, deliveries, and feedback happen.</p>
        </div>
    @endif
</div>

@endsection
