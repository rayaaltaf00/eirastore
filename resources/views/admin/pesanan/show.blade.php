{{--
    Halaman Detail & Manajemen Pesanan Administrator (Admin Order Control Room)
    - Verifikasi ganda: persetujuan data akun dan bukti pembayaran transfer pelanggan
    - Modal tindakan: penolakan bukti bayar, minta revisi data, penugasan worker, dan minta revisi hasil
    - Keamanan & Privasi: Kata sandi akun game disembunyikan total dari pandangan Admin
--}}
@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ 
    modalRevisiData: false, 
    modalTolakBayar: false,
    previewBuktiModal: false,
    modalRevisiHasil: false,
    modalNotifikasiSelesai: false,
    previewLaporanModal: null
}">

    <!-- Top Navigation & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-cyan transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.pesanan.index') }}" class="hover:text-brand-cyan transition-colors">Pesanan</a>
                <span>/</span>
                <span class="text-white font-medium">{{ $pesanan->kode_pesanan }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-white font-mono">{{ $pesanan->kode_pesanan }}</h1>
                <div x-data="{ copied: false }" class="relative inline-flex items-center">
                    <button type="button"
                            @click="copyToClipboard('{{ $pesanan->kode_pesanan }}', 'Kode Pesanan {{ $pesanan->kode_pesanan }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="p-1.5 rounded-xl bg-brand-surface border border-brand-border text-slate-400 hover:text-brand-cyan hover:border-brand-cyan/50 transition-all flex items-center gap-1 text-xs"
                            title="Salin Kode Pesanan">
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <svg x-show="copied" x-cloak class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-show="copied" x-cloak class="text-[10px] font-bold text-emerald-400">Tersalin!</span>
                    </button>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $pesanan->status->badgeColor() }}">
                    {{ $pesanan->status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Dipesan pada {{ $pesanan->created_at->format('d F Y, H:i') }} WIB • Pelanggan: <strong class="text-white">{{ $pesanan->customer->nama ?? 'Tamu' }}</strong></p>
        </div>

        <div class="flex items-center gap-3">
            @if($pesanan->no_wa_customer)
                @php
                    $waClean = preg_replace('/[^0-9]/', '', $pesanan->no_wa_customer);
                    if (str_starts_with($waClean, '0')) {
                        $waClean = '62' . substr($waClean, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $waClean }}?text=Halo%20kak%20{{ urlencode($pesanan->customer->nama ?? '') }},%20terkait%20pesanan%20joki%20{{ $pesanan->kode_pesanan }}%20di%20EiraStore:" 
                   target="_blank"
                   class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold hover:bg-emerald-500/25 transition-all">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Hubungi WhatsApp Pelanggan
                </a>
            @endif

            <a href="{{ route('admin.pesanan.index') }}" 
               class="px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-slate-300 hover:text-white text-xs font-semibold transition-colors">
                &larr; Kembali
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

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs space-y-1">
            <strong class="block text-sm">Terdapat kesalahan validasi:</strong>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- =============================================================== -->
    <!-- PIPELINE INDIKATOR STATUS VERIFIKASI (3 TAHAPAN JELAS)          -->
    <!-- =============================================================== -->
    <div class="p-5 rounded-2xl bg-brand-card/90 border border-brand-border shadow-card">
        <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-brand-border/60">
            <span class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Progres Verifikasi & Penugasan Pesanan
            </span>
            <span class="text-[11px] text-slate-400">Ikuti 3 tahapan verifikasi di bawah ini</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Tahap 1: Data Akun Game -->
            <div class="p-3 rounded-xl border flex items-center gap-3 {{ $pesanan->data_disetujui ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData ? 'bg-orange-500/10 border-orange-500/40 text-orange-300' : 'bg-brand-surface border-brand-border text-slate-300') }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shrink-0 {{ $pesanan->data_disetujui ? 'bg-emerald-500 text-white shadow-glow-emerald' : ($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData ? 'bg-orange-500 text-white' : 'bg-slate-700 text-white') }}">
                    {{ $pesanan->data_disetujui ? '✓' : '1' }}
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider block opacity-75">Tahap 1</span>
                    <strong class="text-xs font-bold block truncate">Data Akun Game</strong>
                    <span class="text-[10px] block opacity-90 truncate">
                        @if($pesanan->data_disetujui)
                            Sudah Disetujui
                        @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData)
                            Menunggu Revisi Customer
                        @else
                            Perlu Verifikasi Admin
                        @endif
                    </span>
                </div>
            </div>

            <!-- Tahap 2: Bukti Transfer -->
            @php
                $bayarOk = $pesanan->pembayaran && $pesanan->pembayaran->status === \App\Enums\StatusPembayaran::Disetujui;
                $bayarTolak = $pesanan->pembayaran && $pesanan->pembayaran->status === \App\Enums\StatusPembayaran::Ditolak;
                $bayarAda = (bool) $pesanan->pembayaran;
            @endphp
            <div class="p-3 rounded-xl border flex items-center gap-3 {{ $bayarOk ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($bayarTolak ? 'bg-rose-500/10 border-rose-500/40 text-rose-300' : ($bayarAda ? 'bg-amber-500/10 border-amber-500/40 text-amber-300' : 'bg-brand-surface border-brand-border text-slate-400')) }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shrink-0 {{ $bayarOk ? 'bg-emerald-500 text-white shadow-glow-emerald' : ($bayarTolak ? 'bg-rose-500 text-white' : ($bayarAda ? 'bg-amber-500 text-brand-bg font-black' : 'bg-slate-700 text-slate-400')) }}">
                    {{ $bayarOk ? '✓' : ($bayarTolak ? '✗' : '2') }}
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider block opacity-75">Tahap 2</span>
                    <strong class="text-xs font-bold block truncate">Bukti Pembayaran</strong>
                    <span class="text-[10px] block opacity-90 truncate">
                        @if($bayarOk)
                            Pembayaran Sah (Lunas)
                        @elseif($bayarTolak)
                            Pembayaran Ditolak
                        @elseif($bayarAda)
                            Perlu Cek Bukti Transfer
                        @else
                            Belum Ada Bukti Transfer
                        @endif
                    </span>
                </div>
            </div>

            <!-- Tahap 3: Penugasan Worker -->
            @php
                $workerOk = (bool) $pesanan->worker_id;
                $siapDitugaskan = $pesanan->status === \App\Enums\StatusPesanan::SiapDitugaskan;
            @endphp
            <div class="p-3 rounded-xl border flex items-center gap-3 {{ $workerOk ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : ($siapDitugaskan ? 'bg-brand-cyan/15 border-brand-cyan text-brand-cyan shadow-glow-blue' : 'bg-brand-surface border-brand-border text-slate-400') }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shrink-0 {{ $workerOk ? 'bg-emerald-500 text-white shadow-glow-emerald' : ($siapDitugaskan ? 'bg-brand-cyan text-brand-bg font-black animate-pulse' : 'bg-slate-700 text-slate-400') }}">
                    {{ $workerOk ? '✓' : '3' }}
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider block opacity-75">Tahap 3</span>
                    <strong class="text-xs font-bold block truncate">Penugasan Worker</strong>
                    <span class="text-[10px] block opacity-90 truncate">
                        @if($workerOk)
                            Ditugaskan ke: {{ $pesanan->worker->nama }}
                        @elseif($siapDitugaskan)
                            Siap Dipilihkan Worker!
                        @else
                            Terkunci (Selesaikan Tahap 1 & 2)
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- =============================================================== -->
    <!-- ALUR KHUSUS: BANNER SIAP DITUGASKAN KE WORKER                   -->
    <!-- =============================================================== -->
    @if($pesanan->status === \App\Enums\StatusPesanan::SiapDitugaskan)
        <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-900/40 via-brand-surface to-brand-card border-2 border-brand-cyan/40 shadow-glow-blue space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/40 flex items-center justify-center font-bold text-lg">
                    ⚡
                </span>
                <div>
                    <h2 class="text-base font-black text-white">Pesanan Siap Ditugaskan ke Worker</h2>
                    <p class="text-xs text-slate-300">Data akun dan bukti pembayaran telah diverifikasi penuh. Tentukan worker aktif yang bertugas memproses order ini.</p>
                </div>
            </div>

            @if($workersAktif->isEmpty())
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center justify-between gap-4">
                    <span>Belum ada Worker aktif yang tersedia di sistem saat ini.</span>
                    <a href="{{ route('admin.workers.index') }}" class="px-3 py-1.5 rounded-lg bg-amber-500 text-brand-bg font-bold text-xs hover:bg-amber-400 transition-colors">
                        Kelola Worker
                    </a>
                </div>
            @else
                <form action="{{ route('admin.pesanan.tugaskan-worker', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Menugaskan Worker..." class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    @csrf
                    <div class="flex-1">
                        <select name="worker_id" required class="w-full px-4 py-2.5 rounded-xl bg-brand-card border border-brand-border text-white text-xs font-semibold focus:border-brand-cyan focus:outline-none">
                            <option value="" disabled selected>-- Pilih Worker Aktif --</option>
                            @foreach($workersAktif as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->nama }} (Beban saat ini: {{ $w->jumlah_pesanan_aktif }} pesanan aktif • WA: {{ $w->no_hp ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-black shadow-glow-blue hover:opacity-95 transition-all text-center">
                        Tugaskan ke Worker & Mulai Pengerjaan &rarr;
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- Banner Laporan Masuk (A-27 & A-29) --}}
    @if($pesanan->status === \App\Enums\StatusPesanan::LaporanMasuk)
        @php
            $laporanTerbaru = $pesanan->laporanPenyelesaian->where('status', \App\Enums\StatusLaporan::MenungguVerifikasi)->sortByDesc('id')->first()
                           ?? $pesanan->laporanPenyelesaian->sortByDesc('id')->first();
        @endphp
        <div class="p-6 rounded-3xl bg-gradient-to-r from-teal-950/60 via-brand-surface to-brand-card border-2 border-teal-500/50 shadow-glow-blue space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-teal-500/20 text-teal-400 border border-teal-500/40 flex items-center justify-center font-bold text-xl shrink-0">
                        📋
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-500 text-brand-bg">Aksi Diperlukan</span>
                            <span class="text-xs text-slate-400">Worker: <strong class="text-white">{{ $pesanan->worker->nama ?? 'Worker' }}</strong></span>
                        </div>
                        <h2 class="text-lg font-black text-white mt-0.5">Laporan Hasil Pengerjaan Worker Masuk (A-27)</h2>
                        <p class="text-xs text-slate-300">Periksa bukti foto hasil jokian berikut. Jika sudah sesuai target, klik Verifikasi. Jika belum sesuai, minta Worker merevisi.</p>
                    </div>
                </div>
            </div>

            @if($laporanTerbaru)
                <div class="p-4 rounded-2xl bg-brand-card/90 border border-brand-border flex flex-col md:flex-row gap-4 items-start">
                    <button type="button" 
                            @click="previewLaporanModal = { url: '{{ route('admin.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $laporanTerbaru->id]) }}', title: 'Bukti Penyelesaian {{ $pesanan->kode_pesanan }}' }"
                            class="relative group rounded-xl overflow-hidden border border-brand-border w-32 h-32 shrink-0 bg-black/40 hover:border-brand-cyan transition-colors">
                        <img src="{{ route('admin.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $laporanTerbaru->id]) }}" 
                             alt="Bukti Laporan" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="px-2 py-1 rounded bg-black/80 text-[10px] text-white font-bold">Perbesar</span>
                        </div>
                    </button>
                    <div class="flex-1 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white">Laporan Waktu: {{ $laporanTerbaru->created_at->format('d F Y, H:i') }} WIB ({{ $laporanTerbaru->created_at->diffForHumans() }})</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $laporanTerbaru->status->badgeColor() }}">
                                {{ $laporanTerbaru->status->label() }}
                            </span>
                        </div>
                        @if($laporanTerbaru->catatan)
                            <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Catatan dari Worker:</span>
                                <p class="text-slate-200 leading-relaxed">{{ $laporanTerbaru->catatan }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <form action="{{ route('admin.pesanan.verifikasi-laporan', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Memverifikasi Laporan..." class="flex-1 w-full">
                    @csrf
                    <button type="submit" 
                            class="w-full py-3 px-5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:opacity-95 active:scale-[0.98] text-white text-xs font-black shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Verifikasi Laporan & Lanjutkan (A-27)
                    </button>
                </form>

                <button type="button" @click="modalRevisiHasil = true"
                        class="w-full sm:w-auto py-3 px-5 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 active:scale-[0.98] border border-rose-500/40 text-rose-300 text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Minta Revisi Hasil Pengerjaan (A-29)
                </button>
            </div>
        </div>
    @endif

    {{-- Banner Worker Selesai (A-28) --}}
    @if($pesanan->status === \App\Enums\StatusPesanan::WorkerSelesai)
        <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-950/60 via-brand-surface to-brand-card border-2 border-emerald-500/50 shadow-glow-emerald space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center font-bold text-xl shrink-0">
                    🎉
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500 text-brand-bg">Terverifikasi Sah</span>
                        <span class="text-xs text-slate-400">Pengerjaan Joki Selesai</span>
                    </div>
                    <h2 class="text-lg font-black text-white mt-0.5">Hasil Pengerjaan Terverifikasi! Teruskan ke Pelanggan (A-28)</h2>
                    <p class="text-xs text-slate-300">Hasil pengerjaan telah sah. Kirimkan pemberitahuan penyelesaian ke pelanggan agar dapat memeriksa game dan mengonfirmasi hasil.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <button type="button" @click="modalNotifikasiSelesai = true"
                        class="w-full sm:flex-1 py-3 px-5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan hover:opacity-95 active:scale-[0.98] text-white text-xs font-black shadow-glow-blue flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Kirim Notifikasi Penyelesaian ke Pelanggan (A-28) &rarr;
                </button>
            </div>
        </div>
    @endif

    {{-- Banner Dikomplain (A-29) --}}
    @if($pesanan->status === \App\Enums\StatusPesanan::Dikomplain)
        @php
            $riwayatKomplain = $pesanan->riwayatStatus->where('status_baru', \App\Enums\StatusPesanan::Dikomplain->value)->sortByDesc('id')->first();
        @endphp
        <div class="p-6 rounded-3xl bg-gradient-to-r from-rose-950/60 via-brand-surface to-brand-card border-2 border-rose-500/50 shadow-lg shadow-rose-500/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center font-bold text-xl shrink-0">
                    ⚠️
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white">Komplain Pelanggan</span>
                        <span class="text-xs text-slate-400">Perlu Tindakan Admin</span>
                    </div>
                    <h2 class="text-lg font-black text-white mt-0.5">Pelanggan Mengajukan Komplain Hasil Pengerjaan (C-12)</h2>
                    <p class="text-xs text-slate-300">Pelanggan melaporkan bahwa hasil jokian belum sesuai target. Berikan instruksi revisi ke Worker agar segera diperbaiki.</p>
                </div>
            </div>

            @if($riwayatKomplain && $riwayatKomplain->catatan)
                <div class="p-4 rounded-2xl bg-brand-surface border border-brand-border/60">
                    <span class="text-[10px] uppercase font-bold text-rose-400 block mb-1">Alasan Komplain dari Pelanggan:</span>
                    <p class="text-xs text-slate-200 leading-relaxed font-medium">{{ $riwayatKomplain->catatan }}</p>
                </div>
            @endif

            <div class="pt-2">
                <button type="button" @click="modalRevisiHasil = true"
                        class="w-full sm:w-auto py-3 px-6 rounded-xl bg-rose-500 hover:bg-rose-400 active:scale-[0.98] text-white text-xs font-black shadow-lg shadow-rose-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Instruksikan Revisi Hasil ke Worker (A-29) &rarr;
                </button>
            </div>
        </div>
    @endif

    {{-- Banner Perlu Revisi Hasil --}}
    @if($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
        <div class="p-5 rounded-2xl bg-orange-500/10 border border-orange-500/30 text-orange-300 text-xs flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-black">⏳</span>
                <div>
                    <strong class="text-white block font-bold">Sedang Menunggu Revisi dari Worker</strong>
                    <span>Instruksi perbaikan telah diteruskan ke worker <strong>{{ $pesanan->worker->nama ?? 'Worker' }}</strong>. Menunggu worker melapor ulang.</span>
                </div>
            </div>
            @if($pesanan->worker && $pesanan->worker->no_hp)
                @php
                    $workerWaRev = preg_replace('/[^0-9]/', '', $pesanan->worker->no_hp);
                    if (str_starts_with($workerWaRev, '0')) {
                        $workerWaRev = '62' . substr($workerWaRev, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $workerWaRev }}?text=Halo%20{{ urlencode($pesanan->worker->nama) }},%20mohon%20segera%20selesaikan%20revisi%20pesanan%20{{ $pesanan->kode_pesanan }}." 
                   target="_blank" class="px-3 py-1.5 rounded-lg bg-orange-500 text-brand-bg font-bold text-xs hover:bg-orange-400 shrink-0">
                    Ingatkan Worker di WA
                </a>
            @endif
        </div>
    @endif

    <!-- GRID UTAMA DUA KOLOM -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ======================================================= -->
        <!-- KOLOM KIRI (Cols 7): Verifikasi Data Akun & Bukti Bayar -->
        <!-- ======================================================= -->
        <div class="lg:col-span-7 space-y-6">

            <!-- KARTU 1: DATA AKUN GAME CUSTOMER (A-21, A-22) -->
            <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">Data Akun Game Customer</h2>
                    </div>

                    @if($pesanan->data_disetujui)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            ✓ Data Disetujui Admin
                        </span>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-orange-500/10 text-orange-400 border border-orange-500/30">
                            ⏳ Menunggu Revisi Customer
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                            Belum Diverifikasi
                        </span>
                    @endif
                </div>

                <!-- Detail Isian Akun Game -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60" x-data="{ copied: false }">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">ID Akun Game:</span>
                            <button type="button" @click="copyToClipboard('{{ $pesanan->id_akun_game }}', 'ID Akun Game'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="text-slate-400 hover:text-brand-cyan transition-colors" title="Salin ID Akun">
                                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                        <span class="font-bold text-white text-sm font-mono mt-0.5 block select-all">{{ $pesanan->id_akun_game }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Metode Login:</span>
                        <span class="font-bold text-white text-sm mt-0.5 block">{{ $pesanan->login_via ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60" x-data="{ copied: false }">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Username / Email Login:</span>
                            @if($pesanan->username_akun_game)
                                <button type="button" @click="copyToClipboard('{{ $pesanan->username_akun_game }}', 'Username Akun Game'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="text-slate-400 hover:text-brand-cyan transition-colors" title="Salin Username">
                                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endif
                        </div>
                        <span class="font-semibold text-white mt-0.5 block select-all">{{ $pesanan->username_akun_game ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Nickname Game:</span>
                        <span class="font-semibold text-white mt-0.5 block">{{ $pesanan->nickname_game ?? '-' }}</span>
                    </div>

                    @if($pesanan->server_game)
                        <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Server:</span>
                            <span class="font-semibold text-white mt-0.5 block">{{ $pesanan->server_game }}</span>
                        </div>
                    @endif

                    @if(!empty($pesanan->data_tambahan['zone_id']))
                        <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Zone ID:</span>
                            <span class="font-semibold text-white mt-0.5 block">{{ $pesanan->data_tambahan['zone_id'] }}</span>
                        </div>
                    @endif

                    <!-- PENTING: Mandat Keamanan PRD A-20 (Password TIDAK ditampilkan ke Admin) -->
                    <div class="sm:col-span-2 p-3 rounded-xl bg-slate-900/60 border border-slate-700/60 flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Password Akun Game Pelanggan:</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono text-xs text-amber-400 font-bold">••••••••••••••••</span>
                                <span class="text-[11px] text-slate-400 font-medium">(Dirahasiakan oleh Enkripsi AES-256)</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20 text-right">
                            Hanya Didekripsi untuk Worker
                        </span>
                    </div>
                </div>

                @if($pesanan->catatan_customer)
                    <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border/60 space-y-1">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Catatan Pelanggan:</span>
                        <p class="text-xs text-slate-200">{{ $pesanan->catatan_customer }}</p>
                    </div>
                @endif

                <!-- Status Persetujuan Data Akun -->
                @if($pesanan->data_disetujui)
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-base">✓</span>
                            <div>
                                <strong class="text-xs font-bold text-white block">Data Akun Game Telah Disetujui</strong>
                                <span class="text-[11px] text-slate-300">Format akun valid dan kredensial siap diproses oleh worker.</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">Terverifikasi</span>
                    </div>
                @endif

                <!-- Aksi Persetujuan / Permintaan Revisi Data -->
                @if($pesanan->status === \App\Enums\StatusPesanan::MenungguVerifikasi && ! $pesanan->data_disetujui)
                    <div class="pt-3 border-t border-brand-border flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <form action="{{ route('admin.pesanan.setujui-data', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Menyetujui Data..." class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-3 px-5 rounded-xl bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-white text-xs font-black shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Setujui Data Akun
                            </button>
                        </form>

                        <button type="button" @click="modalRevisiData = true"
                                class="py-3 px-5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 active:scale-[0.98] border border-amber-500/40 text-amber-300 text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Minta Revisi Data
                        </button>
                    </div>
                @endif
            </div>

            <!-- KARTU 2: BUKTI PEMBAYARAN & VERIFIKASI -->
            <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">Bukti Transfer & Pembayaran</h2>
                    </div>

                    @if($pesanan->pembayaran)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $pesanan->pembayaran->status->badgeColor() }}">
                            {{ $pesanan->pembayaran->status->label() }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-800 text-slate-400 border border-brand-border">
                            Belum Ada Bukti Transfer
                        </span>
                    @endif
                </div>

                @if($pesanan->pembayaran)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="space-y-3">
                            <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Metode Transfer:</span>
                                <span class="font-bold text-white text-sm mt-0.5 block">{{ $pesanan->pembayaran->metode_pembayaran }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Total Nominal Tagihan:</span>
                                <span class="font-black text-brand-cyan text-base mt-0.5 block">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Waktu Diunggah:</span>
                                <span class="font-semibold text-slate-200 mt-0.5 block">{{ $pesanan->pembayaran->created_at->format('d/m/Y H:i:s') }} WIB</span>
                            </div>

                            @if($pesanan->pembayaran->diverifikasi_at)
                                <div class="p-3 rounded-xl bg-brand-surface border border-brand-border/60">
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Diverifikasi Oleh:</span>
                                    <span class="font-semibold text-emerald-400 mt-0.5 block">{{ $pesanan->pembayaran->verifikator->nama ?? 'Admin' }} ({{ $pesanan->pembayaran->diverifikasi_at->format('d/m/Y H:i') }})</span>
                                </div>
                            @endif
                        </div>

                        <!-- Gambar Struk / Bukti Transfer -->
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-1.5">Berkas Struk Bukti Transfer:</span>
                            <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40">
                                <img src="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" 
                                     alt="Bukti Transfer {{ $pesanan->kode_pesanan }}"
                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform cursor-pointer"
                                     @click="previewBuktiModal = true">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity pointer-events-none">
                                    <span class="px-3 py-1.5 rounded-lg bg-brand-card/90 text-white text-xs font-bold border border-brand-border">
                                        Klik untuk Perbesar
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 flex justify-between items-center text-[11px]">
                                <a href="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" target="_blank" class="text-brand-cyan hover:underline font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Buka Tab Penuh
                                </a>
                                <span class="text-slate-500">Privat & Terenkripsi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Pembayaran Disetujui -->
                    @if($pesanan->pembayaran->status === \App\Enums\StatusPembayaran::Disetujui)
                        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-base">✓</span>
                                <div>
                                    <strong class="text-xs font-bold text-white block">Pembayaran Telah Diverifikasi Sah</strong>
                                    <span class="text-[11px] text-slate-300">Diverifikasi oleh {{ $pesanan->pembayaran->verifikator->nama ?? 'Admin' }}. Dana telah masuk ke rekening kas toko.</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">Lunas</span>
                        </div>
                    @endif

                    <!-- Tombol Aksi Verifikasi Pembayaran -->
                    @if($pesanan->status === \App\Enums\StatusPesanan::MenungguVerifikasi && $pesanan->pembayaran->status === \App\Enums\StatusPembayaran::MenungguVerifikasi)
                        <div class="pt-3 border-t border-brand-border flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <form action="{{ route('admin.pesanan.setujui-pembayaran', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Menyetujui Pembayaran..." class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-3 px-5 rounded-xl bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-white text-xs font-black shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Setujui Bukti Transfer
                                </button>
                            </form>

                            <button type="button" @click="modalTolakBayar = true"
                                    class="py-3 px-5 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 active:scale-[0.98] border border-rose-500/40 text-rose-300 text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak Bukti Transfer
                            </button>
                        </div>
                    @endif
                @else
                    <div class="py-6 text-center text-slate-400 bg-brand-surface/30 rounded-xl border border-brand-border/40">
                        <p class="text-xs">Pelanggan belum mengunggah struk bukti transfer untuk pesanan ini.</p>
                        <p class="text-[11px] text-slate-500 mt-1">Batas waktu bayar: {{ $pesanan->batas_waktu_bayar?->format('d/m/Y H:i') ?? '-' }} WIB</p>
                    </div>
                @endif
            </div>

            <!-- KARTU 3: WORKER YANG DITUGASKAN -->
            <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">Petugas Worker Jokian</h2>
                    </div>
                    @if($pesanan->worker)
                        <span class="text-xs font-semibold text-emerald-400">Worker Ditugaskan</span>
                    @else
                        <span class="text-xs text-slate-500">Belum Ada Worker</span>
                    @endif
                </div>

                @if($pesanan->worker)
                    <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface border border-brand-border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/40 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($pesanan->worker->nama, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">{{ $pesanan->worker->nama }}</h3>
                                <p class="text-xs text-slate-400">{{ $pesanan->worker->email }} • No HP: {{ $pesanan->worker->no_hp ?? '-' }}</p>
                            </div>
                        </div>

                        @if($pesanan->worker->no_hp)
                            @php
                                $workerWa = preg_replace('/[^0-9]/', '', $pesanan->worker->no_hp);
                                if (str_starts_with($workerWa, '0')) {
                                    $workerWa = '62' . substr($workerWa, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $workerWa }}?text=Halo%20{{ urlencode($pesanan->worker->nama) }},%20update%20pesanan%20joki%20{{ $pesanan->kode_pesanan }}:" 
                               target="_blank"
                               class="px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-bold hover:bg-emerald-500/20 transition-colors">
                                WhatsApp Worker
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-400">Pesanan ini belum memiliki worker yang ditugaskan. Worker dapat ditugaskan begitu pesanan mencapai status <strong>Siap Ditugaskan</strong>.</p>
                @endif
            </div>

            <!-- KARTU 3B: RIWAYAT BUKTI & LAPORAN PENYELESAIAN WORKER -->
            @if($pesanan->laporanPenyelesaian->isNotEmpty())
                <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-white">Bukti Laporan Pengerjaan Worker</h2>
                        </div>
                        <span class="text-xs text-slate-400 font-semibold">{{ $pesanan->laporanPenyelesaian->count() }} Laporan Diserahkan</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($pesanan->laporanPenyelesaian->sortByDesc('id') as $index => $lap)
                            <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border/60 flex flex-col sm:flex-row items-start gap-4">
                                <button type="button" 
                                        @click="previewLaporanModal = { url: '{{ route('admin.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $lap->id]) }}', title: 'Bukti #{{ $loop->iteration }} - {{ $pesanan->kode_pesanan }}' }"
                                        class="relative group rounded-xl overflow-hidden border border-brand-border w-24 h-24 shrink-0 bg-black/40 hover:border-brand-cyan transition-colors">
                                    <img src="{{ route('admin.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $lap->id]) }}" 
                                         alt="Bukti Laporan" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                        <span class="px-1.5 py-0.5 rounded bg-black/80 text-[9px] text-white font-bold">Perbesar</span>
                                    </div>
                                </button>
                                <div class="flex-1 space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-white">Laporan #{{ $pesanan->laporanPenyelesaian->count() - $loop->index }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lap->status->badgeColor() }}">
                                            {{ $lap->status->label() }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Diserahkan: <strong class="text-slate-300">{{ $lap->created_at->format('d/m/Y H:i') }} WIB</strong> ({{ $lap->created_at->diffForHumans() }})
                                    </div>
                                    @if($lap->catatan)
                                        <p class="text-xs text-slate-200 bg-brand-card p-2 rounded-lg border border-brand-border/40">{{ $lap->catatan }}</p>
                                    @endif
                                    @if($lap->diverifikasi_at)
                                        <div class="text-[10px] text-slate-500">
                                            Diverifikasi oleh: {{ $lap->verifikator->nama ?? 'Admin' }} pada {{ $lap->diverifikasi_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- ======================================================= -->
        <!-- KOLOM KANAN (Cols 5): Rincian Paket & Timeline Riwayat  -->
        <!-- ======================================================= -->
        <div class="lg:col-span-5 space-y-6">

            <!-- KARTU 4: RINCIAN PAKET & LAYANAN JOKI -->
            <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-brand-border">
                    <img src="{{ $pesanan->layanan->poster_url }}" alt="{{ $pesanan->layanan->nama_layanan }}" class="w-12 h-12 rounded-xl object-cover border border-brand-border shrink-0">
                    <div>
                        <span class="text-[10px] font-bold text-brand-cyan uppercase tracking-wider">{{ $pesanan->layanan->game->nama_game ?? '-' }}</span>
                        <h3 class="text-sm font-bold text-white">{{ $pesanan->layanan->nama_layanan }}</h3>
                        <span class="text-xs text-slate-400">Estimasi: {{ $pesanan->layanan->estimasi_waktu ?? '1 - 2 Hari' }}</span>
                    </div>
                </div>

                <!-- Rincian Item Jokian -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Item / Paket Jokian:</span>
                    @foreach($pesanan->pesananItems as $item)
                        <div class="p-2.5 rounded-xl bg-brand-surface border border-brand-border/60 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-white block">{{ $item->nama_item }}</span>
                                <span class="text-[10px] text-slate-400">({{ $item->peran }})</span>
                            </div>
                            <span class="font-bold text-brand-cyan">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Total Harga -->
                <div class="pt-3 border-t border-brand-border flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-slate-400">Total Harga Pesanan:</span>
                    <span class="text-xl font-black text-brand-cyan">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- KARTU 5: TIMELINE & AUDIT LOG PERUBAHAN STATUS -->
            <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Timeline Riwayat Pesanan
                    </h2>
                    <span class="text-[10px] text-slate-500 font-mono">{{ $pesanan->riwayatStatus->count() }} Peristiwa</span>
                </div>

                <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-brand-border">
                    @forelse($pesanan->riwayatStatus->sortByDesc('created_at') as $riwayat)
                        <div class="relative flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-brand-surface border-2 border-brand-cyan flex items-center justify-center shrink-0 z-10">
                                <span class="w-2 h-2 rounded-full bg-brand-cyan"></span>
                            </div>
                            <div class="space-y-1 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-white uppercase">{{ str_replace('_', ' ', $riwayat->status_baru) }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $riwayat->created_at->format('d/m H:i') }}</span>
                                </div>
                                @if($riwayat->catatan)
                                    <p class="text-xs text-slate-300 bg-brand-surface/60 p-2 rounded-lg border border-brand-border/40">{{ $riwayat->catatan }}</p>
                                @endif
                                <p class="text-[10px] text-slate-500">Oleh: {{ $riwayat->pengubah->nama ?? 'Sistem Otomatis' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic">Belum ada riwayat aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================= -->
    <!-- MODAL 1: MINTA REVISI DATA AKUN GAME                     -->
    <!-- ======================================================= -->
    <div x-show="modalRevisiData" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalRevisiData = false">
        <div class="bg-brand-card border border-brand-border rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-card"
             @click.away="modalRevisiData = false">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-amber-400">⚠️</span> Instruksi Revisi Data Akun Game
                </h3>
                <button type="button" @click="modalRevisiData = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-slate-300">
                Tuliskan instruksi atau alasan detail bagian data akun game apa yang perlu diperbaiki oleh pelanggan (misal: ID tidak ditemukan, server salah, atau Zone ID kurang lengkap).
            </p>

            <form action="{{ route('admin.pesanan.minta-revisi-data', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Mengirim Instruksi..." class="space-y-4">
                @csrf
                <div>
                    <label for="catatan_revisi" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Catatan Revisi untuk Pelanggan <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="catatan_revisi" name="catatan" rows="4" required minlength="5" maxlength="1000"
                              placeholder="Contoh: Akun game tidak dapat ditemukan pada server Asia. Mohon periksa kembali ID Game dan Server Anda."
                              class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none"></textarea>
                    <span class="text-[10px] text-slate-500">Minimal 5 karakter, maksimal 1000 karakter.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="modalRevisiData = false"
                            class="px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-brand-bg text-xs font-black transition-colors">
                        Kirim Permintaan Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 2: TOLAK BUKTI PEMBAYARAN                          -->
    <!-- ======================================================= -->
    <div x-show="modalTolakBayar" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalTolakBayar = false">
        <div class="bg-brand-card border border-brand-border rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-card"
             @click.away="modalTolakBayar = false">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                <h3 class="text-base font-bold text-rose-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Tolak Bukti Pembayaran
                </h3>
                <button type="button" @click="modalTolakBayar = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs leading-relaxed">
                <strong>Peringatan Kebijakan:</strong> Menolak bukti transfer akan <strong>membatalkan pesanan secara permanen</strong> dan <strong>menghapus password akun game secara seketika</strong> demi keamanan.
            </div>

            <form action="{{ route('admin.pesanan.tolak-pembayaran', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Menolak & Membatalkan..." class="space-y-4">
                @csrf
                <div>
                    <label for="alasan_tolak" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Alasan Penolakan Pembayaran <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="alasan_tolak" name="alasan" rows="3" required minlength="5" maxlength="1000"
                              placeholder="Contoh: Bukti transfer palsu / mutasi bank tidak sesuai / nominal tidak cocok."
                              class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-rose-400 focus:outline-none"></textarea>
                    <span class="text-[10px] text-slate-500">Minimal 5 karakter, maksimal 1000 karakter.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="modalTolakBayar = false"
                            class="px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold transition-colors">
                        Tolak & Batalkan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 3: PREVIEW BUKTI TRANSFER FULL-SCREEN             -->
    <!-- ======================================================= -->
    @if($pesanan->pembayaran)
        <div x-show="previewBuktiModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
             @keydown.escape.window="previewBuktiModal = false">
            <div class="relative max-w-2xl w-full bg-brand-card border border-brand-border rounded-2xl p-4 shadow-card"
                 @click.away="previewBuktiModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Struk Bukti Transfer: {{ $pesanan->kode_pesanan }}</h4>
                    <button type="button" @click="previewBuktiModal = false" class="text-slate-400 hover:text-white text-lg">&times;</button>
                </div>
                <div class="mt-3 max-h-[75vh] overflow-auto rounded-xl">
                    <img src="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" alt="Struk Transfer" class="w-full object-contain mx-auto">
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================= -->
    <!-- MODAL 4: MINTA REVISI HASIL PENGERJAAN WORKER (A-29)   -->
    <!-- ======================================================= -->
    <div x-show="modalRevisiHasil" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalRevisiHasil = false">
        <div class="bg-brand-card border border-brand-border rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-card"
             @click.away="modalRevisiHasil = false">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                <h3 class="text-base font-bold text-rose-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Minta Revisi Hasil Pengerjaan (A-29)
                </h3>
                <button type="button" @click="modalRevisiHasil = false" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <p class="text-xs text-slate-300">
                Tuliskan catatan detail untuk Worker <strong class="text-white">{{ $pesanan->worker->nama ?? 'Worker' }}</strong> mengenai bagian hasil jokian yang belum sesuai (misal: rank belum tercapai, bintang kurang, atau screenshot tidak jelas).
            </p>

            <form action="{{ route('admin.pesanan.minta-revisi-hasil', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Mengirim Perintah Revisi..." class="space-y-4">
                @csrf
                <div>
                    <label for="catatan_revisi_hasil" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Catatan Instruksi Revisi untuk Worker <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="catatan_revisi_hasil" name="catatan" rows="4" required minlength="5" maxlength="1000"
                              placeholder="Contoh: Rank masih di Mythic 15 bintang, pesanan memesan hingga Mythic 25 bintang. Mohon lanjutkan pengerjaan dan lampirkan bukti baru."
                              class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-rose-400 focus:outline-none"></textarea>
                    <span class="text-[10px] text-slate-500">Minimal 5 karakter, maksimal 1000 karakter.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="modalRevisiHasil = false"
                            class="px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold transition-colors">
                        Kirim Perintah Revisi ke Worker
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 5: KIRIM NOTIFIKASI PENYELESAIAN CUSTOMER (A-28) -->
    <!-- ======================================================= -->
    <div x-show="modalNotifikasiSelesai" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalNotifikasiSelesai = false">
        <div class="bg-brand-card border border-brand-border rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-card"
             @click.away="modalNotifikasiSelesai = false">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-brand-cyan">🚀</span> Kirim Notifikasi Penyelesaian (A-28)
                </h3>
                <button type="button" @click="modalNotifikasiSelesai = false" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <p class="text-xs text-slate-300">
                Status pesanan akan diubah menjadi <strong class="text-brand-cyan">Menunggu Konfirmasi</strong>. Customer akan dapat memeriksa hasil jokian di detail pesanan dan memiliki opsi untuk Menyetujui Hasil atau Mengajukan Komplain.
            </p>

            <form action="{{ route('admin.pesanan.kirim-notifikasi-selesai', $pesanan->kode_pesanan) }}" method="POST" data-loading data-loading-text="Mengirim Notifikasi..." class="space-y-4">
                @csrf
                <div>
                    <label for="pesan_singkat" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Pesan Singkat untuk Pelanggan <span class="text-slate-500 font-normal">(Opsional)</span>
                    </label>
                    <textarea id="pesan_singkat" name="pesan_singkat" rows="3" maxlength="1000"
                              placeholder="Contoh: Pesanan joki kamu sudah selesai dengan selamat! Silakan cek akun game kamu ya kak."
                              class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none"></textarea>
                    <span class="text-[10px] text-slate-500">Maksimal 1000 karakter.</span>
                </div>

                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between gap-3">
                    <div>
                        <strong class="block font-bold">Kirim juga ke WhatsApp Pelanggan?</strong>
                        <span class="text-[11px] text-emerald-400/80">Kirim tautan konfirmasi langsung ke nomor WA pelanggan.</span>
                    </div>
                    @if($pesanan->no_wa_customer)
                        @php
                            $waCleanCust = preg_replace('/[^0-9]/', '', $pesanan->no_wa_customer);
                            if (str_starts_with($waCleanCust, '0')) {
                                $waCleanCust = '62' . substr($waCleanCust, 1);
                            }
                            $waTeks = "Halo kak " . ($pesanan->customer->nama ?? '') . ", pesanan joki " . $pesanan->kode_pesanan . " di EiraStore telah SELESAI dikerjakan! Silakan cek hasil di dalam game dan konfirmasi pesanan kamu di: " . route('pesanan.show', $pesanan->kode_pesanan);
                        @endphp
                        <a href="https://wa.me/{{ $waCleanCust }}?text={{ urlencode($waTeks) }}" 
                           target="_blank"
                           class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs whitespace-nowrap shrink-0 transition-colors">
                            Buka WA
                        </a>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="modalNotifikasiSelesai = false"
                            class="px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-black shadow-glow-blue hover:opacity-95 transition-all">
                        Kirim & Perbarui Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 6: PREVIEW FULLSCREEN LAPORAN WORKER              -->
    <!-- ======================================================= -->
    <div x-show="previewLaporanModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
         @keydown.escape.window="previewLaporanModal = null">
        <div class="relative max-w-3xl w-full bg-brand-card border border-brand-border rounded-2xl p-5 shadow-card"
             @click.away="previewLaporanModal = null">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider" x-text="previewLaporanModal?.title ?? 'Bukti Penyelesaian'"></h4>
                <button type="button" @click="previewLaporanModal = null" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>
            <div class="mt-4 max-h-[75vh] overflow-auto rounded-xl flex items-center justify-center bg-black/40">
                <template x-if="previewLaporanModal">
                    <img :src="previewLaporanModal.url" :alt="previewLaporanModal.title" class="max-w-full max-h-[70vh] object-contain rounded-lg">
                </template>
            </div>
            <div class="mt-3 flex justify-between items-center text-xs">
                <span class="text-slate-400 text-[11px]">Screenshot pengerjaan disimpan di storage privat.</span>
                <template x-if="previewLaporanModal">
                    <a :href="previewLaporanModal.url" target="_blank" class="text-brand-cyan hover:underline font-semibold flex items-center gap-1">
                        Buka Tab Penuh &rarr;
                    </a>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
