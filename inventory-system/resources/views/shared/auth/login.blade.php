<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in · Imprint Inventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: #0a0a0a;
    }
    .brand-glow {
      background: radial-gradient(circle at 20% 30%, rgba(255, 215, 0, 0.10), transparent 55%),
                  radial-gradient(circle at 80% 70%, rgba(255, 215, 0, 0.05), transparent 50%);
    }
    .grid-pattern {
      background-image: linear-gradient(rgba(255,215,0,0.03) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255,215,0,0.03) 1px, transparent 1px);
      background-size: 44px 44px;
    }
    .input-focus-ring:focus {
      box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.20);
      border-color: #fbbf24;
    }
    .btn-yellow {
      background: linear-gradient(145deg, #fbbf24, #f59e0b);
      border: 1px solid rgba(255, 215, 0, 0.3);
      color: #0a0a0a;
      font-weight: 700;
    }
    .btn-yellow:hover {
      background: linear-gradient(145deg, #fcd34d, #fbbf24);
      box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
    }
    .btn-yellow:focus {
      box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.3);
    }
    .feature-icon {
      background: rgba(255, 215, 0, 0.06);
      border: 1px solid rgba(255, 215, 0, 0.10);
    }
    .badge-yellow {
      background: rgba(255, 215, 0, 0.08);
      border: 1px solid rgba(255, 215, 0, 0.15);
    }
    .input-dark {
      background: rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(255, 215, 0, 0.12);
      color: #f5f5f5;
    }
    .input-dark:focus {
      border-color: #fbbf24;
      box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.12);
    }
    .input-dark::placeholder {
      color: #555;
    }
    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .logo-container img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }
  </style>
</head>
<body class="h-full antialiased text-white">
<div class="relative flex min-h-full overflow-hidden bg-[#0a0a0a]">

  <!-- background glow + grid -->
  <div class="pointer-events-none absolute inset-0 brand-glow"></div>
  <div class="pointer-events-none absolute inset-0 grid-pattern opacity-40"></div>

<!-- left panel – imprint brand -->
<div class="relative hidden w-1/2 flex-col justify-between overflow-hidden border-r border-yellow-500/10 bg-[#0a0a0a] px-12 py-10 lg:flex">

  <!-- decorative background elements -->
  <div class="pointer-events-none absolute -left-24 top-20 h-72 w-72 rounded-full bg-yellow-400/10 blur-3xl"></div>
  <div class="pointer-events-none absolute bottom-10 right-0 h-96 w-96 rounded-full bg-yellow-500/5 blur-3xl"></div>

  <div class="pointer-events-none absolute right-10 top-12 text-[9rem] font-black leading-none tracking-tighter text-white/[0.025]">
    IMPRINT
  </div>

  <div class="pointer-events-none absolute bottom-24 left-12 h-px w-72 bg-gradient-to-r from-yellow-400/40 via-yellow-400/10 to-transparent"></div>
  <div class="pointer-events-none absolute bottom-28 left-12 h-px w-48 bg-gradient-to-r from-yellow-400/20 via-yellow-400/5 to-transparent"></div>

  <!-- logo area -->
  <div class="relative z-10 flex items-center gap-4">
    <div class="rounded-2xl bg-black/50 p-2 ring-1 ring-yellow-500/20 shadow-lg shadow-black/60">
      <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white/5">
        <img src="{{ asset('logoic.png') }}" alt="Imprint Inventory Logo" class="h-11 w-11 object-contain" />
      </div>
    </div>

    <div>
      <p class="text-lg font-black tracking-tight text-white">Imprint Customs</p>
      <p class="text-[10px] font-semibold uppercase tracking-[0.35em] text-yellow-400">
        Inventory Command Center
      </p>
    </div>
  </div>

  <!-- hero content -->
  <div class="relative z-10 max-w-xl">
    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-yellow-400/20 bg-yellow-400/5 px-5 py-2 text-[11px] font-semibold uppercase tracking-wider text-yellow-200 backdrop-blur-sm">
      <span class="relative flex h-2 w-2">
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-yellow-400 opacity-60"></span>
        <span class="relative inline-flex h-2 w-2 rounded-full bg-yellow-300"></span>
      </span>
      live stock · store · production · events
    </div>

    <h2 class="max-w-lg text-6xl font-black leading-[0.95] tracking-tight text-white">
      Built for the way
      <span class="block bg-gradient-to-r from-yellow-200 via-yellow-300 to-yellow-500 bg-clip-text text-transparent">
        Imprint works.
      </span>
    </h2>

    <p class="mt-7 max-w-md text-sm leading-7 text-zinc-400">
      A dedicated inventory system for managing Imprint Customs apparel, store stocks,
      event pull-outs, production output, and raw materials in one secure dashboard.
    </p>

    <!-- brand line -->
    <div class="mt-9 flex flex-wrap gap-2 text-[10px] font-bold uppercase tracking-[0.22em] text-zinc-500">
      <span class="text-yellow-400">Custom Apparel</span>
      <span>/</span>
      <span>Store Stocks</span>
      <span>/</span>
      <span>Event Booths</span>
      <span>/</span>
      <span>Raw Materials</span>
    </div>

    <!-- statement block, not feature cards -->
    <div class="mt-12 border-l border-yellow-400/40 pl-6">
      <p class="text-xs font-bold uppercase tracking-[0.3em] text-yellow-400">
        Inventory with accountability
      </p>
      <p class="mt-3 max-w-sm text-lg font-semibold leading-8 text-white">
        Every item released, sold, pulled out, returned, or produced stays recorded.
      </p>
    </div>
  </div>

  <!-- footer -->
  <div class="relative z-10 flex items-center justify-between text-[11px] text-zinc-600">
    <p>© 2026 Imprint Customs</p>
    <p class="uppercase tracking-[0.25em]">Inventory Management System</p>
  </div>
</div>

  <!-- right panel – login -->
  <div class="relative z-10 flex w-full items-center justify-center px-6 py-10 lg:w-1/2 lg:px-16">

    <div class="w-full max-w-md">

      <!-- mobile logo -->
      <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
        <div class="rounded-2xl bg-black/40 p-2 ring-1 ring-yellow-500/20">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 overflow-hidden">
            <img src="{{ asset('logoic.png') }}" alt="Imprint Inventory Logo" class="h-9 w-9 object-contain" />
          </div>
        </div>
        <div>
          <p class="font-bold text-white">Imprint Inventory</p>
          <p class="text-[10px] text-zinc-500">Inventory Management System</p>
        </div>
      </div>

      <!-- main card -->
      <div class="rounded-[2.5rem] border border-yellow-500/10 bg-black/30 p-8 shadow-2xl shadow-black/70 backdrop-blur-xl sm:p-10">

        <div class="mb-8 text-center">
          <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-black/40 ring-1 ring-yellow-500/20 shadow-lg shadow-black/60">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/5 overflow-hidden">
              <img src="{{ asset('logoic.png') }}" alt="Imprint Inventory Logo" class="h-16 w-16 object-contain" />
            </div>
          </div>
          <h1 class="text-3xl font-black tracking-tight text-white">
            Welcome back
          </h1>
          <p class="mt-2 text-sm text-zinc-400">
            Sign in to manage your inventory dashboard.
          </p>
        </div>

        <!-- errors -->
        @if ($errors->any())
          <div class="mb-6 rounded-2xl border border-yellow-400/30 bg-yellow-400/5 px-4 py-3 text-sm font-medium text-yellow-200 backdrop-blur-sm">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
          @csrf

          <!-- email -->
          <div>
            <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-300">
              Email address
            </label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
              </div>
              <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                placeholder="you@company.com"
                class="w-full rounded-2xl input-dark py-3.5 pl-12 pr-4 text-sm font-medium outline-none transition input-focus-ring @error('email') border-yellow-400/50 @enderror"
              >
            </div>
          </div>

          <!-- password -->
          <div>
            <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-300">
              Password
            </label>
            <div class="relative">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0019.5 19.5v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
              </div>
              <input
                id="password"
                type="password"
                name="password"
                required
                placeholder="••••••••"
                class="w-full rounded-2xl input-dark py-3.5 pl-12 pr-4 text-sm font-medium outline-none transition input-focus-ring @error('password') border-yellow-400/50 @enderror"
              >
            </div>
          </div>

          <!-- remember + meta -->
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm font-medium text-zinc-400">
              <input
                type="checkbox"
                name="remember"
                class="h-4 w-4 rounded border-yellow-500/30 bg-black/60 text-yellow-400 accent-yellow-400 focus:ring-yellow-400/30 focus:ring-offset-0"
              >
              <span>Remember me</span>
            </label>
            <span class="text-xs text-zinc-500">30 days</span>
          </div>

          <!-- submit button -->
          <button
            type="submit"
            class="btn-yellow group relative flex w-full items-center justify-center overflow-hidden rounded-2xl px-5 py-3.5 text-sm font-bold shadow-xl shadow-black/50 transition hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-yellow-400/30"
          >
            <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition duration-700 group-hover:translate-x-full"></span>
            <span class="relative flex items-center gap-2">
              Sign in
              <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
              </svg>
            </span>
          </button>
        </form>

        <!-- footer note -->
        <div class="mt-8 rounded-2xl border border-yellow-500/10 bg-black/30 px-4 py-3 text-center backdrop-blur-sm">
          <p class="text-xs text-zinc-500">
            Accounts are provisioned by an administrator.
          </p>
        </div>
      </div>

      <p class="mt-6 text-center text-[11px] text-zinc-600 lg:hidden">
        © 2013 Imprint Customs. All rights reserved.
      </p>
    </div>
  </div>
</div>
</body>
</html>