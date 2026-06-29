@extends('layouts.app')

@section('title', 'Promo Codes')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Promo Codes</h1>
    <p class="mt-1 text-sm text-zinc-500">Discount codes that can be applied to quotes.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">New code</h2>
            <form action="{{ route('promo-codes.store') }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="label">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="input uppercase" placeholder="WELCOME10">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Type</label>
                        <select name="type" class="select">
                            <option value="percent">Percent %</option>
                            <option value="fixed">Fixed ₱</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Value</label>
                        <input type="number" name="value" min="0" step="0.01" value="{{ old('value') }}" required class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Min. subtotal (₱, optional)</label>
                    <input type="number" name="min_subtotal" min="0" step="0.01" value="{{ old('min_subtotal') }}" class="input">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Max uses (optional)</label>
                        <input type="number" name="max_uses" min="1" step="1" value="{{ old('max_uses') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Expires (optional)</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Description (optional)</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="input">
                </div>
                <button type="submit" class="btn btn-primary w-full">Create code</button>
                @error('code')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            @if($codes->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Used</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($codes as $code)
                            <tr>
                                <td class="font-medium text-zinc-900">
                                    {{ $code->code }}
                                    @if($code->description)<div class="text-xs text-zinc-400">{{ $code->description }}</div>@endif
                                </td>
                                <td class="text-zinc-700">{{ $code->label() }}@if($code->min_subtotal)<div class="text-xs text-zinc-400">min ₱{{ number_format($code->min_subtotal, 0) }}</div>@endif</td>
                                <td class="text-zinc-700">{{ $code->used_count }}{{ $code->max_uses ? ' / ' . $code->max_uses : '' }}</td>
                                <td class="text-zinc-500">{{ $code->expires_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    @if($code->active)
                                        <span class="badge badge-green">Active</span>
                                    @else
                                        <span class="badge badge-zinc">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <form action="{{ route('promo-codes.toggle', $code) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm">{{ $code->active ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                        <form action="{{ route('promo-codes.destroy', $code) }}" method="POST" onsubmit="return confirm('Delete this code?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Delete">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-16 text-center">
                    <p class="text-sm font-medium text-zinc-900">No promo codes yet</p>
                    <p class="mt-1 text-sm text-zinc-500">Create one with the form on the left.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
