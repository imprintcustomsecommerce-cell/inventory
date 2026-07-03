@extends('layouts.app')

@section('title', 'Staff & Permissions')

@section('content')

@php
    $warehousesJson = $warehouses->map(fn ($w) => ['id' => $w->id, 'name' => $w->name])->values();
@endphp

<div x-data="staffManager()">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Staff &amp; permissions</h1>
            <p class="mt-1 text-sm text-zinc-500">Accounts, roles, and warehouse access.</p>
        </div>
        <button type="button" class="btn btn-primary" @click="openCreate()">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Staff
        </button>
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('staff.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="input pl-9">
            </div>
            <select name="role" class="select sm:w-48">
                <option value="">All roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ $roleLabels[$r] }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-dark">Filter</button>
                @if(request('search') || request('role'))
                    <a href="{{ route('staff.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>

        @if($staff->count() > 0)
            <div class="overflow-x-auto">
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
                        @foreach($staff as $member)
                            <tr>
                                <td class="font-medium text-zinc-900">{{ $member->name }}</td>
                                <td class="text-zinc-500">{{ $member->email }}</td>
                                <td><span class="badge {{ $member->isAdmin() ? 'badge-amber' : 'badge-zinc' }}">{{ $member->roleLabel() }}</span></td>
                                <td class="text-zinc-500">{{ $member->warehouse?->name ?? ($member->isAdmin() ? 'All' : '—') }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-ghost btn-sm"
                                        data-staff="{{ json_encode(['id' => $member->id, 'name' => $member->name, 'email' => $member->email, 'role' => $member->role, 'warehouse_id' => $member->warehouse_id]) }}"
                                        @click="openEdit($el.dataset.staff)">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($staff->hasPages())
                <div class="border-t border-zinc-200 px-5 py-3">{{ $staff->withQueryString()->links() }}</div>
            @endif
        @else
            <p class="px-6 py-12 text-center text-sm text-zinc-500">No staff accounts match.</p>
        @endif
    </div>

    <!-- Modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-zinc-900/50" @click="open = false"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl">
            <form :action="formAction" method="POST" class="divide-y divide-zinc-200">
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-lg font-semibold text-zinc-900" x-text="mode === 'edit' ? 'Edit staff' : 'New staff'"></h2>
                    <button type="button" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100" @click="open = false">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="label">Full name</label>
                            <input type="text" name="name" x-model="form.name" required class="input">
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input type="email" name="email" x-model="form.email" required class="input">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="label">Role</label>
                            <select name="role" x-model="form.role" required class="select">
                                @foreach($roles as $r)
                                    <option value="{{ $r }}">{{ $roleLabels[$r] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="form.role !== 'admin'">
                            <label class="label">Warehouse</label>
                            <select name="warehouse_id" x-model="form.warehouse_id" class="select">
                                <option value="">Select warehouse…</option>
                                <template x-for="w in warehouses" :key="w.id">
                                    <option :value="w.id" x-text="w.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-zinc-400">Non-admins can only access this warehouse.</p>
                        </div>
                    </div>
                    <div>
                        <label class="label">Password <span class="font-normal text-zinc-400" x-show="mode === 'edit'">(leave blank to keep current)</span></label>
                        <input type="password" name="password" x-model="form.password" :required="mode === 'create'" minlength="8" class="input" autocomplete="new-password">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 bg-zinc-50 px-6 py-4">
                    <div>
                        <template x-if="mode === 'edit'">
                            <button type="button" class="btn btn-ghost text-red-600 hover:bg-red-50" @click="destroy()">Delete</button>
                        </template>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="btn btn-ghost" @click="open = false">Cancel</button>
                        <button type="submit" class="btn btn-primary" x-text="mode === 'edit' ? 'Save changes' : 'Create account'"></button>
                    </div>
                </div>
            </form>

            <form x-ref="deleteForm" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
            </form>
        </div>
    </div>
</div>

<script>
    function staffManager() {
        return {
            open: false,
            mode: 'create',
            warehouses: @json($warehousesJson),
            form: { id: null, name: '', email: '', role: 'store', warehouse_id: '', password: '' },
            get formAction() {
                return this.mode === 'edit' ? `{{ url('staff') }}/${this.form.id}` : `{{ route('staff.store') }}`;
            },
            openCreate() {
                this.mode = 'create';
                this.form = { id: null, name: '', email: '', role: 'store', warehouse_id: '', password: '' };
                this.open = true;
            },
            openEdit(payload) {
                const data = typeof payload === 'string' ? JSON.parse(payload) : payload;
                this.mode = 'edit';
                this.form = { ...data, warehouse_id: data.warehouse_id ?? '', password: '' };
                this.open = true;
            },
            destroy() {
                if (!confirm('Delete this staff account?')) return;
                const f = this.$refs.deleteForm;
                f.action = `{{ url('staff') }}/${this.form.id}`;
                f.submit();
            },
        };
    }
</script>

@endsection
