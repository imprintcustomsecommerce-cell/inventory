@extends('layouts.app')

@section('title', 'Projects')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Projects</h1>
        <p class="mt-1 text-sm text-zinc-500">Customer orders and production jobs.</p>
    </div>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Project
    </a>
</div>

<!-- Stats -->
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $cards = [
            ['label' => 'Total', 'value' => $stats['total'], 'accent' => 'text-zinc-900', 'ring' => 'bg-zinc-100 text-zinc-600'],
            ['label' => 'Pending', 'value' => $stats['pending'], 'accent' => 'text-zinc-900', 'ring' => 'bg-zinc-100 text-zinc-600'],
            ['label' => 'In Production', 'value' => $stats['production'], 'accent' => 'text-amber-600', 'ring' => 'bg-amber-50 text-amber-600'],
            ['label' => 'Completed', 'value' => $stats['completed'], 'accent' => 'text-emerald-600', 'ring' => 'bg-emerald-50 text-emerald-600'],
        ];
    @endphp
    @foreach($cards as $c)
        <div class="card p-5">
            <p class="text-sm font-medium text-zinc-500">{{ $c['label'] }}</p>
            <p class="mt-2 text-3xl font-bold {{ $c['accent'] }}">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('projects.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search project, customer, product…" class="input pl-9">
        </div>
        <select name="status" class="select sm:w-52">
            <option value="">All statuses</option>
            @foreach(\App\Models\Project::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('projects.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($projects->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th>Materials</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $project->project_name }}</td>
                            <td class="text-zinc-500">{{ $project->customer_name ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $project->product_type ?? '—' }}</td>
                            <td class="text-zinc-700">{{ $project->quantity }}</td>
                            <td><span class="badge {{ $project->getStatusBadgeClass() }}">{{ $project->status }}</span></td>
                            <td>
                                @if($project->due_date)
                                    <span class="{{ $project->isOverdue() ? 'font-medium text-red-600' : 'text-zinc-500' }}">{{ $project->due_date->format('M d, Y') }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td>
                                @if($project->materials_deducted)
                                    <span class="badge badge-green">Deducted</span>
                                @else
                                    <span class="badge badge-zinc">{{ $project->materials_count }} listed</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $projects->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No projects found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') || request('status') ? 'Try adjusting your filters.' : 'Create your first project to get started.' }}</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary mt-4">New Project</a>
        </div>
    @endif
</div>

@endsection
