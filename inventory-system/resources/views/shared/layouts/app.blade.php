<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory') · Imprint</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        .sidebar-glow {
            background:
                radial-gradient(ellipse at 20% 0%, rgba(250, 204, 21, 0.08), transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(250, 204, 21, 0.04), transparent 50%);
        }

        .sidebar-grid {
            background-image:
                linear-gradient(rgba(250, 204, 21, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(250, 204, 21, 0.025) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .sidebar-noise {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.02'/%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="h-full bg-[#F8F7F4] antialiased text-zinc-900">
<div class="relative min-h-full lg:flex" x-data="{ open: false }">

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col overflow-hidden border-r border-zinc-800/50 bg-[#0C0C0C] px-4 py-6 transition-transform duration-300 ease-out lg:static lg:z-auto lg:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Sidebar backgrounds -->
        <div class="pointer-events-none absolute inset-0 sidebar-glow"></div>
        <div class="pointer-events-none absolute inset-0 sidebar-grid opacity-50"></div>
        <div class="pointer-events-none absolute inset-0 sidebar-noise"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/30"></div>

        <!-- Sidebar content -->
        <div class="relative z-10 flex h-full flex-col">

            <!-- Brand -->
            <div class="mb-7 flex items-center gap-3 rounded-2xl border border-yellow-500/15 bg-gradient-to-br from-yellow-400/[0.06] to-transparent px-3.5 py-3.5 shadow-lg shadow-black/20">
                @include('shared.partials.logo', ['box' => 'h-9 w-9'])

                <div class="leading-tight">
                    <p class="text-sm font-black tracking-tight text-white">Imprint</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-400">
                        Inventory
                    </p>
                </div>
            </div>

            <!-- Search -->
            <form action="{{ route('search.index') }}" method="GET" class="mb-6">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>

                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search inventory..."
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-900/60 py-2.5 pl-10 pr-3 text-sm text-zinc-200 placeholder:text-zinc-600 outline-none transition-all duration-200 focus:border-brand-500/50 focus:bg-zinc-900 focus:ring-4 focus:ring-brand-400/10"
                    >
                </div>
            </form>

            <!-- Nav: Load sidebar based on user role -->
            <div class="flex-1 overflow-y-auto pr-1 scrollbar-thin">
                @if(auth()->user()->isAdmin())
                    @include('shared.layouts.sidebars.admin')
                @elseif(auth()->user()->isStore())
                    @include('shared.layouts.sidebars.store')
                @elseif(auth()->user()->isInventory())
                    @include('shared.layouts.sidebars.inventory')
                @elseif(auth()->user()->isMaterialsStaff())
                    @include('shared.layouts.sidebars.materials')
                @elseif(auth()->user()->isEvents())
                    @include('shared.layouts.sidebars.events')
                @endif
            </div>

            <!-- User card -->
            <div class="mt-auto border-t border-zinc-800/80 pt-4">
                <div class="rounded-2xl border border-zinc-800/60 bg-zinc-900/40 p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-500 text-sm font-black text-zinc-900 shadow-lg shadow-brand-400/20">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>

                        <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1 leading-tight transition hover:opacity-80" title="Account settings">
                            <p class="truncate text-sm font-semibold text-white">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="truncate text-xs text-zinc-500">
                                {{ auth()->user()->email }}
                            </p>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" title="Sign out" class="rounded-xl p-2 text-zinc-500 transition-all duration-200 hover:bg-red-500/10 hover:text-red-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </aside>

    <!-- Mobile backdrop -->
    <div
        x-show="open"
        @click="open = false"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
    ></div>

    <!-- Main -->
    <div class="min-w-0 flex-1">

        <!-- Topbar mobile -->
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-zinc-200/60 bg-[#F8F7F4]/95 px-4 py-3.5 backdrop-blur-xl lg:hidden">
            <button @click="open = true" class="rounded-xl p-2 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <span class="font-black tracking-tight text-zinc-900">Imprint</span>
        </header>

        <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
            <div class="mx-auto max-w-7xl">

                @if (session('success'))
                    <div class="alert alert-success">
                        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>

                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        <svg class="h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>

                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error flex-col items-start gap-2">
                        <p class="font-semibold">Please fix the following:</p>

                        <ul class="list-inside list-disc space-y-0.5 text-sm font-normal">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

            </div>
        </main>
    </div>
</div>
</body>
</html>
