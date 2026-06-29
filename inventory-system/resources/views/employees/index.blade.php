@extends('layouts.app')

@section('title', 'Employees')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Employees</h1>
    <p class="mt-1 text-sm text-zinc-500">Staff records, positions, and hourly rates. Rates auto-fill when logging project labor.</p>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, position, email…" class="input pl-9">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request('search'))
                <a href="{{ route('employees.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($employees->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Phone</th>
                        <th class="text-right">Hourly rate</th>
                        <th>Hired</th>
                        <th>Status</th>
                        <th class="text-right">Edit</th>
                    </tr>
                </thead>
                @foreach($employees as $employee)
                    <tbody x-data="{ editing: false }">
                        <tr>
                            <td class="font-medium text-zinc-900">
                                {{ $employee->name }}
                                <div class="text-xs text-zinc-400">{{ $employee->email }}</div>
                            </td>
                            <td class="text-zinc-700">{{ $employee->position ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $employee->phone ?? '—' }}</td>
                            <td class="text-right text-zinc-700">{{ $employee->hourly_rate !== null ? '₱' . number_format($employee->hourly_rate, 2) : '—' }}</td>
                            <td class="text-zinc-500">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                @php $badge = match($employee->employment_status) { 'Active' => 'badge-green', 'On Leave' => 'badge-amber', default => 'badge-zinc' }; @endphp
                                <span class="badge {{ $badge }}">{{ $employee->employment_status ?? 'Active' }}</span>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-ghost btn-sm" @click="editing = !editing">Edit</button>
                            </td>
                        </tr>
                        <tr x-show="editing" x-cloak>
                            <td colspan="7" class="bg-zinc-50">
                                <form action="{{ route('employees.update', $employee) }}" method="POST" class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="label">Position</label>
                                        <input type="text" name="position" value="{{ $employee->position }}" class="input">
                                    </div>
                                    <div>
                                        <label class="label">Phone</label>
                                        <input type="text" name="phone" value="{{ $employee->phone }}" class="input">
                                    </div>
                                    <div>
                                        <label class="label">Hourly rate (₱)</label>
                                        <input type="number" name="hourly_rate" min="0" step="0.01" value="{{ $employee->hourly_rate }}" class="input">
                                    </div>
                                    <div>
                                        <label class="label">Hire date</label>
                                        <input type="date" name="hire_date" value="{{ $employee->hire_date?->format('Y-m-d') }}" class="input">
                                    </div>
                                    <div>
                                        <label class="label">Status</label>
                                        <select name="employment_status" class="select">
                                            @foreach(\App\Http\Controllers\EmployeeController::STATUSES as $s)
                                                <option value="{{ $s }}" {{ ($employee->employment_status ?? 'Active') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2 lg:col-span-5 flex justify-end gap-2">
                                        <button type="button" class="btn btn-ghost" @click="editing = false">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No employees found</p>
            <p class="mt-1 text-sm text-zinc-500">Staff accounts created under Users appear here for HR details.</p>
        </div>
    @endif
</div>

@endsection
