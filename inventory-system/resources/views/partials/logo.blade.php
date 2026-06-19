@php $box = $box ?? 'h-10 w-10'; @endphp
@if (file_exists(public_path('images/logo.png')))
    <span class="flex {{ $box }} items-center justify-center overflow-hidden rounded-full bg-white p-0.5 ring-1 ring-black/5">
        <img src="{{ asset('images/logo.png') }}" alt="Imprint Customs" class="h-full w-full rounded-full object-contain">
    </span>
@else
    <div class="flex {{ $box }} items-center justify-center rounded-lg bg-brand-400">
        <svg class="h-6 w-6 text-zinc-900" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
        </svg>
    </div>
@endif
