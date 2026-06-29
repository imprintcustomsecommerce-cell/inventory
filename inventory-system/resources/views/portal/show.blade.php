<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->project_name }} · Imprint Customs</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-zinc-100">

<div class="mx-auto max-w-2xl px-4 py-10">
    <!-- Brand -->
    <div class="mb-8 flex items-center gap-3">
        @include('partials.logo', ['box' => 'h-10 w-10'])
        <div>
            <p class="text-base font-bold text-zinc-900">Imprint Customs</p>
            <p class="text-xs text-zinc-500">Order portal</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <!-- Header -->
    <div class="card p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900">{{ $project->project_name }}</h1>
                <p class="mt-1 text-sm text-zinc-500">{{ $project->product_type }} · Qty {{ $project->quantity }}</p>
            </div>
            <span class="badge {{ $project->getStatusBadgeClass() }}">{{ $project->status }}</span>
        </div>
        @if($project->due_date)
            <p class="mt-3 text-sm text-zinc-500">Target date: <span class="font-medium text-zinc-700">{{ $project->due_date->format('M d, Y') }}</span></p>
        @endif
    </div>

    <!-- Proofs -->
    <div class="mt-6">
        <h2 class="mb-3 text-sm font-semibold text-zinc-900">Design proofs</h2>

        @forelse($project->proofs as $proof)
            <div class="card mb-4 p-5">
                <div class="flex items-start gap-4">
                    @if($proof->isImage())
                        <a href="{{ $proof->url() }}" target="_blank">
                            <img src="{{ $proof->url() }}" alt="Proof v{{ $proof->version }}" class="h-24 w-24 rounded-lg border border-zinc-200 object-cover">
                        </a>
                    @else
                        <a href="{{ $proof->url() }}" target="_blank" class="flex h-24 w-24 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 text-xs font-medium text-zinc-500">View file</a>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-zinc-900">Version {{ $proof->version }}</span>
                            <span class="badge {{ $proof->getStatusBadgeClass() }}">{{ $proof->status }}</span>
                        </div>
                        @if($proof->feedback)<p class="mt-1 text-sm text-zinc-600">{{ $proof->feedback }}</p>@endif
                        <p class="mt-1 text-xs text-zinc-400">Sent {{ $proof->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                @if($proof->isPending())
                    <div class="mt-4 flex flex-col gap-3 border-t border-zinc-100 pt-4">
                        <form action="{{ route('portal.proofs.approve', [$project->public_token, $proof]) }}" method="POST"
                              onsubmit="return confirm('Approve this design for production?');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full">Approve this design</button>
                        </form>
                        <details class="text-sm">
                            <summary class="cursor-pointer text-zinc-500 hover:text-zinc-900">Request a change instead</summary>
                            <form action="{{ route('portal.proofs.reject', [$project->public_token, $proof]) }}" method="POST" class="mt-2 flex flex-col gap-2">
                                @csrf
                                <textarea name="feedback" rows="2" required class="input" placeholder="What would you like changed?"></textarea>
                                <button type="submit" class="btn btn-dark">Send change request</button>
                            </form>
                        </details>
                    </div>
                @endif
            </div>
        @empty
            <div class="card px-6 py-10 text-center text-sm text-zinc-500">
                No proofs to review yet. We'll notify you when your design is ready.
            </div>
        @endforelse
    </div>

    <p class="mt-8 text-center text-xs text-zinc-400">Questions? Reply to the message that shared this link.</p>
</div>

</body>
</html>
