@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Katalog Data Game
            </h2>
            <p class="text-xs text-slate-400 mt-1">Kelola judul game yang dilayani untuk pemesanan joki di EiraStore</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.games.create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Game Baru
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Judul Game</span>
                <p class="text-2xl font-black text-white mt-1">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-brand-blue/15 border border-brand-blue/30 flex items-center justify-center text-brand-cyan">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Game Aktif (Tampil)</span>
                <p class="text-2xl font-black text-emerald-400 mt-1">{{ $stats['aktif'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Layanan Joki</span>
                <p class="text-2xl font-black text-brand-cyan mt-1">{{ $stats['total_layanan'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-brand-cyan">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4">
        <form method="GET" action="{{ route('admin.games.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Cari nama game atau kategori..." 
                       class="w-full bg-brand-surface border border-brand-border rounded-xl pl-10 pr-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" 
                        onchange="this.form.submit()"
                        class="w-full bg-brand-surface border border-brand-border rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-brand-cyan">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Hanya Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Hanya Nonaktif</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-brand-surface hover:bg-brand-surface/80 border border-brand-border text-sm font-medium text-slate-200 transition-colors">
                Terapkan
            </button>
            @if(request('q') || request('status'))
                <a href="{{ route('admin.games.index') }}" class="px-4 py-2 rounded-xl bg-brand-surface/40 hover:bg-brand-surface border border-brand-border text-sm font-medium text-slate-400 hover:text-slate-200 text-center transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Games Table Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl overflow-hidden shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-brand-border bg-brand-surface/50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3.5 px-4 w-14 text-center">Gambar</th>
                        <th class="py-3.5 px-4">Nama Game & Kategori</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Deskripsi</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Layanan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border text-sm">
                    @forelse($games as $game)
                        <tr class="hover:bg-brand-surface/30 transition-colors">
                            <!-- Thumbnail Gambar -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-brand-surface border border-brand-border shrink-0 mx-auto">
                                    <img src="{{ $game->gambar_url }}" 
                                         alt="{{ $game->nama_game }}" 
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='https://placehold.co/120x120/0a1128/38bdf8?text=GAME';">
                                </div>
                            </td>

                            <!-- Nama Game & Kategori -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $game->nama_game }}</span>
                                @if($game->kategori)
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-md text-[10px] font-semibold bg-brand-blue/20 text-brand-cyan border border-brand-blue/30">
                                        {{ $game->kategori }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-500 italic">Tanpa kategori</span>
                                @endif
                            </td>

                            <!-- Deskripsi Singkat -->
                            <td class="py-3.5 px-4 text-slate-400 text-xs hidden md:table-cell max-w-xs truncate">
                                {{ $game->deskripsi ? Str::limit($game->deskripsi, 65) : '—' }}
                            </td>

                            <!-- Jumlah Layanan -->
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.layanan.index', ['game_id' => $game->id]) }}" 
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-surface border border-brand-border text-slate-200 hover:text-brand-cyan hover:border-brand-cyan/40 transition-colors">
                                    {{ $game->layanan_count }} Layanan
                                    <svg class="w-3.5 h-3.5 ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>

                            <!-- Status Aktif -->
                            <td class="py-3.5 px-4 text-center">
                                @if($game->aktif)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Kelola Layanan -->
                                    <a href="{{ route('admin.layanan.index', ['game_id' => $game->id]) }}" 
                                       title="Kelola Layanan Joki Game Ini"
                                       class="p-2 rounded-lg bg-brand-surface text-slate-300 hover:text-brand-cyan hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    </a>

                                    <!-- Tombol Ubah -->
                                    <a href="{{ route('admin.games.edit', $game) }}" 
                                       title="Ubah Data Game"
                                       class="p-2 rounded-lg bg-brand-surface text-amber-400 hover:text-amber-300 hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <!-- Tombol Hapus (Dialog Konfirmasi) -->
                                    <form method="POST" 
                                          action="{{ route('admin.games.destroy', $game) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data game {{ $game->nama_game }}? Game hanya dapat dihapus bila tidak memiliki layanan terkait.');"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus Game"
                                                class="p-2 rounded-lg bg-brand-surface text-red-400 hover:text-red-300 hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="max-w-sm mx-auto text-slate-400 space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-brand-surface border border-brand-border flex items-center justify-center mx-auto text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-300">Data game kosong</p>
                                    <p class="text-xs text-slate-500">Belum ada data game yang ditambahkan atau tidak sesuai filter pencarian.</p>
                                    <div class="pt-2">
                                        <a href="{{ route('admin.games.create') }}" class="inline-flex items-center text-xs font-semibold text-brand-cyan hover:underline">
                                            + Tambah Game Baru Sekarang
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($games->hasPages())
            <div class="p-4 border-t border-brand-border bg-brand-surface/20">
                {{ $games->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
