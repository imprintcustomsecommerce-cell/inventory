@extends('layouts.app')

@section('title', 'Users')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Users</h1>
    <p class="mt-1 text-sm text-zinc-500">Manage who can access the system and what they can do.</p>
</div>

<!-- Add user -->
<div class="card mb-6 p-4">
    <form action="{{ route('users.store') }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @csrf
        <div>
            <label class="label">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="input">
        </div>
        <div>
            <label class="label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="input">
        </div>
        <div>
            <label class="label">Role</label>
            <select name="role" class="select">
                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div>
            <label class="label">Warehouse <span class="font-normal text-zinc-400">(staff only)</span></label>
            <select name="warehouse_id" class="select">
                <option value="">— none / all —</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Department</label>
            <select name="department" class="select">
                <option value="">General</option>
                <option value="materials" {{ old('department') === 'materials' ? 'selected' : '' }}>Materials only</option>
            </select>
        </div>
        <div>
            <label class="label">Password</label>
            <input type="password" name="password" required class="input">
        </div>
        <div>
            <label class="label">Confirm</label>
            <input type="password" name="password_confirmation" required class="input">
        </div>
        <div class="sm:col-span-3">
            <button type="submit" class="btn btn-primary">Create user</button>
        </div>
    </form>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Warehouse</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <span class="font-medium text-zinc-900">{{ $user->name }}</span>
                            @if($user->id === auth()->id())<span class="badge badge-zinc">You</span>@endif
                        </div>
                    </td>
                    <td class="text-zinc-500">{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->isAdmin() ? 'badge-amber' : 'badge-zinc' }}">{{ ucfirst($user->role) }}</span>
                        @if($user->department === 'materials')<span class="badge badge-green">Materials</span>@endif
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span class="text-xs text-zinc-400">All warehouses</span>
                        @else
                            <span class="badge badge-zinc">{{ $user->warehouse?->name ?? 'Unassigned' }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('users.update', $user) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="role" class="select py-1.5 text-xs" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <select name="warehouse_id" class="select py-1.5 text-xs">
                                    <option value="">— warehouse —</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}" {{ $user->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                                @if($user->id !== auth()->id())
                                    <button type="submit" class="btn btn-ghost btn-sm">Save</button>
                                @endif
                            </form>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
