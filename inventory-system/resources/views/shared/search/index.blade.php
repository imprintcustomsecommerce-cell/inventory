@extends('shared.layouts.app')

@section('title', 'Search')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Search</h1>
    @if($q !== '')
        <p class="mt-1 text-sm text-zinc-500">{{ $total }} {{ Str::plural('result', $total) }} for “<span class="font-medium text-zinc-700">{{ $q }}</span>”</p>
    @else
        <p class="mt-1 text-sm text-zinc-500">Search projects, customers, quotes, invoices, products, and online orders.</p>
    @endif
</div>

<form action="{{ route('search.index') }}" method="GET" class="mb-8">
    <div class="relative max-w-xl">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        <input type="search" name="q" value="{{ $q }}" autofocus placeholder="Type at least 2 characters…" class="input pl-10">
    </div>
</form>

@if($q !== '' && $total === 0)
    <div class="card px-6 py-16 text-center">
        <p class="text-sm font-medium text-zinc-900">No matches</p>
        <p class="mt-1 text-sm text-zinc-500">Try a different name, number, or keyword.</p>
    </div>
@else
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach($groups as $label => $items)
            @if($items->count() > 0)
                <div class="card overflow-hidden">
                    <div class="border-b border-zinc-200 px-5 py-3">
                        <h2 class="text-sm font-semibold text-zinc-900">{{ $label }} <span class="text-zinc-400">({{ $items->count() }})</span></h2>
                    </div>
                    <ul class="divide-y divide-zinc-100">
                        @foreach($items as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-zinc-50">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-zinc-900">{{ $item['label'] }}</p>
                                        @if($item['meta'])<p class="truncate text-xs text-zinc-500">{{ $item['meta'] }}</p>@endif
                                    </div>
                                    <svg class="h-4 w-4 shrink-0 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
@endif

@endsection
