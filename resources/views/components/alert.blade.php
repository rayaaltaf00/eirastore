@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
    $styles = match ($type) {
        'success' => [
            'wrap' => 'bg-emerald-950/40 border-emerald-500/40 text-emerald-200',
            'icon' => 'text-emerald-400',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'error', 'danger' => [
            'wrap' => 'bg-red-950/40 border-red-500/40 text-red-200',
            'icon' => 'text-red-400',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'warning' => [
            'wrap' => 'bg-amber-950/40 border-amber-500/40 text-amber-200',
            'icon' => 'text-amber-400',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        ],
        default => [
            'wrap' => 'bg-blue-950/40 border-blue-500/40 text-blue-200',
            'icon' => 'text-brand-cyan',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
    };
@endphp

<div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" {{ $attributes->merge(['class' => "p-4 rounded-xl border flex items-start space-x-3 text-sm {$styles['wrap']}"]) }}>
    <div class="flex-shrink-0 {{ $styles['icon'] }} mt-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $styles['svg'] !!}
        </svg>
    </div>
    <div class="flex-1 font-medium">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button @click="show = false" type="button" class="flex-shrink-0 text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
