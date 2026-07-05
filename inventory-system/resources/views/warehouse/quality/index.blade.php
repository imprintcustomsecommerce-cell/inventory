@extends('shared.layouts.app')

@section('title', 'Quality / QC')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Quality / QC</h1>
    <p class="mt-1 text-sm text-zinc-500">Defects, reprints, returns, and complaints across all projects.</p>
</div>


<div class="card overflow-hidden">
    <form method="GET" action="{{ route('quality.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reason, project…" class="input pl-9">
        </div>
        <select name="type" class="select sm:w-40" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach(\App\Models\ProjectIssue::TYPES as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        <select name="status" class="select sm:w-44" onchange="this.form.submit()">
            <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
            @foreach(\App\Models\ProjectIssue::STATUSES as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-dark">Filter</button>
    </form>

    @if($issues->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Project</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Rework</th>
                        <th class="text-right">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issues as $issue)
                        <tr>
                            <td><span class="badge {{ $issue->getTypeBadgeClass() }}">{{ $issue->type }}</span></td>
                            <td><span class="badge {{ $issue->getStatusBadgeClass() }}">{{ $issue->status }}</span></td>
                            <td class="font-medium text-zinc-900">{{ $issue->project?->project_name ?? '—' }}</td>
                            <td class="text-zinc-700">
                                {{ $issue->reason ?? '—' }}
                                @if($issue->description)<div class="text-xs text-zinc-400">{{ \Illuminate\Support\Str::limit($issue->description, 60) }}</div>@endif
                            </td>
                            <td class="text-zinc-500">{{ $issue->reported_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-right text-zinc-700">{{ $issue->quantity_affected ?: '—' }}</td>
                            <td class="text-right text-zinc-700">{{ (float) $issue->rework_cost > 0 ? '₱' . number_format($issue->rework_cost, 2) : '—' }}</td>
                            <td class="text-right">
                                @if($issue->project)
                                    <a href="{{ route('projects.show', $issue->project) }}" class="btn btn-ghost btn-sm">Open</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($issues->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $issues->links() }}</div>
        @endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No issues found</p>
            <p class="mt-1 text-sm text-zinc-500">Log quality issues from a project's page.</p>
        </div>
    @endif
</div>

@endsection
