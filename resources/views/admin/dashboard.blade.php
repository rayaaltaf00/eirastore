@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    
    <!-- Welcome & Highlight Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-card via-brand-surface to-brand-card border border-brand-border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-card">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-brand-blue/20 text-brand-cyan border border-brand-blue/30">
                    Dashboard Utama
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                    Sistem Aktif & Terhubung
                </span>
                <span class="text-xs text-slate-500">•</span>
                <span class="text-xs text-slate-400 font-medium">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h2 class="text-2xl font-black text-white mt-2">Selamat Datang, {{ Auth::user()->nama }}! 👋</h2>
            <p class="text-xs text-slate-400 mt-1">Pantau verifikasi pesanan masuk, alur pengerjaan worker, dan transaksi joki game EiraStore.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pesanan.index', ['filter' => 'tindakan']) }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Periksa Antrean Tindakan ({{ $butuhTindakanCount }})
            </a>
        </div>
    </div>

    <!-- 4 Key Executive Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pesanan Butuh Tindakan -->
        <a href="{{ route('admin.pesanan.index', ['filter' => 'tindakan']) }}" 
           class="p-5 rounded-2xl bg-brand-card/90 border border-brand-border hover:border-amber-500/50 transition-all shadow-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Butuh Tindakan Admin</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black {{ $butuhTindakanCount > 0 ? 'text-amber-400' : 'text-slate-300' }}">{{ $butuhTindakanCount }}</span>
                <span class="text-xs text-amber-400/80 font-medium">Prioritas Verifikasi</span>
            </div>
        </a>

        <!-- Total Pesanan -->
        <a href="{{ route('admin.pesanan.index', ['status' => 'semua']) }}" 
           class="p-5 rounded-2xl bg-brand-card/90 border border-brand-border hover:border-brand-cyan/50 transition-all shadow-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Seluruh Pesanan</span>
                <div class="w-10 h-10 rounded-xl bg-brand-blue/15 border border-brand-blue/30 text-brand-cyan flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black text-white">{{ $totalPesanan }}</span>
                <span class="text-xs text-slate-400 font-medium">Riwayat Transaksi</span>
            </div>
        </a>

        <!-- Total Worker Aktif -->
        <a href="{{ route('admin.workers.index') }}" 
           class="p-5 rounded-2xl bg-brand-card/90 border border-brand-border hover:border-cyan-500/50 transition-all shadow-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Worker Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black text-white">{{ $totalWorkerAktif }}</span>
                <span class="text-xs text-cyan-400 font-medium">Siap Ditugaskan</span>
            </div>
        </a>

        <!-- Total Pendapatan Selesai -->
        <div class="p-5 rounded-2xl bg-brand-card/90 border border-brand-border shadow-card">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pendapatan Pesanan Selesai</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-400">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                <span class="text-xs text-emerald-400/80 font-medium">Omzet Sukses</span>
            </div>
        </div>
    </div>

    <!-- 11 Kartu Status Pesanan Lengkap (Referensi: PRD A-19) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Distribusi Status Pesanan (11 Status Kunci)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Klik pada salah satu status untuk membuka daftar pesanan yang difilter otomatis</p>
            </div>
            <a href="{{ route('admin.pesanan.index', ['status' => 'semua']) }}" class="text-xs text-brand-cyan hover:underline font-semibold">
                Lihat Semua Pesanan &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            
            <!-- 1. Menunggu Verifikasi -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'menunggu_verifikasi']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-amber-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Menunggu Verifikasi</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['menunggu_verifikasi'] }}</span>
                    <span class="text-[10px] text-slate-500">Bukti/Data</span>
                </div>
            </a>

            <!-- 2. Perlu Revisi Data -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'perlu_revisi_data']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-orange-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-orange-400 uppercase tracking-wider">Perlu Revisi Data</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['perlu_revisi_data'] }}</span>
                    <span class="text-[10px] text-slate-500">Customer</span>
                </div>
            </a>

            <!-- 3. Siap Ditugaskan -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'siap_ditugaskan']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-blue-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-blue-400 uppercase tracking-wider">Siap Ditugaskan</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['siap_ditugaskan'] }}</span>
                    <span class="text-[10px] text-slate-500">Pilih Worker</span>
                </div>
            </a>

            <!-- 4. Diproses -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'diproses']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-indigo-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-indigo-400 uppercase tracking-wider">Diproses</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['diproses'] }}</span>
                    <span class="text-[10px] text-slate-500">Antrean Worker</span>
                </div>
            </a>

            <!-- 5. Sedang Dikerjakan -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'sedang_dikerjakan']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-purple-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-purple-400 uppercase tracking-wider">Sedang Dikerjakan</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['sedang_dikerjakan'] }}</span>
                    <span class="text-[10px] text-slate-500">On Progress</span>
                </div>
            </a>

            <!-- 6. Laporan Masuk -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'laporan_masuk']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-yellow-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-yellow-400 uppercase tracking-wider">Laporan Masuk</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['laporan_masuk'] }}</span>
                    <span class="text-[10px] text-slate-500">Cek Hasil</span>
                </div>
            </a>

            <!-- 7. Worker Selesai -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'worker_selesai']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-teal-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-teal-400 uppercase tracking-wider">Worker Selesai</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['worker_selesai'] }}</span>
                    <span class="text-[10px] text-slate-500">Kirim Notif</span>
                </div>
            </a>

            <!-- 8. Menunggu Konfirmasi -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'menunggu_konfirmasi']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-cyan-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-cyan-400 uppercase tracking-wider">Menunggu Konfirmasi</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['menunggu_konfirmasi'] }}</span>
                    <span class="text-[10px] text-slate-500">Customer</span>
                </div>
            </a>

            <!-- 9. Dikomplain -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'dikomplain']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-rose-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-rose-400 uppercase tracking-wider">Dikomplain</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['dikomplain'] }}</span>
                    <span class="text-[10px] text-slate-500">Komplain Aktif</span>
                </div>
            </a>

            <!-- 10. Perlu Revisi Hasil -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'perlu_revisi_hasil']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-red-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between">
                <span class="text-[11px] font-bold text-red-400 uppercase tracking-wider">Perlu Revisi Hasil</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-white">{{ $statusCounts['perlu_revisi_hasil'] }}</span>
                    <span class="text-[10px] text-slate-500">Perbaikan Worker</span>
                </div>
            </a>

            <!-- 11. Selesai -->
            <a href="{{ route('admin.pesanan.index', ['status' => 'selesai']) }}" 
               class="p-4 rounded-xl bg-brand-card/90 border border-brand-border hover:border-emerald-500/50 hover:bg-brand-surface transition-all flex flex-col justify-between col-span-2 sm:col-span-1 lg:col-span-2">
                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Selesai (Tuntas)</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-emerald-400">{{ $statusCounts['selesai'] }}</span>
                    <span class="text-[10px] text-slate-500">Pesanan Sukses</span>
                </div>
            </a>

        </div>
    </div>

    <!-- Pesanan Terbaru Butuh Tindakan Admin -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Pesanan Terbaru yang Membutuhkan Tindakan
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Antrean pesanan yang menunggu verifikasi, penugasan worker, atau respon keluhan</p>
            </div>
            <a href="{{ route('admin.pesanan.index', ['filter' => 'tindakan']) }}" class="text-xs text-brand-cyan hover:underline font-semibold">
                Lihat Seluruh Antrean &rarr;
            </a>
        </div>

        @if($pesananPerluTindakan->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-brand-surface/60 border-b border-brand-border text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        <tr>
                            <th class="py-3 px-4">Kode Pesanan</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            <th class="py-3 px-4">Layanan Game</th>
                            <th class="py-3 px-4">Total Bayar</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/60">
                        @foreach($pesananPerluTindakan as $item)
                            <tr class="hover:bg-brand-surface/30 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-white text-xs">
                                    {{ $item->kode_pesanan }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-white text-xs">{{ $item->customer->nama ?? 'Tamu' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $item->no_wa_customer ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="text-xs font-medium text-slate-200">{{ $item->layanan->nama_layanan ?? '-' }}</p>
                                    <p class="text-[11px] text-brand-cyan">{{ $item->layanan->game->nama_game ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-white text-xs">
                                    Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $item->status->badgeColor() }}">
                                        {{ $item->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.pesanan.show', $item->kode_pesanan) }}" 
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-brand-surface border border-brand-border text-xs font-semibold text-brand-cyan hover:bg-brand-blue hover:text-white hover:border-transparent transition-all">
                                        Periksa & Kelola &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400 bg-brand-surface/20 rounded-xl border border-brand-border/50">
                <svg class="w-10 h-10 mx-auto text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold text-white">Semua Bersih & Terkendali!</p>
                <p class="text-xs text-slate-500 mt-1">Saat ini tidak ada pesanan yang memerlukan tindakan verifikasi mendesak.</p>
            </div>
        @endif
    </div>

</div>
@endsection
