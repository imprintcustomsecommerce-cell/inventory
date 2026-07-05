@extends('shared.layouts.app')

@section('title', 'Sales Channels')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Sales Channels</h1>
    <p class="mt-1 text-sm text-zinc-500">Connect your marketplace shops and pull in online orders.</p>
</div>

@php $anyLive = $channels->contains(fn ($c) => $c->isLive()); @endphp
@unless($anyLive)
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <strong>No live channel yet — no orders will appear until one is authorized.</strong>
        Create a developer app with your Seller Centre account
        (<a href="https://open.shopee.com" target="_blank" class="font-semibold underline">open.shopee.com</a>,
        <a href="https://open.lazada.com" target="_blank" class="font-semibold underline">open.lazada.com</a>,
        <a href="https://partner.tiktokshop.com" target="_blank" class="font-semibold underline">partner.tiktokshop.com</a>),
        then paste its keys under <strong>Live API setup</strong> and click <strong>Authorize shop</strong>.
        Once authorized, real orders sync automatically every 15 minutes.
    </div>
@endunless

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($channels as $channel)
        <div class="card p-5" x-data="{ showKeys: false }">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-sm font-semibold {{ $channel->color() }}">{{ $channel->name }}</span>
                <div class="flex items-center gap-1.5">
                    @if($channel->isLive() && $channel->isConnected())
                        <span class="badge badge-green">Live</span>
                    @elseif($channel->isLive())
                        <span class="badge badge-amber">Paused</span>
                    @else
                        <span class="badge badge-zinc">Awaiting API keys</span>
                    @endif
                </div>
            </div>

            <p class="mt-3 text-sm text-zinc-500">Shop</p>
            <p class="text-sm font-medium text-zinc-900">
                {{ $channel->shop_name ?? '—' }}
                @if($channel->isLive())
                    <span class="text-xs text-zinc-400">· shop #{{ $channel->credentials['shop_id'] }}</span>
                @endif
            </p>

            <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div>
                    <dt class="text-zinc-500">Orders</dt>
                    <dd class="font-semibold text-zinc-900">{{ $channel->orders_count }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">New</dt>
                    <dd class="font-semibold {{ $channel->new_orders_count > 0 ? 'text-amber-600' : 'text-zinc-900' }}">{{ $channel->new_orders_count }}</dd>
                </div>
            </dl>

            <p class="mt-3 text-xs text-zinc-400">
                {{ $channel->last_synced_at ? 'Last synced ' . $channel->last_synced_at->diffForHumans() : 'Never synced' }}
            </p>

            @if($channel->isLive())
                <div class="mt-4 flex gap-2">
                    <form action="{{ route('channels.toggle', $channel) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="btn {{ $channel->isConnected() ? 'btn-ghost' : 'btn-primary' }} w-full">
                            {{ $channel->isConnected() ? 'Pause syncing' : 'Resume syncing' }}
                        </button>
                    </form>
                    @if($channel->isConnected())
                        <form action="{{ route('channels.sync', $channel) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-dark">Sync now</button>
                        </form>
                    @endif
                </div>
            @endif

            <!-- Live API setup -->
            <div class="mt-4 border-t border-zinc-100 pt-3">
                <button type="button" @click="showKeys = !showKeys"
                        class="flex w-full items-center justify-between text-xs font-semibold uppercase tracking-wider text-zinc-400 hover:text-zinc-600">
                    <span>Live API setup</span>
                    <svg class="h-4 w-4 transition-transform" :class="showKeys ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>

                <div x-show="showKeys" x-cloak class="mt-3 space-y-3">
                    <form action="{{ route('channels.credentials', $channel) }}" method="POST" class="space-y-2">
                        @csrf
                        @if($channel->platform === 'shopee')
                            <div>
                                <label class="label text-xs">Partner ID</label>
                                <input type="text" name="partner_id" class="input py-2 text-sm"
                                       value="{{ $channel->credentials['partner_id'] ?? '' }}" placeholder="e.g. 2007xxx">
                            </div>
                            <div>
                                <label class="label text-xs">Partner Key</label>
                                <input type="password" name="partner_key" class="input py-2 text-sm"
                                       value="{{ $channel->credentials['partner_key'] ?? '' }}" placeholder="shpk...">
                            </div>
                        @else
                            <div>
                                <label class="label text-xs">App Key</label>
                                <input type="text" name="app_key" class="input py-2 text-sm"
                                       value="{{ $channel->credentials['app_key'] ?? '' }}" placeholder="from {{ $channel->platform === 'lazada' ? 'open.lazada.com' : 'partner.tiktokshop.com' }}">
                            </div>
                            <div>
                                <label class="label text-xs">App Secret</label>
                                <input type="password" name="app_secret" class="input py-2 text-sm"
                                       value="{{ $channel->credentials['app_secret'] ?? '' }}" placeholder="app secret">
                            </div>
                        @endif
                        <button type="submit" class="btn btn-ghost btn-sm w-full">Save keys</button>
                    </form>

                    @if($channel->hasApiCredentials())
                        <a href="{{ route('channels.authorize', $channel) }}" class="btn btn-primary btn-sm w-full">
                            {{ $channel->isLive() ? 'Re-authorize shop' : 'Authorize shop' }}
                        </a>
                    @endif

                    @if($channel->isLive())
                        <p class="text-xs text-emerald-600">✓ Live — syncing pulls real {{ $channel->name }} orders every 15 minutes.</p>
                    @endif

                    @if($channel->platform === 'tiktok')
                        <p class="text-xs text-zinc-400">TikTok: set the redirect URL in Partner Center to
                            <code class="text-[10px]">{{ route('channels.callback', $channel) }}</code></p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
