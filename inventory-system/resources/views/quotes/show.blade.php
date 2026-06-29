@extends('layouts.app')

@section('title', $quote->quote_number)

@section('content')

<a href="{{ route('quotes.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to quotes
</a>

<!-- Header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $quote->quote_number }}</h1>
            <span class="badge {{ $quote->getStatusBadgeClass() }}">{{ $quote->status }}</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $quote->title }} · {{ $quote->customer?->displayName() ?? 'No customer' }}
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($quote->status === 'Draft')
            <form action="{{ route('quotes.status', $quote) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="Sent">
                <button type="submit" class="btn btn-dark">Mark sent</button>
            </form>
        @endif
        @if(in_array($quote->status, ['Draft', 'Sent', 'Expired']))
            <form action="{{ route('quotes.status', $quote) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="Approved">
                <button type="submit" class="btn btn-primary">Mark approved</button>
            </form>
            <form action="{{ route('quotes.status', $quote) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="Rejected">
                <button type="submit" class="btn btn-ghost text-red-600 hover:bg-red-50">Reject</button>
            </form>
        @endif
        @if($quote->status === 'Approved' && !$quote->project_id)
            <form action="{{ route('quotes.convert', $quote) }}" method="POST"
                  onsubmit="return confirm('Convert this quote into a production project?');">
                @csrf
                <button type="submit" class="btn btn-primary">Convert to project</button>
            </form>
        @endif
        @if(in_array($quote->status, ['Approved', 'Converted']))
            <form action="{{ route('quotes.invoice', $quote) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-dark">Create invoice</button>
            </form>
        @endif
        <a href="{{ route('quotes.pdf', $quote) }}" target="_blank" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            PDF
        </a>
        @if($quote->isEditable())
            <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-ghost">Edit</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Details + totals -->
    <div class="space-y-6 lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Details</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Customer</dt>
                    <dd class="text-right text-zinc-900">
                        @if($quote->customer)
                            <a href="{{ route('customers.show', $quote->customer) }}" class="text-zinc-900 underline-offset-2 hover:underline">{{ $quote->customer->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Valid until</dt>
                    <dd class="{{ $quote->isExpired() ? 'font-medium text-red-600' : 'text-zinc-900' }}">{{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Prepared by</dt>
                    <dd class="text-zinc-900">{{ $quote->user?->name ?? '—' }}</dd>
                </div>
                @if($quote->project)
                    <div class="flex justify-between gap-4 border-t border-zinc-100 pt-3">
                        <dt class="text-zinc-500">Project</dt>
                        <dd><a href="{{ route('projects.show', $quote->project) }}" class="badge badge-green hover:opacity-80">{{ $quote->project->project_name }}</a></dd>
                    </div>
                @endif
            </dl>
            @if($quote->notes)
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">Notes</p>
                    <p class="mt-1 text-sm text-zinc-700">{{ $quote->notes }}</p>
                </div>
            @endif
            @if($quote->terms)
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">Terms</p>
                    <p class="mt-1 text-sm text-zinc-700">{{ $quote->terms }}</p>
                </div>
            @endif
        </div>

        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Totals</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Subtotal</dt>
                    <dd class="font-medium text-zinc-900">₱{{ number_format($quote->subtotal, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Discount @if($quote->promo_code)<span class="badge badge-green ml-1">{{ $quote->promo_code }}</span>@endif</dt>
                    <dd class="font-medium text-zinc-900">− ₱{{ number_format($quote->discount, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-zinc-100 pt-3">
                    <dt class="text-zinc-500">Total</dt>
                    <dd class="text-lg font-bold text-zinc-900">₱{{ number_format($quote->total, 2) }}</dd>
                </div>
            </dl>

            <!-- Promo code -->
            <div class="mt-4 border-t border-zinc-100 pt-4">
                @if($quote->promo_code)
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-zinc-600">Code <span class="font-semibold text-zinc-900">{{ $quote->promo_code }}</span> applied</p>
                        <form action="{{ route('quotes.promo.remove', $quote) }}" method="POST" onsubmit="return confirm('Remove the promo code?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Remove</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('quotes.promo.apply', $quote) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" required class="input uppercase" placeholder="Promo code">
                        <button type="submit" class="btn btn-dark">Apply</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Line items -->
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Line items</h2>
                    <p class="text-xs text-zinc-500">What this quote covers and how it's priced.</p>
                </div>
                @unless($quote->isEditable())
                    <span class="badge badge-green">Locked</span>
                @endunless
            </div>

            @if($quote->isEditable())
                <form action="{{ route('quotes.items.add', $quote) }}" method="POST" class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="label">Description</label>
                        <input type="text" name="description" required placeholder="e.g. Full-sublimation jersey w/ logo" class="input">
                    </div>
                    <div class="w-full sm:w-24">
                        <label class="label">Qty</label>
                        <input type="number" name="quantity" min="1" step="1" value="1" required class="input">
                    </div>
                    <div class="w-full sm:w-32">
                        <label class="label">Unit price</label>
                        <input type="number" name="unit_price" min="0" step="0.01" required class="input">
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            @endif

            @if($quote->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit price</th>
                                <th class="text-right">Line total</th>
                                @if($quote->isEditable())<th class="text-right">Remove</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $item)
                                <tr>
                                    <td class="font-medium text-zinc-900">{{ $item->description }}</td>
                                    <td class="text-right text-zinc-700">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                    <td class="text-right text-zinc-700">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right font-medium text-zinc-900">₱{{ number_format($item->total, 2) }}</td>
                                    @if($quote->isEditable())
                                        <td class="text-right">
                                            <form action="{{ route('quotes.items.remove', [$quote, $item]) }}" method="POST" onsubmit="return confirm('Remove this line item?');">
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
                    No line items yet. Add what this quote covers above.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
