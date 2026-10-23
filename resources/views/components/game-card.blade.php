@props([
    'game' => null,
    'nama' => 'Nama Game',
    'kategori' => 'Game',
    'developer' => 'Publisher',
    'thumbnail' => null,
    'href' => '#',
    'hargaMulai' => null,
])

@php
    $title = $game ? $game->nama_game : $nama;
    $cat = $game && isset($game->kategori) ? $game->kategori : $kategori;
    $dev = $game && isset($game->developer) ? $game->developer : $developer;
    $imageField = $game ? ($game->gambar ?? $game->thumbnail ?? null) : $thumbnail;
    $thumb = $imageField ? (str_starts_with($imageField, 'http') ? $imageField : asset('storage/' . $imageField)) : null;
    $link = $game ? (Route::has('game.show') ? route('game.show', $game->id) : url('/game/' . $game->id)) : $href;
@endphp

<a href="{{ $link }}" class="group relative flex flex-col bg-brand-card hover:bg-brand-cardHover rounded-2xl border border-brand-border hover:border-brand-blue/50 p-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-glow-blue overflow-hidden">
    
    <!-- Thumbnail Poster -->
    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-brand-surface">
        @if($thumb)
            <img src="{{ $thumb }}" alt="{{ $title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <!-- Fallback Poster Bertekstur -->
            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-brand-surface to-brand-card p-4 text-center">
                <div class="w-12 h-12 rounded-xl bg-brand-blue/20 text-brand-cyan flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M7 6h10a6 6 0 0 1 5.92 5.09l.86 4.74A3.5 3.5 0 0 1 20.34 20H18a3 3 0 0 1-2.83-2l-.5-1.5a1 1 0 0 0-.95-.68h-3.44a1 1 0 0 0-.95.68l-.5 1.5A3 3 0 0 1 6 20H3.66a3.5 3.5 0 0 1-3.44-4.17l.86-4.74A6 6 0 0 1 7 6z"/></svg>
                </div>
                <span class="text-xs font-bold text-slate-300 group-hover:text-white transition-colors">{{ $title }}</span>
            </div>
        @endif

        <!-- Kategori Badge -->
        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-brand-darkest/80 text-brand-cyan backdrop-blur-sm border border-brand-border">
            {{ $cat }}
        </span>
    </div>

    <!-- Informasi Game -->
    <div class="mt-3 flex flex-col flex-1 justify-between">
        <div>
            <h3 class="text-sm font-bold text-white group-hover:text-brand-cyan transition-colors line-clamp-1">
                {{ $title }}
            </h3>
            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">
                {{ $dev }}
            </p>
        </div>

        <div class="mt-3 pt-2.5 border-t border-brand-border/60 flex items-center justify-between">
            <span class="text-[11px] text-slate-400 font-medium">Layanan Joki</span>
            <span class="inline-flex items-center text-xs font-semibold text-brand-cyan group-hover:translate-x-0.5 transition-transform">
                Pesan
                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        </div>
    </div>
</a>
