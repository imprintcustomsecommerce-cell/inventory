<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in · Imprint Inventory</title>
  @vite(['resources/css/app.css'])
  <style>
    body {
      background: radial-gradient(circle at 15% 15%, #3a2c12 0%, #0a0a0a 45%),
                  radial-gradient(circle at 85% 85%, #241c08 0%, #0a0a0a 55%),
                  #0a0a0a;
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
    .visual-panel {
      background: linear-gradient(160deg, #3d2f10 0%, #171308 45%, #0a0a0a 100%);
    }
  </style>
</head>
<body class="h-full antialiased text-white">
<div class="relative flex min-h-full items-center justify-center overflow-hidden p-4 sm:p-8">

  <!-- ambient background -->
  <div class="pointer-events-none absolute inset-0 grid-pattern opacity-30"></div>

  <!-- floating card -->
  <div class="relative z-10 flex w-full max-w-6xl overflow-hidden rounded-[2.5rem] border border-yellow-500/10 bg-[#0f0f0f] p-3 shadow-2xl shadow-black/70">

    <!-- left panel — sign in form -->
    <div class="flex w-full flex-col justify-center px-8 py-10 sm:px-14 lg:w-1/2 lg:px-16">

      <!-- mobile logo -->
      <div class="mb-8 flex items-center gap-3 lg:hidden">
        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-black/40 ring-1 ring-yellow-500/20">
          <img src="{{ asset('logoic.png') }}" alt="Imprint Inventory Logo" class="h-10 w-10 object-contain" />
        </div>
        <div>
          <p class="font-bold text-white">Imprint Inventory</p>
          <p class="text-[10px] text-zinc-500">Inventory Management System</p>
        </div>
      </div>

      <h1 class="text-4xl font-black tracking-tight text-white sm:text-5xl">
        Welcome back!
      </h1>
      <p class="mt-4 text-sm font-semibold text-zinc-400">
        Sign in to manage your inventory dashboard.
      </p>

      <!-- errors -->
      @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-yellow-400/30 bg-yellow-400/5 px-4 py-3 text-sm font-medium text-yellow-200">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
        @csrf

        <!-- email -->
        <input
          id="email"
          type="email"
          name="email"
          value="{{ old('email') }}"
          required
          autofocus
          placeholder="Email"
          class="w-full rounded-2xl input-dark px-5 py-4 text-sm font-medium outline-none transition input-focus-ring @error('email') border-yellow-400/50 @enderror"
        >

        <!-- password -->
        <div class="relative">
          <input
            id="password"
            type="password"
            name="password"
            required
            placeholder="Password"
            class="w-full rounded-2xl input-dark px-5 py-4 pr-12 text-sm font-medium outline-none transition input-focus-ring @error('password') border-yellow-400/50 @enderror"
          >
          <button
            type="button"
            onclick="const p = document.getElementById('password'); const isPw = p.type === 'password'; p.type = isPw ? 'text' : 'password'; this.querySelector('svg').innerHTML = isPw
              ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88\'/>'
              : '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/>';"
            class="absolute inset-y-0 right-0 flex items-center pr-4 text-zinc-500 hover:text-zinc-300"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </button>
        </div>

        <!-- remember + meta -->
        <div class="flex items-center justify-between pt-1">
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
          class="btn-yellow group relative flex w-full items-center justify-center overflow-hidden rounded-2xl px-5 py-4 text-sm font-bold shadow-xl shadow-black/50 transition hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-yellow-400/30"
        >
          <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition duration-700 group-hover:translate-x-full"></span>
          <span class="relative">Sign In</span>
        </button>
      </form>

      @if (config('demo.enabled'))
        <!-- demo account picker — only on the public demo, never on the LAN install -->
        <div class="mt-8 rounded-2xl border border-yellow-500/20 bg-black/30 px-4 py-4">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-yellow-400">Demo accounts</p>
            <span class="text-xs text-zinc-500">password: <code class="text-zinc-400">{{ config('demo.password') }}</code></span>
          </div>
          <p class="mt-1 text-xs text-zinc-500">Pick a role to fill the form, then press Sign In.</p>

          <div class="mt-3 grid gap-1.5">
            @foreach (config('demo.accounts') as $account)
              <button
                type="button"
                title="{{ $account['blurb'] }}"
                data-demo-email="{{ $account['email'] }}"
                class="js-demo-account flex items-center justify-between gap-3 rounded-xl border border-yellow-500/10 bg-black/40 px-3 py-2 text-left transition hover:border-yellow-400/40 hover:bg-black/60 focus:outline-none focus:ring-2 focus:ring-yellow-400/30"
              >
                <span class="text-xs font-bold text-zinc-200">{{ $account['label'] }}</span>
                <span class="truncate text-xs text-zinc-500">{{ $account['email'] }}</span>
              </button>
            @endforeach
          </div>
        </div>

        <script>
          // Fill the form rather than submitting, so the credentials being used
          // are visible before sign-in.
          document.querySelectorAll('.js-demo-account').forEach(function (button) {
            button.addEventListener('click', function () {
              document.getElementById('email').value = button.dataset.demoEmail;
              document.getElementById('password').value = @json(config('demo.password'));
              document.getElementById('password').focus();
            });
          });
        </script>
      @else
        <!-- footer note -->
        <div class="mt-8 rounded-2xl border border-yellow-500/10 bg-black/30 px-4 py-3 text-center">
          <p class="text-xs text-zinc-500">
            Accounts are provisioned by an administrator.
          </p>
        </div>
      @endif
    </div>

    <!-- right panel — brand visual -->
    <div class="visual-panel relative hidden w-1/2 flex-col justify-between overflow-hidden rounded-[2rem] px-12 py-10 lg:flex">

      <!-- decorative background elements -->
      <div class="pointer-events-none absolute -left-24 top-20 h-72 w-72 rounded-full bg-yellow-400/10 blur-3xl"></div>
      <div class="pointer-events-none absolute bottom-10 right-0 h-96 w-96 rounded-full bg-yellow-500/10 blur-3xl"></div>
      <div class="pointer-events-none absolute right-10 top-12 text-[9rem] font-black leading-none tracking-tighter text-white/[0.04]">
        IMPRINT
      </div>

      <!-- logo -->
      <div class="relative z-10 flex items-center gap-5">
        <div class="rounded-3xl bg-black/40 p-3 ring-1 ring-yellow-500/20 shadow-lg shadow-black/60">
          <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl bg-white/5">
            <img src="{{ asset('logoic.png') }}" alt="Imprint Inventory Logo" class="h-16 w-16 object-contain" />
          </div>
        </div>
        <div>
          <p class="text-2xl font-black tracking-tight text-white">Imprint Customs</p>
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-yellow-400">
            Inventory Command Center
          </p>
        </div>
      </div>

      <!-- hero content -->
      <div class="relative z-10">
        <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-yellow-400/20 bg-yellow-400/5 px-5 py-2 text-[11px] font-semibold uppercase tracking-wider text-yellow-200">
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-yellow-400 opacity-60"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-yellow-300"></span>
          </span>
          live stock · store · production · events
        </div>

        <h2 class="max-w-lg text-4xl font-black leading-[1.05] tracking-tight text-white">
          Finally, all your
          <span class="block bg-gradient-to-r from-yellow-200 via-yellow-300 to-yellow-500 bg-clip-text text-transparent">
            work in one place.
          </span>
        </h2>

        <p class="mt-6 max-w-sm text-sm leading-7 text-zinc-400">
          A dedicated inventory system for managing Imprint Customs apparel, store stocks,
          event pull-outs, production output, and raw materials — all in one secure dashboard.
        </p>
      </div>

      <!-- footer -->
      <div class="relative z-10 flex items-center justify-between text-[11px] text-zinc-500">
        <p>© 2026 Imprint Customs</p>
        <p class="uppercase tracking-[0.25em]">Inventory Management System</p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
