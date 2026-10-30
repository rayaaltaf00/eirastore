@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ 
    previewBukti: null,
    modalBuktiOpen: false,
    openPreview(url, title) {
        this.previewBukti = { url, title };
        this.modalBuktiOpen = true;
    }
}">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-cyan transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-white font-medium">Laporan Worker</span>
            </nav>
            <h1 class="text-2xl font-black text-white">Laporan Penyelesaian Worker (A-26)</h1>
            <p class="text-xs text-slate-400 mt-1">Periksa screenshot hasil screenshot jokian dari Worker sebelum diteruskan ke pelanggan.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pesanan.index', ['filter' => 'tindakan']) }}" 
               class="px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-slate-300 hover:text-white text-xs font-semibold flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Antrean Tindakan Admin
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Tab Status & Pencarian -->
    <div class="p-4 rounded-2xl bg-brand-card/90 border border-brand-border shadow-card flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <!-- Tab Status -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('admin.laporan-worker.index', ['status' => 'menunggu_verifikasi', 'q' => request('q')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2 {{ $statusFilter === 'menunggu_verifikasi' ? 'bg-amber-500 text-brand-bg shadow-lg shadow-amber-500/20 font-black' : 'bg-brand-surface text-slate-300 hover:text-white border border-brand-border/60' }}">
                <span>Menunggu Verifikasi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'menunggu_verifikasi' ? 'bg-brand-bg text-amber-400' : 'bg-brand-card text-amber-400 font-bold' }}">
                    {{ $counts['menunggu_verifikasi'] }}
                </span>
            </a>

            <a href="{{ route('admin.laporan-worker.index', ['status' => 'terverifikasi', 'q' => request('q')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2 {{ $statusFilter === 'terverifikasi' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20 font-black' : 'bg-brand-surface text-slate-300 hover:text-white border border-brand-border/60' }}">
                <span>Terverifikasi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'terverifikasi' ? 'bg-emerald-700 text-white' : 'bg-brand-card text-slate-400' }}">
                    {{ $counts['terverifikasi'] }}
                </span>
            </a>

            <a href="{{ route('admin.laporan-worker.index', ['status' => 'ditolak', 'q' => request('q')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2 {{ $statusFilter === 'ditolak' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20 font-black' : 'bg-brand-surface text-slate-300 hover:text-white border border-brand-border/60' }}">
                <span>Ditolak / Revisi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'ditolak' ? 'bg-rose-700 text-white' : 'bg-brand-card text-slate-400' }}">
                    {{ $counts['ditolak'] }}
                </span>
            </a>

            <a href="{{ route('admin.laporan-worker.index', ['status' => 'semua', 'q' => request('q')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2 {{ $statusFilter === 'semua' ? 'bg-brand-blue text-white shadow-glow-blue font-black' : 'bg-brand-surface text-slate-300 hover:text-white border border-brand-border/60' }}">
                <span>Semua Laporan</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'semua' ? 'bg-brand-card text-brand-cyan' : 'bg-brand-card text-slate-400' }}">
                    {{ $counts['semua'] }}
                </span>
            </a>
        </div>

        <!-- Form Pencarian -->
        <form method="GET" action="{{ route('admin.laporan-worker.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <div class="relative w-full md:w-64">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode / worker / game..."
                       class="w-full pl-9 pr-3 py-2 rounded-xl bg-brand-surface border border-brand-border text-white text-xs placeholder-slate-500 focus:border-brand-cyan focus:outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-brand-blue hover:bg-brand-blue/80 text-white text-xs font-bold transition-colors">
                Cari
            </button>
            @if(request('q'))
                <a href="{{ route('admin.laporan-worker.index', ['status' => $statusFilter]) }}" class="px-2 py-2 text-xs text-slate-400 hover:text-white">Reset</a>
            @endif
        </form>
    </div>

    <!-- Tabel Daftar Laporan Worker -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl shadow-card overflow-hidden">
        @if($laporanList->isEmpty())
            <div class="py-16 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-brand-surface border border-brand-border text-slate-500 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white">Tidak ada laporan pengerjaan ditemukan</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    @if($statusFilter === 'menunggu_verifikasi')
                        Semua laporan dari worker telah selesai diperiksa. Belum ada antrean laporan baru yang menunggu verifikasi saat ini.
                    @else
                        Tidak ada riwayat laporan yang cocok dengan filter yang dipilih.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-brand-border bg-brand-surface/60 text-slate-400 uppercase text-[10px] tracking-wider font-bold">
                            <th class="py-3 px-4">Pesanan & Game</th>
                            <th class="py-3 px-4">Worker Bertugas</th>
                            <th class="py-3 px-4">Waktu Lapor</th>
                            <th class="py-3 px-4">Foto Bukti</th>
                            <th class="py-3 px-4">Status Laporan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/60">
                        @foreach($laporanList as $lap)
                            <tr class="hover:bg-brand-surface/40 transition-colors">
                                <!-- Pesanan & Game -->
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.pesanan.show', $lap->pesanan->kode_pesanan) }}" class="font-mono font-bold text-white text-xs hover:text-brand-cyan transition-colors">
                                                {{ $lap->pesanan->kode_pesanan }}
                                            </a>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lap->pesanan->status->badgeColor() }}">
                                                {{ $lap->pesanan->status->label() }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-400 text-[11px]">
                                            <span class="text-brand-cyan font-semibold">{{ $lap->pesanan->layanan->game->nama_game ?? '-' }}</span>
                                            <span>•</span>
                                            <span>{{ $lap->pesanan->layanan->nama_layanan ?? '-' }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500">
                                            Customer: <strong class="text-slate-300">{{ $lap->pesanan->customer->nama ?? 'Tamu' }}</strong>
                                        </div>
                                    </div>
                                </td>

                                <!-- Worker -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/40 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($lap->worker->nama ?? 'W', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="text-white block font-medium">{{ $lap->worker->nama ?? '-' }}</strong>
                                            @if($lap->worker && $lap->worker->no_hp)
                                                <span class="text-[11px] text-slate-400">{{ $lap->worker->no_hp }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Waktu Lapor -->
                                <td class="py-4 px-4 text-slate-300">
                                    <div>
                                        <span class="font-medium block">{{ $lap->created_at->format('d/m/Y H:i') }} WIB</span>
                                        <span class="text-[10px] text-slate-500">{{ $lap->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>

                                <!-- Foto Bukti & Catatan -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="openPreview('{{ route('admin.pesanan.bukti-selesai', [$lap->pesanan->kode_pesanan, $lap->id]) }}', 'Bukti {{ $lap->pesanan->kode_pesanan }} - {{ $lap->worker->nama ?? 'Worker' }}')"
                                                class="relative group rounded-xl overflow-hidden border border-brand-border w-14 h-14 shrink-0 bg-black/40 hover:border-brand-cyan transition-colors">
                                            <img src="{{ route('admin.pesanan.bukti-selesai', [$lap->pesanan->kode_pesanan, $lap->id]) }}" 
                                                 alt="Bukti Selesai" 
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </div>
                                        </button>
                                        @if($lap->catatan)
                                            <div class="max-w-xs">
                                                <span class="text-[10px] uppercase font-bold text-slate-500 block">Catatan:</span>
                                                <p class="text-[11px] text-slate-300 truncate" title="{{ $lap->catatan }}">{{ $lap->catatan }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Laporan -->
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $lap->status->badgeColor() }}">
                                            {{ $lap->status->label() }}
                                        </span>
                                        @if($lap->diverifikasi_at)
                                            <span class="text-[10px] text-slate-500 block">
                                                Oleh: {{ $lap->verifikator->nama ?? 'Admin' }} ({{ $lap->diverifikasi_at->format('d/m H:i') }})
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.pesanan.show', $lap->pesanan->kode_pesanan) }}" 
                                           class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold hover:opacity-95 shadow-glow-blue transition-all">
                                            Buka Pesanan &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($laporanList->hasPages())
                <div class="p-4 border-t border-brand-border">
                    {{ $laporanList->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Modal Lightbox Bukti Penyelesaian -->
    <div x-show="modalBuktiOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
         @keydown.escape.window="modalBuktiOpen = false">
        <div class="relative max-w-3xl w-full bg-brand-card border border-brand-border rounded-2xl p-5 shadow-card"
             @click.away="modalBuktiOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider" x-text="previewBukti?.title ?? 'Screenshot Bukti Selesai'"></h4>
                <button type="button" @click="modalBuktiOpen = false" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>
            <div class="mt-4 max-h-[75vh] overflow-auto rounded-xl flex items-center justify-center bg-black/40">
                <template x-if="previewBukti">
                    <img :src="previewBukti.url" :alt="previewBukti.title" class="max-w-full max-h-[70vh] object-contain rounded-lg">
                </template>
            </div>
            <div class="mt-3 flex justify-between items-center text-xs">
                <span class="text-slate-400 text-[11px]">Screenshot pengerjaan disimpan di storage privat.</span>
                <template x-if="previewBukti">
                    <a :href="previewBukti.url" target="_blank" class="text-brand-cyan hover:underline font-semibold flex items-center gap-1">
                        Buka Tab Penuh &rarr;
                    </a>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
