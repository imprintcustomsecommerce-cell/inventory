@extends('shared.layouts.app')

@section('title', 'New Event')

@section('content')

<div class="mx-auto max-w-xl">
    <a href="{{ route('events.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to events
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">New event</h1>
        <p class="mt-1 text-sm text-zinc-500">Create the event, then pull stock into it via Transfer from Inventory.</p>
    </div>

    <form action="{{ route('events.store') }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            <div>
                <label class="label">Event name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Bazaar @ SM Megamall" class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Location <span class="font-normal text-zinc-400">(optional)</span></label>
                    <input type="text" name="location" value="{{ old('location') }}" class="input">
                </div>
                <div>
                    <label class="label">Event date <span class="font-normal text-zinc-400">(optional)</span></label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" class="input">
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('events.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create event</button>
        </div>
    </form>
</div>

@endsection
