@extends('shared.layouts.app')

@section('title', 'Edit Project')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('projects.show', $project) }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to project
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Edit project</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $project->project_name }}</p>
    </div>

    <form action="{{ route('projects.update', $project) }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        @include('warehouse.projects.partials.form-fields')
        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>

    @if(auth()->user()->isAdmin())
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-6">
        <h3 class="text-sm font-semibold text-red-900">Delete this project</h3>
        <p class="mt-1 text-sm text-red-700">Permanently removes the project and its material list. This cannot be undone.</p>
        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete “{{ $project->project_name }}”? This cannot be undone.');" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete project</button>
        </form>
    </div>
    @endif
</div>

@endsection
