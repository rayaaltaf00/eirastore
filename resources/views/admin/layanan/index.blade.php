@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Katalog Layanan Joki
            </h2>
            <p class="text-xs text-slate-400 mt-1">Kelola jenis paket joki, metode perhitungan harga, serta daftar item tingkatan</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.layanan.create', request()->only('game_id')) }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Layanan Baru
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Layanan Joki</span>
                <p class="text-2xl font-black text-white mt-1">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-brand-blue/15 border border-brand-blue/30 flex items-center justify-center text-brand-cyan">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Layanan Aktif</span>
                <p class="text-2xl font-black text-emerald-400 mt-1">{{ $stats['aktif'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Layanan Nonaktif</span>
                <p class="text-2xl font-black text-slate-400 mt-1">{{ $stats['nonaktif'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-700/20 border border-slate-700/40 flex items-center justify-center text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4">
        <form method="GET" action="{{ route('admin.layanan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Pencarian -->
            <div class="lg:col-span-2 relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Cari nama layanan atau game..." 
                       class="w-full bg-brand-surface border border-brand-border rounded-xl pl-10 pr-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
            </div>

            <!-- Filter Game -->
            <div>
                <select name="game_id" 
                        onchange="this.form.submit()"
                        class="w-full bg-brand-surface border border-brand-border rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-brand-cyan">
                    <option value="">Semua Game</option>
                    @foreach($games as $g)
                        <option value="{{ $g->id }}" {{ request('game_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nama_game }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tipe Harga -->
            <div>
                <select name="tipe_harga" 
                        onchange="this.form.submit()"
                        class="w-full bg-brand-surface border border-brand-border rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-brand-cyan">
                    <option value="">Semua Tipe Harga</option>
                    <option value="tangga" {{ request('tipe_harga') === 'tangga' ? 'selected' : '' }}>Tangga (Rank)</option>
                    <option value="progres" {{ request('tipe_harga') === 'progres' ? 'selected' : '' }}>Progres (Persen)</option>
                    <option value="pilihan" {{ request('tipe_harga') === 'pilihan' ? 'selected' : '' }}>Pilihan (Item)</option>
                </select>
            </div>

            <!-- Aksi Tombol -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-brand-surface hover:bg-brand-surface/80 border border-brand-border text-sm font-medium text-slate-200 text-center transition-colors">
                    Terapkan
                </button>
                @if(request()->anyFilled(['q', 'game_id', 'tipe_harga', 'status']))
                    <a href="{{ route('admin.layanan.index') }}" class="py-2 px-3 rounded-xl bg-brand-surface/40 hover:bg-brand-surface border border-brand-border text-sm font-medium text-slate-400 hover:text-slate-200 text-center transition-colors">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Layanan Table Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl overflow-hidden shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-brand-border bg-brand-surface/50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3.5 px-4 w-16 text-center">Poster</th>
                        <th class="py-3.5 px-4">Game</th>
                        <th class="py-3.5 px-4">Nama Layanan</th>
                        <th class="py-3.5 px-4 text-center">Tipe Perhitungan</th>
                        <th class="py-3.5 px-4 text-center">Item Layanan</th>
                        <th class="py-3.5 px-4 text-center">Estimasi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border text-sm">
                    @forelse($layananList as $layanan)
                        <tr class="hover:bg-brand-surface/30 transition-colors">
                            
                            <!-- Poster Layanan -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="w-12 h-14 rounded-xl overflow-hidden bg-brand-surface border border-brand-border shrink-0 mx-auto shadow-sm">
                                    <img src="{{ $layanan->gambar_url }}" 
                                         alt="{{ $layanan->nama_layanan }}" 
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.src='https://placehold.co/100x120/0a1128/38bdf8?text=JOKI';">
                                </div>
                            </td>

                            <!-- Game -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-lg overflow-hidden bg-brand-surface border border-brand-border shrink-0">
                                        <img src="{{ $layanan->game->gambar_url }}" 
                                             alt="{{ $layanan->game->nama_game }}" 
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='https://placehold.co/80x80/0a1128/38bdf8?text=GAME';">
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block">{{ $layanan->game->nama_game }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $layanan->game->kategori ?? 'Umum' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Nama Layanan & Deskripsi -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-white block">{{ $layanan->nama_layanan }}</span>
                                @if($layanan->deskripsi)
                                    <span class="text-xs text-slate-400 block truncate max-w-xs">{{ Str::limit($layanan->deskripsi, 60) }}</span>
                                @endif
                            </td>

                            <!-- Tipe Perhitungan Harga -->
                            <td class="py-3.5 px-4 text-center">
                                @if($layanan->tipe_harga->value === 'tangga')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        Tangga (Rank)
                                    </span>
                                @elseif($layanan->tipe_harga->value === 'progres')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        Progres (Persen)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Pilihan (Item)
                                    </span>
                                @endif
                            </td>

                            <!-- Item Layanan (Link ke daftar item) -->
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold {{ $layanan->items_count > 0 ? 'bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 hover:bg-brand-blue/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20' }} transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    {{ $layanan->items_count }} Item
                                </a>
                            </td>

                            <!-- Estimasi Waktu -->
                            <td class="py-3.5 px-4 text-center text-xs text-slate-300">
                                {{ $layanan->estimasi_waktu ?: '—' }}
                            </td>

                            <!-- Status Aktif -->
                            <td class="py-3.5 px-4 text-center">
                                @if($layanan->aktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Kelola Item -->
                                    <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
                                       title="Kelola Item Tingkatan / Harga"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-brand-surface text-brand-cyan hover:bg-brand-surface/80 border border-brand-border text-xs font-semibold transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                        Item
                                    </a>

                                    <!-- Tombol Ubah -->
                                    <a href="{{ route('admin.layanan.edit', $layanan) }}" 
                                       title="Ubah Layanan"
                                       class="p-2 rounded-lg bg-brand-surface text-amber-400 hover:text-amber-300 hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form method="POST" 
                                          action="{{ route('admin.layanan.destroy', $layanan) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan {{ $layanan->nama_layanan }} beserta seluruh item di dalamnya?');"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus Layanan"
                                                class="p-2 rounded-lg bg-brand-surface text-red-400 hover:text-red-300 hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-sm mx-auto text-slate-400 space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-brand-surface border border-brand-border flex items-center justify-center mx-auto text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-300">Data layanan joki kosong</p>
                                    <p class="text-xs text-slate-500">Belum ada layanan joki yang ditambahkan pada game ini atau tidak sesuai filter.</p>
                                    <div class="pt-2">
                                        <a href="{{ route('admin.layanan.create', request()->only('game_id')) }}" class="inline-flex items-center text-xs font-semibold text-brand-cyan hover:underline">
                                            + Tambah Layanan Baru Sekarang
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($layananList->hasPages())
            <div class="p-4 border-t border-brand-border bg-brand-surface/20">
                {{ $layananList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
