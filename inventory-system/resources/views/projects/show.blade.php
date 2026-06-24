@extends('layouts.app')

@section('title', $project->project_name)

@section('content')

<a href="{{ route('projects.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to projects
</a>

<!-- Header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $project->project_name }}</h1>
            <span class="badge {{ $project->getStatusBadgeClass() }}">{{ $project->status }}</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $project->customer_name ?? 'No customer' }} · {{ $project->product_type ?? 'No product type' }} · Qty {{ $project->quantity }}
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(!$project->materials_deducted && !in_array($project->status, ['Completed', 'Cancelled']))
            <form action="{{ route('projects.startProduction', $project) }}" method="POST"
                  onsubmit="return confirm('Start production? This deducts all listed materials from inventory.');">
                @csrf
                <button type="submit" class="btn btn-primary">Start production</button>
            </form>
        @endif
        @if($project->status !== 'Completed' && $project->status !== 'Cancelled')
            <form action="{{ route('projects.complete', $project) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-dark">Mark completed</button>
            </form>
        @endif
        <a href="{{ route('projects.pdf', $project) }}" target="_blank" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Job Order PDF
        </a>
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-ghost">Edit</a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Details -->
    <div class="space-y-6 lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Details</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Due date</dt>
                    <dd class="{{ $project->isOverdue() ? 'font-medium text-red-600' : 'text-zinc-900' }}">{{ $project->due_date ? $project->due_date->format('M d, Y') : '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Materials</dt>
                    <dd class="text-zinc-900">
                        @if($project->materials_deducted)
                            <span class="badge badge-green">Deducted</span>
                        @else
                            <span class="badge badge-zinc">Not yet</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Production started</dt>
                    <dd class="text-zinc-900">{{ $project->started_production_at?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Completed</dt>
                    <dd class="text-zinc-900">{{ $project->completed_at?->format('M d, Y') ?? '—' }}</dd>
                </div>
            </dl>
            @if($project->remarks)
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">Remarks</p>
                    <p class="mt-1 text-sm text-zinc-700">{{ $project->remarks }}</p>
                </div>
            @endif
        </div>

        <!-- Costing -->
        @php $cost = $project->materialsCost(); $margin = $project->margin(); @endphp
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Costing</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Material cost</dt>
                    <dd class="font-medium text-zinc-900">₱{{ number_format($cost, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Quoted price</dt>
                    <dd class="font-medium text-zinc-900">{{ $project->quoted_price !== null ? '₱' . number_format($project->quoted_price, 2) : '—' }}</dd>
                </div>
                @if($margin !== null)
                    <div class="flex justify-between gap-4 border-t border-zinc-100 pt-3">
                        <dt class="text-zinc-500">Margin</dt>
                        <dd class="font-semibold {{ $margin >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₱{{ number_format($margin, 2) }}</dd>
                    </div>
                @endif
            </dl>
            <p class="mt-3 text-xs text-zinc-400">Material cost uses each item's unit cost × quantity needed.</p>
        </div>

        <!-- Status history -->
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Status history</h2>
            @if($project->statusLogs->count() > 0)
                <ol class="mt-4 space-y-4">
                    @foreach($project->statusLogs as $log)
                        <li class="relative flex gap-3">
                            <span class="mt-1 flex h-2 w-2 shrink-0 rounded-full bg-brand-400"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-zinc-900">
                                    @if($log->from_status)
                                        <span class="text-zinc-500">{{ $log->from_status }}</span>
                                        <span class="text-zinc-300">→</span>
                                    @endif
                                    <span class="font-medium">{{ $log->to_status }}</span>
                                </p>
                                @if($log->note)<p class="text-xs text-zinc-500">{{ $log->note }}</p>@endif
                                <p class="text-xs text-zinc-400">{{ $log->created_at->format('M d, Y · h:i A') }}{{ $log->user ? ' · ' . $log->user->name : '' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-3 text-sm text-zinc-500">No status changes recorded yet.</p>
            @endif
        </div>
    </div>

    <!-- Materials (Bill of materials) -->
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Bill of materials</h2>
                    <p class="text-xs text-zinc-500">Stock deducted from inventory when production starts.</p>
                </div>
                @if($project->materials_deducted)
                    <span class="badge badge-green">Locked</span>
                @elseif($hasTemplate)
                    <form action="{{ route('projects.applyTemplate', $project) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Apply {{ $project->product_type }} template
                        </button>
                    </form>
                @endif
            </div>

            @if(!$project->materials_deducted)
                <form action="{{ route('projects.materials.add', $project) }}" method="POST" class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="label">Material</label>
                        <select name="inventory_item_id" required class="select">
                            <option value="">Select item…</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->displayName() }} ({{ $item->current_stock }} {{ $item->unit }} in stock)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-32">
                        <label class="label">Qty needed</label>
                        <input type="number" name="quantity_needed" min="0.01" step="0.01" required class="input">
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            @endif

            @if($project->materials->count() > 0)
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Needed</th>
                                <th>{{ $project->materials_deducted ? 'Used' : 'In stock' }}</th>
                                <th>Status</th>
                                @if(!$project->materials_deducted)<th class="text-right">Remove</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->materials as $material)
                                @php
                                    $needed = (float) $material->quantity_needed;
                                    $available = (float) $material->inventoryItem->current_stock;
                                    $enough = $available >= $needed;
                                    $shortBy = rtrim(rtrim(number_format($needed - $available, 2), '0'), '.');
                                @endphp
                                <tr>
                                    <td class="font-medium text-zinc-900">
                                        {{ $material->inventoryItem->displayName() }}
                                        @if($material->remarks)<div class="text-xs text-zinc-400">{{ $material->remarks }}</div>@endif
                                    </td>
                                    <td class="text-zinc-700">{{ rtrim(rtrim(number_format($material->quantity_needed, 2), '0'), '.') }} {{ $material->inventoryItem->unit }}</td>
                                    <td class="text-zinc-700">
                                        @if($project->materials_deducted)
                                            {{ rtrim(rtrim(number_format($material->quantity_used, 2), '0'), '.') }} {{ $material->inventoryItem->unit }}
                                        @else
                                            {{ $available }} {{ $material->inventoryItem->unit }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->materials_deducted)
                                            <span class="badge badge-green">Deducted</span>
                                        @elseif($enough)
                                            <span class="badge badge-green">Available</span>
                                        @else
                                            <span class="badge badge-red">Short {{ $shortBy }}</span>
                                        @endif
                                    </td>
                                    @if(!$project->materials_deducted)
                                        <td class="text-right">
                                            <form action="{{ route('projects.materials.remove', [$project, $material]) }}" method="POST" onsubmit="return confirm('Remove this material?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center text-sm text-zinc-500">
                    No materials listed yet. Add the items this project consumes above.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
