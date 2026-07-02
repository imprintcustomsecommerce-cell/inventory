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
            @if($project->customer)
                <a href="{{ route('customers.show', $project->customer) }}" class="font-medium text-zinc-700 underline-offset-2 hover:underline">{{ $project->customer->name }}</a>
            @else
                {{ $project->customer_name ?? 'No customer' }}
            @endif
            · {{ $project->product_type ?? 'No product type' }} · Qty {{ $project->quantity }}
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
            <p class="mt-3 text-xs text-zinc-400">Margin = quoted price − material cost.</p>
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
        <!-- Customer portal link -->
        <div class="card mb-6 p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Customer portal</h2>
                    <p class="text-xs text-zinc-500">Share a private link so the customer can view and approve proofs — no login.</p>
                </div>
                <form action="{{ route('projects.share', $project) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn {{ $project->public_token ? 'btn-ghost' : 'btn-primary' }} btn-sm">
                        {{ $project->public_token ? 'Regenerate' : 'Generate link' }}
                    </button>
                </form>
            </div>
            @if($project->public_token)
                <div class="mt-3 flex items-center gap-2">
                    <input type="text" readonly value="{{ $project->portalUrl() }}" onclick="this.select()" class="input text-xs">
                    <a href="{{ $project->portalUrl() }}" target="_blank" class="btn btn-ghost btn-sm shrink-0">Open</a>
                </div>
                <p class="mt-2 text-xs text-zinc-400">Anyone with this link can view and approve. Regenerate to revoke the old one.</p>
            @endif
        </div>

        <!-- Design proofs -->
        <div class="card mb-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Design proofs</h2>
                    <p class="text-xs text-zinc-500">Upload artwork, send for approval, and track customer sign-off.</p>
                </div>
                @if($project->hasApprovedProof())
                    <span class="badge badge-green">Approved</span>
                @elseif($project->proofs->isNotEmpty() && $project->latestProof()->isPending())
                    <span class="badge badge-amber">Awaiting approval</span>
                @endif
            </div>

            <form action="{{ route('projects.proofs.upload', $project) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="label">Artwork / proof file</label>
                    <input type="file" name="file" required class="input" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.ai,.psd">
                    <p class="mt-1 text-xs text-zinc-400">JPG, PNG, GIF, WEBP, SVG, PDF, AI, PSD · up to 20 MB</p>
                </div>
                <div class="flex-1">
                    <label class="label">Note (optional)</label>
                    <input type="text" name="feedback" value="{{ old('feedback') }}" class="input" placeholder="e.g. Front print, v2 with bigger logo">
                </div>
                <button type="submit" class="btn btn-primary">Upload proof</button>
            </form>
            @error('file')<p class="px-4 pt-3 text-xs text-red-600">{{ $message }}</p>@enderror

            @if($project->proofs->count() > 0)
                <ul class="divide-y divide-zinc-100">
                    @foreach($project->proofs as $proof)
                        <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex items-start gap-3">
                                @if($proof->isImage())
                                    <a href="{{ $proof->url() }}" target="_blank">
                                        <img src="{{ $proof->url() }}" alt="Proof v{{ $proof->version }}" class="h-16 w-16 rounded-lg border border-zinc-200 object-cover">
                                    </a>
                                @else
                                    <span class="flex h-16 w-16 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 text-xs font-medium text-zinc-500">FILE</span>
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-zinc-900">v{{ $proof->version }}</span>
                                        <span class="badge {{ $proof->getStatusBadgeClass() }}">{{ $proof->status }}</span>
                                    </div>
                                    <a href="{{ $proof->url() }}" target="_blank" class="block max-w-[16rem] truncate text-sm text-brand-600 underline-offset-2 hover:underline">{{ $proof->original_name }}</a>
                                    <p class="text-xs text-zinc-400">
                                        {{ $proof->humanSize() }} · {{ $proof->created_at->format('M d, Y') }}{{ $proof->uploader ? ' · ' . $proof->uploader->name : '' }}
                                    </p>
                                    @if($proof->feedback)
                                        <p class="mt-1 text-xs text-zinc-600">“{{ $proof->feedback }}”</p>
                                    @endif
                                    @if($proof->decided_at)
                                        <p class="text-xs text-zinc-400">{{ $proof->status }} {{ $proof->decided_at->format('M d, Y') }}{{ $proof->decider ? ' · ' . $proof->decider->name : '' }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                @if($proof->isPending())
                                    <form action="{{ route('projects.proofs.approve', [$project, $proof]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('reject-{{ $proof->id }}').classList.toggle('hidden')">Request revision</button>
                                @endif
                                <form action="{{ route('projects.proofs.delete', [$project, $proof]) }}" method="POST" onsubmit="return confirm('Delete this proof?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </form>
                            </div>

                            @if($proof->isPending())
                                <form id="reject-{{ $proof->id }}" action="{{ route('projects.proofs.reject', [$project, $proof]) }}" method="POST" class="hidden w-full basis-full sm:order-last">
                                    @csrf
                                    <div class="mt-2 flex flex-col gap-2 rounded-lg bg-red-50 p-3 sm:flex-row sm:items-end">
                                        <div class="flex-1">
                                            <label class="label">Revision feedback</label>
                                            <input type="text" name="feedback" required class="input" placeholder="What needs to change?">
                                        </div>
                                        <button type="submit" class="btn btn-dark">Send back for revision</button>
                                    </div>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-12 text-center text-sm text-zinc-500">
                    No proofs uploaded yet. Upload the first artwork to start the approval flow.
                </div>
            @endif
        </div>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Bill of materials</h2>
                    <p class="text-xs text-zinc-500">Stock deducted from inventory when production starts.</p>
                </div>
                @if($project->materials_deducted)
                    <span class="badge badge-green">Locked</span>
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
                        <input type="number" name="quantity_needed" min="1" step="1" required class="input">
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

        <!-- Delivery & dispatch -->
        <div class="card mt-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Delivery &amp; dispatch</h2>
                    <p class="text-xs text-zinc-500">Schedule, dispatch, and confirm receipt of finished goods.</p>
                </div>
                @if($project->deliveries->isNotEmpty())
                    <span class="badge {{ $project->latestDelivery()->getStatusBadgeClass() }}">{{ $project->latestDelivery()->status }}</span>
                @endif
            </div>

            <form action="{{ route('projects.deliveries.store', $project) }}" method="POST" class="grid grid-cols-1 gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label class="label">Method</label>
                    <input type="text" name="method" value="{{ old('method') }}" class="input" placeholder="Pickup, Lalamove, J&T…">
                </div>
                <div>
                    <label class="label">Courier (optional)</label>
                    <input type="text" name="courier" value="{{ old('courier') }}" class="input">
                </div>
                <div>
                    <label class="label">Tracking # (optional)</label>
                    <input type="text" name="tracking_number" value="{{ old('tracking_number') }}" class="input">
                </div>
                <div>
                    <label class="label">Recipient</label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $project->customer->name ?? $project->customer_name) }}" class="input">
                </div>
                <div>
                    <label class="label">Contact #</label>
                    <input type="text" name="recipient_contact" value="{{ old('recipient_contact') }}" class="input">
                </div>
                <div>
                    <label class="label">Scheduled date</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}" class="input">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="input" placeholder="Delivery address">
                </div>
                <div>
                    <label class="label">Delivery fee (₱)</label>
                    <input type="number" name="fee" min="0" step="0.01" value="{{ old('fee') }}" class="input">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex justify-end">
                    <button type="submit" class="btn btn-primary">Schedule delivery</button>
                </div>
            </form>

            @if($project->deliveries->count() > 0)
                <ul class="divide-y divide-zinc-100">
                    @foreach($project->deliveries as $delivery)
                        <li class="p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="badge {{ $delivery->getStatusBadgeClass() }}">{{ $delivery->status }}</span>
                                        <span class="text-sm font-medium text-zinc-900">{{ $delivery->method ?? 'Delivery' }}</span>
                                        @if($delivery->tracking_number)<span class="text-xs text-zinc-400">#{{ $delivery->tracking_number }}</span>@endif
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        {{ $delivery->recipient_name ?? '—' }}{{ $delivery->recipient_contact ? ' · ' . $delivery->recipient_contact : '' }}
                                    </p>
                                    @if($delivery->address)<p class="text-xs text-zinc-500">{{ $delivery->address }}</p>@endif
                                    <p class="mt-1 text-xs text-zinc-400">
                                        @if($delivery->scheduled_date)Scheduled {{ $delivery->scheduled_date->format('M d, Y') }} · @endif
                                        @if($delivery->courier){{ $delivery->courier }} · @endif
                                        @if((float) $delivery->fee > 0)Fee ₱{{ number_format($delivery->fee, 2) }} · @endif
                                        @if($delivery->dispatched_at)Dispatched {{ $delivery->dispatched_at->format('M d, h:i A') }} · @endif
                                        @if($delivery->delivered_at)Delivered {{ $delivery->delivered_at->format('M d, h:i A') }}{{ $delivery->received_by ? ' to ' . $delivery->received_by : '' }}@endif
                                    </p>
                                    @if($delivery->remarks)<p class="mt-1 text-xs text-zinc-600">“{{ $delivery->remarks }}”</p>@endif
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    @if($delivery->status === 'Scheduled')
                                        <form action="{{ route('projects.deliveries.status', [$project, $delivery]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="Out for Delivery">
                                            <button type="submit" class="btn btn-primary btn-sm">Dispatch</button>
                                        </form>
                                    @endif
                                    @if($delivery->isOpen())
                                        <button type="button" class="btn btn-dark btn-sm" onclick="document.getElementById('deliver-{{ $delivery->id }}').classList.toggle('hidden')">Mark delivered</button>
                                        <form action="{{ route('projects.deliveries.status', [$project, $delivery]) }}" method="POST" onsubmit="return confirm('Mark this delivery as failed?');">
                                            @csrf
                                            <input type="hidden" name="status" value="Failed">
                                            <button type="submit" class="btn btn-ghost btn-sm">Failed</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('projects.deliveries.destroy', [$project, $delivery]) }}" method="POST" onsubmit="return confirm('Remove this delivery?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($delivery->isOpen())
                                <form id="deliver-{{ $delivery->id }}" action="{{ route('projects.deliveries.status', [$project, $delivery]) }}" method="POST" class="hidden">
                                    @csrf
                                    <input type="hidden" name="status" value="Delivered">
                                    <div class="mt-3 flex flex-col gap-2 rounded-lg bg-emerald-50 p-3 sm:flex-row sm:items-end">
                                        <div class="flex-1">
                                            <label class="label">Received by</label>
                                            <input type="text" name="received_by" class="input" placeholder="Name of person who received">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Confirm delivered</button>
                                    </div>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-12 text-center text-sm text-zinc-500">
                    No deliveries yet. Schedule one once the job is ready to ship.
                </div>
            @endif
        </div>

        <!-- Quality / reprints -->
        <div class="card mt-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Quality &amp; reprints</h2>
                    <p class="text-xs text-zinc-500">Log defects, reprints, returns, and complaints with rework cost.</p>
                </div>
                @if($project->openIssuesCount() > 0)
                    <span class="badge badge-red">{{ $project->openIssuesCount() }} open</span>
                @endif
            </div>

            <form action="{{ route('projects.issues.store', $project) }}" method="POST" class="grid grid-cols-1 gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label class="label">Type</label>
                    <select name="type" required class="select">
                        @foreach(\App\Models\ProjectIssue::TYPES as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Reason</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="input" placeholder="Misprint, wrong size…">
                </div>
                <div>
                    <label class="label">Date</label>
                    <input type="date" name="reported_at" value="{{ old('reported_at', now()->toDateString()) }}" required class="input">
                </div>
                <div>
                    <label class="label">Qty affected</label>
                    <input type="number" name="quantity_affected" min="0" step="1" value="{{ old('quantity_affected') }}" class="input">
                </div>
                <div>
                    <label class="label">Rework cost (₱)</label>
                    <input type="number" name="rework_cost" min="0" step="0.01" value="{{ old('rework_cost') }}" class="input">
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="label">Notes</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="input" placeholder="Optional detail">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex justify-end">
                    <button type="submit" class="btn btn-primary">Log issue</button>
                </div>
                @error('type')<p class="text-xs text-red-600 sm:col-span-2 lg:col-span-3">{{ $message }}</p>@enderror
            </form>

            @if($project->issues->count() > 0)
                <ul class="divide-y divide-zinc-100">
                    @foreach($project->issues as $issue)
                        <li class="flex flex-col gap-2 p-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge {{ $issue->getTypeBadgeClass() }}">{{ $issue->type }}</span>
                                    <span class="badge {{ $issue->getStatusBadgeClass() }}">{{ $issue->status }}</span>
                                    @if($issue->reason)<span class="text-sm font-medium text-zinc-900">{{ $issue->reason }}</span>@endif
                                </div>
                                @if($issue->description)<p class="mt-1 text-sm text-zinc-600">{{ $issue->description }}</p>@endif
                                <p class="mt-1 text-xs text-zinc-400">
                                    {{ $issue->reported_at?->format('M d, Y') }}
                                    @if($issue->quantity_affected > 0) · {{ $issue->quantity_affected }} pcs @endif
                                    @if((float) $issue->rework_cost > 0) · rework ₱{{ number_format($issue->rework_cost, 2) }} @endif
                                    @if($issue->reporter) · {{ $issue->reporter->name }} @endif
                                    @if($issue->resolved_at) · {{ $issue->status }} {{ $issue->resolved_at->format('M d') }} @endif
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if($issue->isOpen())
                                    <form action="{{ route('projects.issues.status', [$project, $issue]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="Resolved">
                                        <button type="submit" class="btn btn-primary btn-sm">Resolve</button>
                                    </form>
                                    @if($issue->status === 'Open')
                                        <form action="{{ route('projects.issues.status', [$project, $issue]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="In Progress">
                                            <button type="submit" class="btn btn-ghost btn-sm">Start</button>
                                        </form>
                                    @endif
                                @endif
                                <form action="{{ route('projects.issues.destroy', [$project, $issue]) }}" method="POST" onsubmit="return confirm('Remove this issue?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-12 text-center text-sm text-zinc-500">
                    No quality issues logged. That's a good thing.
                </div>
            @endif
        </div>

        <!-- Customer feedback -->
        <div class="card mt-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Customer feedback</h2>
                    <p class="text-xs text-zinc-500">Record the customer's rating and review after delivery.</p>
                </div>
                @if($project->feedback->isNotEmpty())
                    <span class="text-sm font-semibold text-amber-500">{{ $project->feedback->first()->stars() }}</span>
                @endif
            </div>

            <form action="{{ route('projects.feedback.store', $project) }}" method="POST" class="grid grid-cols-1 gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div>
                    <label class="label">Rating</label>
                    <select name="rating" required class="select">
                        <option value="">Select…</option>
                        @for($r = 5; $r >= 1; $r--)
                            <option value="{{ $r }}">{{ $r }} — {{ str_repeat('★', $r) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="label">Reviewer</label>
                    <input type="text" name="reviewer_name" value="{{ old('reviewer_name', $project->customer->name ?? $project->customer_name) }}" class="input">
                </div>
                <div>
                    <label class="label">Date</label>
                    <input type="date" name="submitted_at" value="{{ old('submitted_at', now()->toDateString()) }}" required class="input">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                        <input type="checkbox" name="would_recommend" value="1" checked class="rounded border-zinc-300">
                        Would recommend
                    </label>
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="label">Comment</label>
                    <input type="text" name="comment" value="{{ old('comment') }}" class="input" placeholder="What the customer said…">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit" class="btn btn-primary">Save feedback</button>
                </div>
                @error('rating')<p class="text-xs text-red-600 sm:col-span-2 lg:col-span-4">{{ $message }}</p>@enderror
            </form>

            @if($project->feedback->count() > 0)
                <ul class="divide-y divide-zinc-100">
                    @foreach($project->feedback as $fb)
                        <li class="flex items-start justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-amber-500">{{ $fb->stars() }}</span>
                                    <span class="text-sm font-medium text-zinc-900">{{ $fb->reviewer_name ?? 'Customer' }}</span>
                                    @if($fb->would_recommend)<span class="badge badge-green">Recommends</span>@endif
                                </div>
                                @if($fb->comment)<p class="mt-1 text-sm text-zinc-600">“{{ $fb->comment }}”</p>@endif
                                <p class="mt-1 text-xs text-zinc-400">{{ $fb->submitted_at?->format('M d, Y') }}</p>
                            </div>
                            <form action="{{ route('projects.feedback.destroy', [$project, $fb]) }}" method="POST" onsubmit="return confirm('Remove this feedback?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-12 text-center text-sm text-zinc-500">
                    No feedback yet.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
