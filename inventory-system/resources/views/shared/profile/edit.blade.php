@extends('shared.layouts.app')

@section('title', 'Account')

@section('content')

<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Account settings</h1>
        <p class="mt-1 text-sm text-zinc-500">Update your details and password.</p>
    </div>

    <!-- Profile info -->
    <form action="{{ route('profile.update') }}" method="POST" class="card mb-6 divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        <div class="space-y-5 p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-400 text-lg font-bold text-zinc-900">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">{{ $user->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                </div>
            </div>
            <div>
                <label class="label">Full name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Email address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="input @error('email') input-error @enderror">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex justify-end bg-zinc-50 px-6 py-4">
            <button type="submit" class="btn btn-primary">Save profile</button>
        </div>
    </form>

    <!-- Change password -->
    <form action="{{ route('profile.password') }}" method="POST" class="card divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        <div class="space-y-5 p-6">
            <div>
                <h2 class="text-sm font-semibold text-zinc-900">Change password</h2>
                <p class="text-xs text-zinc-500">Use at least 8 characters.</p>
            </div>
            <div>
                <label class="label">Current password</label>
                <input type="password" name="current_password" required class="input @error('current_password') input-error @enderror">
                @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">New password</label>
                    <input type="password" name="password" required class="input @error('password') input-error @enderror">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Confirm new password</label>
                    <input type="password" name="password_confirmation" required class="input">
                </div>
            </div>
        </div>
        <div class="flex justify-end bg-zinc-50 px-6 py-4">
            <button type="submit" class="btn btn-dark">Update password</button>
        </div>
    </form>
</div>

@endsection
