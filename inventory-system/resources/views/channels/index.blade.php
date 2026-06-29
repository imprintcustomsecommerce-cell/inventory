@extends('layouts.app')

@section('title', 'Sales Channels')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Sales Channels</h1>
    <p class="mt-1 text-sm text-zinc-500">Connect your marketplace shops and pull in online orders.</p>
</div>

<div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
    <strong>Mock mode.</strong> Connecting and syncing here is simulated for now — no live marketplace credentials are used. Wire up real API keys later to go live.
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($channels as $channel)
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-sm font-semibold {{ $channel->color() }}">{{ $channel->name }}</span>
                @if($channel->isConnected())
                    <span class="badge badge-green">Connected</span>
                @else
                    <span class="badge badge-zinc">Disconnected</span>
                @endif
            </div>

            <p class="mt-3 text-sm text-zinc-500">Shop</p>
            <p class="text-sm font-medium text-zinc-900">{{ $channel->shop_name ?? '—' }}</p>

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

            <div class="mt-4 flex gap-2">
                <form action="{{ route('channels.toggle', $channel) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="btn {{ $channel->isConnected() ? 'btn-ghost' : 'btn-primary' }} w-full">
                        {{ $channel->isConnected() ? 'Disconnect' : 'Connect' }}
                    </button>
                </form>
                @if($channel->isConnected())
                    <form action="{{ route('channels.sync', $channel) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-dark">Sync</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

@endsection
