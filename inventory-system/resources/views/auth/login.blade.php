<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · Imprint Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="flex min-h-full">

    <!-- Brand panel -->
    <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-zinc-900 p-12 lg:flex">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-400/10 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-brand-400/5 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-400">
                <svg class="h-6 w-6 text-zinc-900" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            </div>
            <span class="text-lg font-semibold text-white">Imprint Inventory</span>
        </div>

        <div class="relative">
            <h2 class="text-3xl font-bold leading-tight text-white">Stock control,<br><span class="text-brand-400">simplified.</span></h2>
            <p class="mt-4 max-w-sm text-zinc-400">Real-time stock levels, low-stock alerts, and a complete movement history — all in one clean dashboard.</p>
            <ul class="mt-8 space-y-3">
                @foreach(['Track every stock-in and stock-out', 'Instant low-stock & out-of-stock alerts', 'Full audit trail of adjustments'] as $feature)
                    <li class="flex items-center gap-3 text-sm text-zinc-300">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-400/20">
                            <svg class="h-3 w-3 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-zinc-600">© 2026 Imprint Customs. All rights reserved.</p>
    </div>

    <!-- Form panel -->
    <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-20">
        <div class="mx-auto w-full max-w-sm">
            <!-- Mobile logo -->
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-400">
                    <svg class="h-6 w-6 text-zinc-900" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                </div>
                <span class="text-lg font-semibold text-zinc-900">Imprint Inventory</span>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Welcome back</h1>
            <p class="mt-2 text-sm text-zinc-500">Sign in to your account to continue.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="label">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@company.com" class="input @error('email') input-error @enderror">
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="label mb-0">Password</label>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••" class="input mt-1.5 @error('password') input-error @enderror">
                </div>
                <label class="flex items-center gap-2 text-sm text-zinc-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-brand-500 accent-brand-400 focus:ring-brand-400">
                    Remember me for 30 days
                </label>
                <button type="submit" class="btn btn-primary w-full">Sign in</button>
            </form>

            <p class="mt-8 text-center text-sm text-zinc-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-zinc-900 hover:text-brand-600">Create one</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
