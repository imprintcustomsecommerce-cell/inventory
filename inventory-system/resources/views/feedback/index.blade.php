@extends('layouts.app')

@section('title', 'Customer Feedback')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Customer Feedback</h1>
    <p class="mt-1 text-sm text-zinc-500">Ratings and reviews from delivered projects.</p>
</div>

<!-- Stats -->
<div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="card p-6">
        <p class="text-sm font-medium text-zinc-500">Average rating</p>
        <div class="mt-2 flex items-end gap-2">
            <p class="text-4xl font-bold text-zinc-900">{{ number_format($stats['average'], 1) }}</p>
            <p class="mb-1 text-lg text-amber-500">{{ str_repeat('★', (int) round($stats['average'])) }}{{ str_repeat('☆', 5 - (int) round($stats['average'])) }}</p>
        </div>
        <p class="mt-1 text-xs text-zinc-400">from {{ $stats['count'] }} {{ Str::plural('review', $stats['count']) }}</p>
    </div>

    <div class="card p-6">
        <p class="text-sm font-medium text-zinc-500">Would recommend</p>
        <p class="mt-2 text-4xl font-bold text-emerald-600">{{ $stats['recommend_pct'] }}%</p>
    </div>

    <div class="card p-6">
        <p class="mb-2 text-sm font-medium text-zinc-500">Distribution</p>
        @foreach($stats['distribution'] as $star => $n)
            @php $pct = $stats['count'] > 0 ? round($n / $stats['count'] * 100) : 0; @endphp
            <div class="flex items-center gap-2 text-xs">
                <span class="w-6 text-zinc-500">{{ $star }}★</span>
                <div class="h-2 flex-1 rounded-full bg-zinc-100">
                    <div class="h-2 rounded-full bg-amber-400" style="width: {{ $pct }}%"></div>
                </div>
                <span class="w-6 text-right text-zinc-500">{{ $n }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('feedback.index') }}" class="flex gap-3 border-b border-zinc-200 p-4">
        <select name="rating" class="select sm:w-44" onchange="this.form.submit()">
            <option value="">All ratings</option>
            @for($r = 5; $r >= 1; $r--)
                <option value="{{ $r }}" {{ (string) request('rating') === (string) $r ? 'selected' : '' }}>{{ $r }} stars</option>
            @endfor
        </select>
    </form>

    @if($feedback->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rating</th>
                        <th>Project</th>
                        <th>Reviewer</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-right">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feedback as $fb)
                        <tr>
                            <td class="whitespace-nowrap text-amber-500">{{ $fb->stars() }}</td>
                            <td class="font-medium text-zinc-900">{{ $fb->project?->project_name ?? '—' }}</td>
                            <td class="text-zinc-700">{{ $fb->reviewer_name ?? '—' }}</td>
                            <td class="text-zinc-600">{{ \Illuminate\Support\Str::limit($fb->comment, 80) ?: '—' }}</td>
                            <td class="text-zinc-500">{{ $fb->submitted_at?->format('M d, Y') }}</td>
                            <td class="text-right">
                                @if($fb->project)
                                    <a href="{{ route('projects.show', $fb->project) }}" class="btn btn-ghost btn-sm">Open</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($feedback->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $feedback->links() }}</div>
        @endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No feedback yet</p>
            <p class="mt-1 text-sm text-zinc-500">Record feedback from a delivered project's page.</p>
        </div>
    @endif
</div>

@endsection
