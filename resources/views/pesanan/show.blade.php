@extends('layouts.storefront')

@section('content')
<div class="py-8 lg:py-12" x-data="checkoutPage({{ $sisaDetik ?? 0 }})">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                    <a href="{{ route('beranda') }}" class="hover:text-brand-cyan transition-colors">Beranda</a>
                    <span>/</span>
                    <a href="{{ route('pesanan.saya') }}" class="hover:text-brand-cyan transition-colors">Pesanan Saya</a>
                    <span>/</span>
                    <span class="text-white font-medium">{{ $pesanan->kode_pesanan }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white">{{ $pesanan->kode_pesanan }}</h1>
                    
                    <!-- Status Badge -->
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $pesanan->status->badgeColor() }}">
                        {{ $pesanan->status->label() }}
                    </span>
                </div>
            </div>

            <!-- Tanggal Order -->
            <div class="text-xs text-slate-400 sm:text-right">
                <span class="block">Dibuat pada:</span>
                <span class="font-semibold text-slate-200">{{ $pesanan->created_at->format('d M Y, H:i') }} WIB</span>
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

        <!-- ======================================================= -->
        <!-- BANNER COUNTDOWN 2 JAM (HANYA SAAT MENUNGGU PEMBAYARAN) -->
        <!-- ======================================================= -->
        @if($pesanan->status === \App\Enums\StatusPesanan::MenungguPembayaran)
            <div class="relative overflow-hidden rounded-3xl border border-brand-blue/30 bg-gradient-to-r from-brand-card via-brand-surface to-brand-card p-6 sm:p-8 shadow-card">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-brand-blue/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </span>
                            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Menunggu Pembayaran Transfer</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-white">Selesaikan Pembayaran Sebelum Batas Waktu</h2>
                        <p class="text-xs text-slate-400 max-w-xl">
                            Pesanan akan otomatis kedaluwarsa dan kredensial akun game akan langsung dihapus dalam 2 jam demi keamanan jika belum ada pembayaran.
                        </p>
                    </div>

                    <!-- Countdown Display Widget -->
                    <div class="flex items-center gap-3 self-start md:self-auto shrink-0">
                        <!-- Jam -->
                        <div class="flex flex-col items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-brand-card border border-brand-border shadow-card">
                            <span class="text-xl sm:text-2xl font-black text-white font-mono" x-text="hours">00</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Jam</span>
                        </div>
                        <span class="text-xl font-bold text-slate-500">:</span>
                        <!-- Menit -->
                        <div class="flex flex-col items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-brand-card border border-brand-border shadow-card">
                            <span class="text-xl sm:text-2xl font-black text-brand-cyan font-mono" x-text="minutes">00</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Menit</span>
                        </div>
                        <span class="text-xl font-bold text-slate-500">:</span>
                        <!-- Detik -->
                        <div class="flex flex-col items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-brand-card border border-brand-border shadow-card">
                            <span class="text-xl sm:text-2xl font-black text-amber-400 font-mono" x-text="seconds">00</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Detik</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Batalkan Pesanan Mandiri -->
                <div class="mt-6 pt-6 border-t border-brand-border/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <p class="text-xs text-slate-400">Salah memilih paket atau ingin mengubah data akun game?</p>
                    <button type="button" @click="showBatalModal = true"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold hover:bg-rose-500/20 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Batalkan Pesanan Ini</span>
                    </button>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- SISI KIRI: Aksi Customer, Instruksi Rekening & Form Unggah Bukti (Cols 7) -->
            <div class="lg:col-span-7 space-y-8">
                
                <!-- ======================================================= -->
                <!-- AKSI CUSTOMER MENURUT SIKLUS PENYELESAIAN (C-11 s/d C-14) -->
                <!-- DITAMPILKAN DI ATAS AGAR LANGSUNG TERLIHAT OLEH CUSTOMER -->
                <!-- ======================================================= -->

                {{-- 1. MENUNGGU KONFIRMASI: SETUJUI HASIL (C-11) ATAU AJUKAN KOMPLAIN (C-12) --}}
                @if($pesanan->status === \App\Enums\StatusPesanan::MenungguKonfirmasi)
                    @php
                        $riwayatKonfirmasiTerbaru = $pesanan->riwayatStatus
                            ->where('status_baru', \App\Enums\StatusPesanan::MenungguKonfirmasi->value)
                            ->last();
                        $pesanAdminSelesai = null;
                        if ($riwayatKonfirmasiTerbaru && $riwayatKonfirmasiTerbaru->catatan) {
                            $prefix = 'Notifikasi penyelesaian diteruskan ke pelanggan: ';
                            if (\Illuminate\Support\Str::startsWith($riwayatKonfirmasiTerbaru->catatan, $prefix)) {
                                $pesanAdminSelesai = \Illuminate\Support\Str::after($riwayatKonfirmasiTerbaru->catatan, $prefix);
                            }
                        }
                    @endphp
                    <div class="bg-brand-card/90 border-2 border-brand-cyan/40 rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-brand-cyan/15 text-brand-cyan border border-brand-cyan/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Hasil Pengerjaan Siap Dikonfirmasi! (C-11 / C-12)</h2>
                                <p class="text-xs text-slate-400">Worker telah menyelesaikan tugas dan Admin telah memverifikasi laporan.</p>
                            </div>
                        </div>

                        @if($pesanAdminSelesai)
                            <!-- PESAN KHUSUS DARI ADMIN UNTUK CUSTOMER -->
                            <div class="p-4 rounded-2xl bg-brand-surface/95 border-2 border-brand-cyan/50 space-y-2 shadow-inner">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-xl bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/30 flex items-center justify-center text-xs">💬</span>
                                    <strong class="font-bold text-white text-xs uppercase tracking-wider">Pesan dari Admin / EiraStore:</strong>
                                </div>
                                <div class="p-3.5 rounded-xl bg-brand-card/80 border border-brand-border text-slate-100 text-xs leading-relaxed font-medium">
                                    "{{ $pesanAdminSelesai }}"
                                </div>
                            </div>
                        @endif

                        <!-- Petunjuk Pengecekan -->
                        <div class="p-4 rounded-2xl bg-brand-cyan/10 border border-brand-cyan/20 text-brand-cyan text-xs leading-relaxed space-y-2">
                            <div class="flex items-center gap-2 font-bold text-white">
                                <span>🎮</span>
                                <span>Petunjuk Pengecekan Hasil:</span>
                            </div>
                            <ol class="list-decimal list-inside space-y-1 text-slate-300 text-[11px]">
                                <li>Periksa foto screenshot bukti pengerjaan dari Worker di bawah ini.</li>
                                <li>Login ke akun game Anda untuk memeriksa langsung apakah rank / level / misi sudah sesuai pesanan.</li>
                                <li>Bila hasil sudah cocok, klik <strong class="text-emerald-400">Setujui Hasil</strong>. Password akun game Anda akan <strong>langsung dihapus secara permanen</strong> demi privasi.</li>
                                <li>Bila ada kekurangan atau belum sesuai, klik <strong class="text-rose-400">Ajukan Komplain</strong> agar Worker segera memperbaikinya.</li>
                            </ol>
                        </div>

                        @if($pesanan->laporanTerbaru)
                            <div class="p-4 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Screenshot Hasil Pengerjaan Worker
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $pesanan->laporanTerbaru->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>

                                <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40 max-w-md">
                                    <img src="{{ route('pesanan.bukti-selesai', $pesanan->kode_pesanan) }}" 
                                         alt="Bukti Selesai {{ $pesanan->kode_pesanan }}"
                                         class="w-full max-h-60 object-cover object-top group-hover:scale-105 transition-transform cursor-pointer"
                                         @click="previewBuktiSelesaiModal = true">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer"
                                         @click="previewBuktiSelesaiModal = true">
                                        <span class="px-3.5 py-1.5 rounded-xl bg-brand-card/90 text-white text-xs font-bold border border-brand-border shadow-lg flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Perbesar Screenshot
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Tombol Aksi C-11 & C-12 -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <button type="button" @click="modalSetujuiHasil = true"
                                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:opacity-95 text-white text-xs font-black shadow-glow-emerald flex items-center justify-center gap-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Setujui Hasil Joki (C-11)</span>
                            </button>

                            <button type="button" @click="modalKomplain = true"
                                    class="w-full py-3.5 px-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 hover:bg-rose-500/25 hover:text-white text-xs font-bold flex items-center justify-center gap-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Ajukan Komplain (C-12)</span>
                            </button>
                        </div>
                    </div>

                {{-- 2. HASIL DISETUJUI: KONFIRMASI SELESAI (C-13) --}}
                @elseif($pesanan->status === \App\Enums\StatusPesanan::HasilDisetujui)
                    <div class="bg-brand-card/90 border-2 border-emerald-500/40 rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Hasil Pengerjaan Telah Disetujui!</h2>
                                <p class="text-xs text-slate-400">Password akun game Anda telah dihapus secara aman dari database.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs leading-relaxed">
                            Terima kasih telah memeriksa hasil joki. Silakan klik tombol <strong>Konfirmasi Selesai</strong> di bawah untuk menutup pesanan ini secara final dan memberikan rating layanan.
                        </div>

                        @if($pesanan->laporanTerbaru)
                            <div class="p-4 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Bukti Pengerjaan Terverifikasi</span>
                                <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40 max-w-sm">
                                    <img src="{{ route('pesanan.bukti-selesai', $pesanan->kode_pesanan) }}" 
                                         alt="Bukti Selesai {{ $pesanan->kode_pesanan }}"
                                         class="w-full max-h-52 object-cover object-top group-hover:scale-105 transition-transform cursor-pointer"
                                         @click="previewBuktiSelesaiModal = true">
                                </div>
                            </div>
                        @endif

                        <button type="button" @click="modalKonfirmasiSelesai = true"
                                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-black shadow-glow-blue hover:opacity-95 transition-all text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Konfirmasi Selesai (C-13)</span>
                        </button>
                    </div>

                {{-- 3. SELESAI: RATING & ULASAN (C-14) ATAU LIHAT ULASAN --}}
                @elseif($pesanan->status === \App\Enums\StatusPesanan::Selesai)
                    <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Pesanan Selesai</h2>
                                <p class="text-xs text-slate-400">Selesai pada {{ $pesanan->selesai_at?->format('d M Y, H:i') ?? $pesanan->updated_at->format('d M Y, H:i') }} WIB</p>
                            </div>
                        </div>

                        @if($pesanan->ulasan)
                            <!-- TAMPILAN ULASAN YANG SUDAH DIBERIKAN -->
                            <div class="p-5 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-white uppercase tracking-wider">Ulasan & Penilaian Anda</span>
                                    <span class="text-[11px] text-slate-400">{{ $pesanan->ulasan->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>

                                <!-- Bintang Rating -->
                                <div class="flex items-center gap-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $pesanan->ulasan->rating ? 'text-amber-400 fill-amber-400' : 'text-slate-600' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="text-xs font-bold text-amber-400 ml-1">{{ $pesanan->ulasan->rating }}/5 Bintang</span>
                                </div>

                                @if($pesanan->ulasan->komentar)
                                    <p class="text-xs text-slate-300 italic pt-1">"{{ $pesanan->ulasan->komentar }}"</p>
                                @endif
                            </div>
                        @else
                            <!-- FORM RATING & ULASAN CUSTOMER (C-14) -->
                            <div class="p-5 rounded-2xl bg-brand-surface/70 border border-brand-cyan/30 space-y-4">
                                <div>
                                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                        <span class="text-amber-400">★</span> Beri Rating & Ulasan (C-14)
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Bagaimana pengalaman Anda memakai layanan joki EiraStore?</p>
                                </div>

                                <form action="{{ route('pesanan.simpan-ulasan', $pesanan->kode_pesanan) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="rating" :value="reviewRating">

                                    <!-- Interaktif Star Selector -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-2">Pilih Rating Bintang:</label>
                                        <div class="flex items-center gap-2">
                                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                                <button type="button" 
                                                        @click="reviewRating = star"
                                                        @mouseenter="reviewHover = star"
                                                        @mouseleave="reviewHover = reviewRating"
                                                        class="p-1 hover:scale-125 transition-transform focus:outline-none">
                                                    <svg class="w-7 h-7" :class="(reviewHover >= star) ? 'text-amber-400 fill-amber-400' : 'text-slate-600'" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <span class="text-xs font-bold text-amber-400 ml-2" x-text="reviewRating + ' Bintang'"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="komentar" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                            Komentar / Ulasan <span class="text-slate-500 font-normal">(Opsional, maksimal 500 karakter)</span>
                                        </label>
                                        <textarea id="komentar" name="komentar" rows="3" maxlength="500"
                                                  placeholder="Ceritakan kepuasan Anda terhadap kecepatan pengerjaan dan keramahan layanan EiraStore..."
                                                  class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none"></textarea>
                                    </div>

                                    <button type="submit"
                                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:opacity-95 text-brand-bg font-black text-xs shadow-glow-orange transition-all">
                                        Kirim Rating & Ulasan
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if($pesanan->laporanTerbaru)
                            <div class="p-4 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Foto Bukti Pengerjaan Joki</span>
                                <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40 max-w-sm">
                                    <img src="{{ route('pesanan.bukti-selesai', $pesanan->kode_pesanan) }}" 
                                         alt="Bukti Selesai {{ $pesanan->kode_pesanan }}"
                                         class="w-full max-h-52 object-cover object-top group-hover:scale-105 transition-transform cursor-pointer"
                                         @click="previewBuktiSelesaiModal = true">
                                </div>
                            </div>
                        @endif
                    </div>

                {{-- 4. DIKOMPLAIN: STATUS PENINJAUAN ADMIN --}}
                @elseif($pesanan->status === \App\Enums\StatusPesanan::Dikomplain)
                    <div class="bg-brand-card/90 border-2 border-rose-500/40 rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 border border-rose-500/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Komplain Sedang Ditinjau Admin</h2>
                                <p class="text-xs text-slate-400">Admin akan segera memeriksa komplain Anda dan menginstruksikan Worker untuk melakukan revisi pengerjaan.</p>
                            </div>
                        </div>

                        @if($pesanan->laporanTerbaru)
                            <div class="p-4 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Foto Bukti Sebelumnya</span>
                                <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40 max-w-sm">
                                    <img src="{{ route('pesanan.bukti-selesai', $pesanan->kode_pesanan) }}" 
                                         alt="Bukti Selesai {{ $pesanan->kode_pesanan }}"
                                         class="w-full max-h-52 object-cover object-top group-hover:scale-105 transition-transform cursor-pointer"
                                         @click="previewBuktiSelesaiModal = true">
                                </div>
                            </div>
                        @endif
                    </div>

                {{-- 5. PERLU REVISI HASIL: WORKER SEDANG MEMPERBAIKI --}}
                @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
                    <div class="bg-brand-card/90 border-2 border-amber-500/40 rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Worker Sedang Melakukan Revisi Pengerjaan</h2>
                                <p class="text-xs text-slate-400">Worker sedang melanjutkan pengerjaan sesuai arahan revisi. Kami akan memberi tahu Anda setelah laporan baru masuk.</p>
                            </div>
                        </div>
                    </div>

                {{-- 6. DIBATALKAN: ALASAN PEMBATALAN DARI ADMIN --}}
                @elseif($pesanan->status === \App\Enums\StatusPesanan::Dibatalkan)
                    @php
                        $riwayatBatal = $pesanan->riwayatStatus
                            ->where('status_baru', \App\Enums\StatusPesanan::Dibatalkan->value)
                            ->last();
                        $alasanBatal = $riwayatBatal?->catatan;
                    @endphp
                    <div class="bg-brand-card/90 border-2 border-rose-500/40 rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 border border-rose-500/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Pesanan Dibatalkan</h2>
                                <p class="text-xs text-slate-400">Pesanan ini telah dibatalkan dan seluruh kredensial akun telah dihapus dari sistem.</p>
                            </div>
                        </div>

                        @if($alasanBatal)
                            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-xs space-y-1.5">
                                <span class="text-[10px] text-rose-300 uppercase font-bold block tracking-wider">Keterangan / Alasan dari Admin:</span>
                                <p class="text-slate-100 font-medium">"{{ $alasanBatal }}"</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- ======================================================= -->
                <!-- INSTRUKSI REKENING & FORM PEMBAYARAN / STATUS TRANSFER  -->
                <!-- ======================================================= -->
                @if($pesanan->status === \App\Enums\StatusPesanan::MenungguPembayaran)
                    <!-- DAFTAR REKENING PEMBAYARAN TUJUAN -->
                    <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex items-center justify-between pb-4 border-b border-brand-border">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Rekening Tujuan Pembayaran</h2>
                                <p class="text-xs text-slate-400">Silakan transfer nominal pas sesuai total tagihan</p>
                            </div>
                            <span class="text-xs font-bold text-brand-cyan bg-brand-blue/15 px-3 py-1 rounded-full border border-brand-blue/30">
                                Total: Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Grid Pilihan Rekening -->
                        <div class="space-y-4">
                            @foreach($metodePembayaran as $metode)
                                <div class="p-4 rounded-2xl bg-brand-surface border border-brand-border hover:border-slate-600 transition-all space-y-3">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            @if($metode->logo_url)
                                                <img src="{{ $metode->logo_url }}" alt="{{ $metode->nama_metode }}" class="h-8 max-w-[80px] object-contain rounded-lg p-1 bg-white">
                                            @else
                                                <div class="w-10 h-8 rounded-lg bg-brand-blue/20 border border-brand-blue/30 flex items-center justify-center text-brand-cyan font-black text-xs">
                                                    {{ strtoupper(substr($metode->nama_metode, 0, 3)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="text-xs font-bold text-white block">{{ $metode->nama_metode }}</span>
                                                <span class="text-[11px] text-slate-400">a.n. {{ $metode->atas_nama }}</span>
                                            </div>
                                        </div>

                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/5 text-slate-400 border border-brand-border">
                                            {{ $metode->kategori }}
                                        </span>
                                    </div>

                                    @if($metode->isQris() && $metode->gambar_qris_url)
                                        <!-- Tombol Tampilkan QRIS -->
                                        <div class="pt-2 flex items-center justify-between">
                                            <span class="text-xs text-slate-400">Scan QRIS melalui semua m-Banking / e-Wallet</span>
                                            <button type="button" @click="qrisModal = '{{ $metode->gambar_qris_url }}'"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 text-xs font-bold hover:bg-brand-blue/30 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                                <span>Buka QRIS</span>
                                            </button>
                                        </div>
                                    @else
                                        <!-- Nomor Rekening & Salin -->
                                        <div class="pt-2 flex items-center justify-between bg-brand-card px-3 py-2 rounded-xl border border-brand-border/60">
                                            <span class="font-mono text-sm font-black text-white tracking-wider">{{ $metode->nomor_rekening }}</span>
                                            <button type="button" @click="salinRekening('{{ $metode->nomor_rekening }}', '{{ $metode->nama_metode }}')"
                                                    class="text-xs font-bold text-brand-cyan hover:text-white flex items-center gap-1 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Salin</span>
                                            </button>
                                        </div>
                                    @endif

                                    @if($metode->petunjuk_transfer)
                                        <p class="text-[11px] text-slate-400 pt-1 leading-relaxed">{{ $metode->petunjuk_transfer }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- FORM UNGGAH BUKTI PEMBAYARAN -->
                    <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="pb-4 border-b border-brand-border">
                            <h2 class="text-base sm:text-lg font-bold text-white">Unggah Bukti Transfer</h2>
                            <p class="text-xs text-slate-400">Setelah berhasil transfer, lampirkan bukti/struk agar pembayaran diverifikasi oleh Admin</p>
                        </div>

                        <form action="{{ route('pesanan.bukti.unggah', $pesanan->kode_pesanan) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <label for="metode_pembayaran" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Rekening Tujuan Transfer Anda <span class="text-rose-400">*</span>
                                </label>
                                <select id="metode_pembayaran" name="metode_pembayaran" required
                                        class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                    <option value="" disabled selected>Pilih rekening tujuan transfer</option>
                                    @foreach($metodePembayaran as $metode)
                                        <option value="{{ $metode->nama_metode }} - {{ $metode->nomor_rekening }}">
                                            {{ $metode->nama_metode }} ({{ $metode->nomor_rekening }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Foto / Screenshot Bukti Transfer <span class="text-rose-400">*</span>
                                </label>

                                <!-- Dropzone & Live Image Preview -->
                                <div class="mt-1 p-4 border-2 border-brand-border border-dashed rounded-2xl bg-brand-surface/60 transition-all">
                                    <!-- Jika belum memilih file -->
                                    <div x-show="!filePreviewUrl" class="space-y-2 text-center py-4">
                                        <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-xs text-slate-400 justify-center">
                                            <label for="bukti_transfer" class="relative cursor-pointer rounded-xl px-4 py-2 bg-brand-blue/20 hover:bg-brand-blue/30 text-brand-cyan border border-brand-blue/30 font-bold transition-all">
                                                <span>Pilih Berkas Struk</span>
                                                <input id="bukti_transfer" name="bukti_transfer" type="file" accept="image/jpeg,image/png,image/webp" required class="sr-only" @change="previewImage">
                                            </label>
                                        </div>
                                        <p class="text-[11px] text-slate-400">Format JPG, PNG, atau WebP (Maksimal 2 MB)</p>
                                    </div>

                                    <!-- Live Preview Setelah File Dipilih -->
                                    <div x-show="filePreviewUrl" x-cloak class="space-y-3">
                                        <div class="relative rounded-xl overflow-hidden border border-brand-border bg-black/60 max-w-sm mx-auto p-2">
                                            <img :src="filePreviewUrl" alt="Pratinjau Bukti Transfer" class="w-full max-h-60 object-contain rounded-lg mx-auto">
                                            <button type="button" @click="hapusFileTerpilih"
                                                    class="absolute top-4 right-4 p-2 rounded-xl bg-rose-500/90 hover:bg-rose-600 text-white shadow-lg transition-all"
                                                    title="Hapus foto dan pilih ulang">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="flex items-center justify-between px-2 text-xs">
                                            <span class="text-emerald-400 font-semibold flex items-center gap-1.5 truncate max-w-[240px]">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span x-text="fileName"></span>
                                            </span>
                                            <label for="bukti_transfer" class="text-brand-cyan hover:underline cursor-pointer font-bold shrink-0">
                                                Ganti Berkas
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-bold shadow-glow-blue hover:opacity-95 active:scale-[0.99] transition-all text-center">
                                Kirim Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData)
                    <!-- =================================================== -->
                    <!-- FORM REVISI DATA AKUN GAME OLEH CUSTOMER (C-10)     -->
                    <!-- =================================================== -->
                    <div class="bg-brand-card/90 border-2 border-orange-500/40 rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="p-4 rounded-2xl bg-orange-500/10 border border-orange-500/30 text-orange-300 text-xs space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⚠️</span>
                                <strong class="font-bold text-white text-sm">Admin Meminta Revisi Data Akun Game</strong>
                            </div>
                            @php
                                $catatanRevisi = $pesanan->riwayatStatus->where('status_baru', \App\Enums\StatusPesanan::PerluRevisiData->value)->last()?->catatan;
                            @endphp
                            @if($catatanRevisi)
                                <div class="p-3 rounded-xl bg-brand-surface/80 border border-brand-border/60 text-slate-200 text-xs">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Pesan dari Admin:</span>
                                    <p class="italic">"{{ $catatanRevisi }}"</p>
                                </div>
                            @endif
                            <p class="text-[11px] text-orange-200/90 leading-relaxed">
                                Silakan perbaiki data login akun game Anda di bawah ini tanpa perlu membayar ulang. Setelah Anda menekan <strong>Simpan Revisi Data</strong>, status pesanan akan otomatis kembali ke <strong>Menunggu Verifikasi</strong>.
                            </p>
                        </div>

                        <form action="{{ route('pesanan.revisi-data', $pesanan->kode_pesanan) }}" method="POST" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="id_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        ID Akun Game <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" id="id_akun_game" name="id_akun_game" required
                                           value="{{ old('id_akun_game', $pesanan->id_akun_game) }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>

                                <div>
                                    <label for="login_via" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Metode Login
                                    </label>
                                    <input type="text" id="login_via" name="login_via"
                                           value="{{ old('login_via', $pesanan->login_via) }}"
                                           placeholder="Moonton / Facebook / Google / dll"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>

                                <div>
                                    <label for="username_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Username / Email Login
                                    </label>
                                    <input type="text" id="username_akun_game" name="username_akun_game"
                                           value="{{ old('username_akun_game', $pesanan->username_akun_game) }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>

                                <div>
                                    <label for="password_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Password Baru (Opsional)
                                    </label>
                                    <input type="password" id="password_akun_game" name="password_akun_game"
                                           placeholder="Kosongkan jika password lama benar"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                    <span class="text-[10px] text-slate-500">Isi hanya jika ingin mengganti password akun.</span>
                                </div>

                                <div>
                                    <label for="nickname_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Nickname In-Game
                                    </label>
                                    <input type="text" id="nickname_game" name="nickname_game"
                                           value="{{ old('nickname_game', $pesanan->nickname_game) }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>

                                <div>
                                    <label for="server_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Server Game
                                    </label>
                                    <input type="text" id="server_game" name="server_game"
                                           value="{{ old('server_game', $pesanan->server_game) }}"
                                           placeholder="Contoh: Asia / Server 101"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>

                                <div>
                                    <label for="zone_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Zone ID (Khusus MLBB dll)
                                    </label>
                                    <input type="text" id="zone_id" name="zone_id"
                                           value="{{ old('zone_id', $pesanan->data_tambahan['zone_id'] ?? '') }}"
                                           placeholder="Contoh: (2123)"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>

                                <div>
                                    <label for="no_wa_customer" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Nomor WhatsApp Aktif
                                    </label>
                                    <input type="text" id="no_wa_customer" name="no_wa_customer"
                                           value="{{ old('no_wa_customer', $pesanan->no_wa_customer) }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label for="catatan_customer" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Catatan Tambahan untuk Worker
                                </label>
                                <textarea id="catatan_customer" name="catatan_customer" rows="2"
                                          class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">{{ old('catatan_customer', $pesanan->catatan_customer) }}</textarea>
                            </div>

                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:opacity-95 text-brand-bg text-sm font-black shadow-glow-orange transition-all text-center">
                                Simpan Revisi Data Akun & Kirim Ulang ke Admin &rarr;
                            </button>
                        </form>
                    </div>

                    <!-- KARTU STATUS PEMBAYARAN -->
                    <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-white">Status Pembayaran: {{ $pesanan->pembayaran?->status?->label() ?? 'Menunggu Verifikasi' }}</h3>
                                <p class="text-xs text-slate-400">Pembayaran Anda tetap tersimpan dan tidak perlu ditransfer ulang.</p>
                            </div>
                        </div>

                        @if($pesanan->pembayaran && $pesanan->pembayaran->bukti_transfer)
                            <div class="p-4 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Struk Bukti Pembayaran Tersimpan
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $pesanan->pembayaran->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>

                                <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40 max-w-sm">
                                    <img src="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" 
                                         alt="Bukti Transfer {{ $pesanan->kode_pesanan }}"
                                         class="w-full max-h-56 object-cover object-top group-hover:scale-105 transition-transform cursor-pointer"
                                         @click="previewBuktiModal = true">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer"
                                         @click="previewBuktiModal = true">
                                        <span class="px-3.5 py-1.5 rounded-xl bg-brand-card/90 text-white text-xs font-bold border border-brand-border shadow-lg flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Perbesar Gambar
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1 text-xs">
                                    <a href="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-brand-cyan hover:underline font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Buka Tab Baru
                                    </a>
                                    <span class="text-[11px] text-emerald-400 font-medium">✓ Bukti tersimpan rapi</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- INFORMASI STATUS PEMBAYARAN SAAT TIDAK MENUNGGU PEMBAYARAN -->
                    <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-white">Status Pembayaran: {{ $pesanan->pembayaran?->status?->label() ?? 'Belum Ada Pembayaran' }}</h3>
                                <p class="text-xs text-slate-400">
                                    @if($pesanan->pembayaran)
                                        Metode: {{ $pesanan->pembayaran->metode_pembayaran }} • Diunggah {{ $pesanan->pembayaran->created_at->diffForHumans() }}
                                    @else
                                        Pesanan ini berstatus {{ $pesanan->status->label() }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($pesanan->pembayaran && $pesanan->pembayaran->bukti_transfer)
                            <div class="p-4 rounded-2xl bg-brand-surface/70 border border-brand-border space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Struk Bukti Pembayaran Tersimpan
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $pesanan->pembayaran->created_at->format('d M Y, H:i') }} WIB</span>
                                </div>

                                <div class="relative group rounded-xl overflow-hidden border border-brand-border bg-black/40 max-w-sm">
                                    <img src="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" 
                                         alt="Bukti Transfer {{ $pesanan->kode_pesanan }}"
                                         class="w-full max-h-56 object-cover object-top group-hover:scale-105 transition-transform cursor-pointer"
                                         @click="previewBuktiModal = true">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer"
                                         @click="previewBuktiModal = true">
                                        <span class="px-3.5 py-1.5 rounded-xl bg-brand-card/90 text-white text-xs font-bold border border-brand-border shadow-lg flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Perbesar Gambar
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1 text-xs">
                                    <a href="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-brand-cyan hover:underline font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Buka Tab Baru
                                    </a>
                                    <span class="text-[11px] text-emerald-400 font-medium">✓ Bukti siap diverifikasi Admin</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- TIMELINE RIWAYAT STATUS PESANAN -->
                <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                    <h3 class="text-base font-bold text-white pb-3 border-b border-brand-border">Riwayat & Timeline Pesanan</h3>
                    
                    <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-brand-border">
                        @php
                            $internalStatuses = ['siap_ditugaskan', 'laporan_masuk', 'worker_selesai'];
                            $filteredRiwayat = $pesanan->riwayatStatus->reject(function ($r) use ($internalStatuses) {
                                return in_array($r->status_baru, $internalStatuses);
                            });
                        @endphp
                        @forelse($filteredRiwayat as $riwayat)
                            <div class="relative flex items-start gap-4">
                                <div class="w-7 h-7 rounded-full bg-brand-surface border-2 border-brand-cyan flex items-center justify-center shrink-0 z-10">
                                    <span class="w-2 h-2 rounded-full bg-brand-cyan"></span>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white uppercase">{{ \App\Enums\StatusPesanan::tryFrom($riwayat->status_baru)?->label() ?? str_replace('_', ' ', $riwayat->status_baru) }}</span>
                                        <span class="text-[10px] text-slate-500">• {{ $riwayat->created_at->format('d M, H:i') }} WIB</span>
                                    </div>
                                    @php
                                        $isWorkerNote = $riwayat->pengubah?->isWorker() || ($riwayat->diubah_oleh && $riwayat->diubah_oleh === $pesanan->worker_id);
                                    @endphp
                                    @if($riwayat->catatan && ! $isWorkerNote)
                                        <p class="text-xs text-slate-400">{{ $riwayat->catatan }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 italic pl-11">Belum ada pembaruan status.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- SISI KANAN: Rincian Pesanan & Akun Game (Cols 5) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- KARTU RINCIAN PESANAN -->
                <div class="bg-brand-card/95 border border-brand-border rounded-3xl p-6 shadow-card space-y-6">
                    <div class="flex items-center gap-4 pb-4 border-b border-brand-border">
                        <img src="{{ $pesanan->layanan->poster_url }}" alt="{{ $pesanan->layanan->nama_layanan }}" class="w-14 h-14 rounded-2xl object-cover border border-brand-border shrink-0">
                        <div>
                            <span class="text-[10px] font-bold text-brand-cyan uppercase tracking-wider">{{ $pesanan->layanan->game->nama_game }}</span>
                            <h3 class="text-sm font-bold text-white">{{ $pesanan->layanan->nama_layanan }}</h3>
                            <span class="text-xs text-slate-400">Estimasi: {{ $pesanan->layanan->estimasi_waktu ?? '1 - 2 Hari' }}</span>
                        </div>
                    </div>

                    <!-- Items Detail -->
                    <div class="space-y-2.5">
                        <span class="text-xs font-bold text-white uppercase tracking-wider block">Item Pengerjaan</span>
                        @foreach($pesanan->pesananItems as $item)
                            <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-xl bg-brand-surface border border-brand-border">
                                <div>
                                    <span class="font-bold text-white block">{{ $item->nama_item }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $item->peran }})</span>
                                </div>
                                <span class="font-bold text-brand-cyan">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Total Tagihan -->
                    <div class="pt-4 border-t border-brand-border flex items-baseline justify-between">
                        <span class="text-sm font-bold text-slate-300">Total Pembayaran:</span>
                        <span class="text-2xl font-black text-brand-cyan">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- KARTU DATA AKUN GAME -->
                <div class="bg-brand-card/95 border border-brand-border rounded-3xl p-6 shadow-card space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-brand-border">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Data Akun Game</h3>
                        <span class="text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Terenkripsi AES-256
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-brand-border/40">
                            <span class="text-slate-400">ID Akun:</span>
                            <span class="font-bold text-white">{{ $pesanan->id_akun_game }}</span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-brand-border/40">
                            <span class="text-slate-400">Metode Login:</span>
                            <span class="font-bold text-white">{{ $pesanan->login_via }}</span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-brand-border/40">
                            <span class="text-slate-400">Username / Email:</span>
                            <span class="font-bold text-white">{{ $pesanan->username_akun_game }}</span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-brand-border/40">
                            <span class="text-slate-400">Password:</span>
                            @if($pesanan->kredensial_dihapus_at)
                                <span class="font-semibold text-rose-400">Kredensial telah dihapus</span>
                            @else
                                <span class="font-mono text-slate-400">•••••••• (Tersimpan)</span>
                            @endif
                        </div>

                        @if($pesanan->nickname_game)
                            <div class="flex justify-between py-1 border-b border-brand-border/40">
                                <span class="text-slate-400">Nickname:</span>
                                <span class="font-bold text-white">{{ $pesanan->nickname_game }}</span>
                            </div>
                        @endif

                        @if($pesanan->server_game)
                            <div class="flex justify-between py-1 border-b border-brand-border/40">
                                <span class="text-slate-400">Server:</span>
                                <span class="font-bold text-white">{{ $pesanan->server_game }}</span>
                            </div>
                        @endif

                        @if(!empty($pesanan->data_tambahan['zone_id']))
                            <div class="flex justify-between py-1 border-b border-brand-border/40">
                                <span class="text-slate-400">Zone ID:</span>
                                <span class="font-bold text-white">{{ $pesanan->data_tambahan['zone_id'] }}</span>
                            </div>
                        @endif

                        @if($pesanan->catatan_customer)
                            <div class="pt-2">
                                <span class="text-slate-400 block mb-1">Catatan Khusus:</span>
                                <p class="p-2.5 rounded-xl bg-brand-surface text-slate-300 text-[11px] border border-brand-border">
                                    {{ $pesanan->catatan_customer }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- ============================================== -->
    <!-- MODAL POPUP QRIS PREVIEW                       -->
    <!-- ============================================== -->
    <div x-show="qrisModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="qrisModal = null">
        <div class="relative bg-brand-card border border-brand-border rounded-3xl p-6 max-w-sm w-full text-center shadow-card"
             @click.away="qrisModal = null">
            <h3 class="text-base font-bold text-white mb-1">Scan QRIS EiraStore</h3>
            <p class="text-xs text-slate-400 mb-4">Gunakan aplikasi BCA, Mandiri, GoPay, Dana, OVO, atau ShopeePay</p>
            
            <div class="bg-white p-3 rounded-2xl inline-block shadow-inner">
                <img :src="qrisModal" alt="QRIS EiraStore" class="w-64 h-64 object-contain">
            </div>

            <p class="text-xs font-black text-brand-cyan mt-4">Total: Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>

            <button type="button" @click="qrisModal = null"
                    class="mt-4 w-full py-2.5 px-4 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-white hover:bg-white/10 transition-colors">
                Tutup QRIS
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI BATALKAN PESANAN MANDIRI     -->
    <!-- ============================================== -->
    <div x-show="showBatalModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="showBatalModal = false">
        <div class="relative bg-brand-card border border-brand-border rounded-3xl p-6 max-w-md w-full text-center shadow-card"
             @click.away="showBatalModal = false">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-white mb-2">Batalkan Pesanan {{ $pesanan->kode_pesanan }}?</h3>
            <p class="text-xs text-slate-400 mb-6">
                Password akun game Anda akan <span class="text-white font-semibold">langsung dihapus secara permanen</span> dari database kami demi keamanan. Anda dapat membuat pesanan baru setelahnya.
            </p>

            <div class="flex items-center gap-3">
                <button type="button" @click="showBatalModal = false"
                        class="flex-1 py-3 px-4 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Tidak, Kembali
                </button>

                <form action="{{ route('pesanan.batalkan', $pesanan->kode_pesanan) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold transition-colors">
                        Ya, Batalkan Pesanan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL PREVIEW BUKTI TRANSFER CUSTOMER          -->
    <!-- ============================================== -->
    @if($pesanan->pembayaran && $pesanan->pembayaran->bukti_transfer)
        <div x-show="previewBuktiModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
             @keydown.escape.window="previewBuktiModal = false">
            <div class="relative max-w-2xl w-full bg-brand-card border border-brand-border rounded-3xl p-6 shadow-card"
                 @click.away="previewBuktiModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border mb-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Struk Bukti Transfer: {{ $pesanan->kode_pesanan }}</h4>
                    <button type="button" @click="previewBuktiModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                <div class="max-h-[75vh] overflow-auto rounded-2xl bg-black/50 p-2 text-center">
                    <img src="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" alt="Struk Transfer" class="w-full object-contain mx-auto rounded-xl">
                </div>
                <div class="mt-4 flex items-center justify-between text-xs">
                    <a href="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" target="_blank"
                       class="text-brand-cyan hover:underline font-bold inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka Gambar Ukuran Asli
                    </a>
                    <button type="button" @click="previewBuktiModal = false"
                            class="px-5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-white hover:bg-white/10 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL SETUJUI HASIL (C-11)                     -->
    <!-- ============================================== -->
    <div x-show="modalSetujuiHasil" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalSetujuiHasil = false">
        <div class="relative bg-brand-card border border-emerald-500/30 rounded-3xl p-6 sm:p-8 max-w-md w-full text-center shadow-card"
             @click.away="modalSetujuiHasil = false">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-white mb-2">Setujui Hasil Pengerjaan?</h3>
            <p class="text-xs text-slate-300 leading-relaxed mb-6">
                Dengan menyetujui, Anda menyatakan bahwa pengerjaan joki sudah sesuai dengan pesanan Anda.
                <br><br>
                <span class="text-emerald-400 font-bold block bg-emerald-500/10 p-2.5 rounded-xl border border-emerald-500/20">
                    🔒 Password akun game Anda akan LANGSUNG DIHAPUS PERMANEN dari sistem demi privasi dan keamanan akun Anda.
                </span>
            </p>

            <div class="flex items-center gap-3">
                <button type="button" @click="modalSetujuiHasil = false"
                        class="flex-1 py-3 px-4 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Batal
                </button>

                <form action="{{ route('pesanan.setujui-hasil', $pesanan->kode_pesanan) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:opacity-95 text-white text-xs font-black shadow-glow-emerald transition-all">
                        Ya, Setujui Hasil
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL AJUKAN KOMPLAIN (C-12)                   -->
    <!-- ============================================== -->
    <div x-show="modalKomplain" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalKomplain = false">
        <div class="relative bg-brand-card border border-rose-500/30 rounded-3xl p-6 sm:p-8 max-w-lg w-full text-left shadow-card"
             @click.away="modalKomplain = false">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Ajukan Komplain Hasil Pengerjaan</h3>
                    <p class="text-xs text-slate-400">Jelaskan kekurangan atau kendala yang Anda temukan.</p>
                </div>
            </div>

            <form action="{{ route('pesanan.ajukan-komplain', $pesanan->kode_pesanan) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="alasan_komplain" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Alasan & Detail Komplain <span class="text-rose-400">* (minimal 10 karakter)</span>
                    </label>
                    <textarea id="alasan_komplain" name="alasan" rows="4" required minlength="10" maxlength="1000"
                              placeholder="Contoh: Rank belum mencapai target Mythic Honor, saat ini masih Legend 1 bintang 3..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-rose-400 focus:outline-none"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Admin akan meninjau keluhan Anda dan menginstruksikan Worker untuk melanjutkan perbaikan.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="modalKomplain = false"
                            class="py-2.5 px-4 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="py-2.5 px-5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold transition-all shadow-glow-rose">
                        Kirim Komplain
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL KONFIRMASI SELESAI (C-13)                -->
    <!-- ============================================== -->
    <div x-show="modalKonfirmasiSelesai" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="modalKonfirmasiSelesai = false">
        <div class="relative bg-brand-card border border-brand-border rounded-3xl p-6 sm:p-8 max-w-md w-full text-center shadow-card"
             @click.away="modalKonfirmasiSelesai = false">
            <div class="w-14 h-14 rounded-2xl bg-brand-cyan/15 border border-brand-cyan/30 text-brand-cyan flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-white mb-2">Konfirmasi Penyelesaian Akhir?</h3>
            <p class="text-xs text-slate-300 leading-relaxed mb-6">
                Pesanan ini akan ditandai sebagai <strong>Selesai Sepenuhnya</strong>. Anda dapat memberikan ulasan dan rating kepuasan layanan setelah ini.
            </p>

            <div class="flex items-center gap-3">
                <button type="button" @click="modalKonfirmasiSelesai = false"
                        class="flex-1 py-3 px-4 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Kembali
                </button>

                <form action="{{ route('pesanan.konfirmasi-selesai', $pesanan->kode_pesanan) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-black shadow-glow-blue hover:opacity-95 transition-all">
                        Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL PREVIEW BUKTI SELESAI WORKER             -->
    <!-- ============================================== -->
    @if($pesanan->laporanTerbaru)
        <div x-show="previewBuktiSelesaiModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
             @keydown.escape.window="previewBuktiSelesaiModal = false">
            <div class="relative max-w-3xl w-full bg-brand-card border border-brand-border rounded-3xl p-6 shadow-card"
                 @click.away="previewBuktiSelesaiModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-brand-border mb-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Foto Bukti Pengerjaan: {{ $pesanan->kode_pesanan }}</h4>
                    <button type="button" @click="previewBuktiSelesaiModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                <div class="max-h-[75vh] overflow-auto rounded-2xl bg-black/50 p-2 text-center">
                    <img src="{{ route('pesanan.bukti-selesai', $pesanan->kode_pesanan) }}" alt="Bukti Pengerjaan Joki" class="w-full object-contain mx-auto rounded-xl">
                </div>
                <div class="mt-4 flex items-center justify-between text-xs">
                    <a href="{{ route('pesanan.bukti-selesai', $pesanan->kode_pesanan) }}" target="_blank"
                       class="text-brand-cyan hover:underline font-bold inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka Gambar Ukuran Penuh
                    </a>
                    <button type="button" @click="previewBuktiSelesaiModal = false"
                            class="px-5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-white hover:bg-white/10 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
function checkoutPage(initialSeconds) {
    return {
        remainingSeconds: initialSeconds,
        hours: '00',
        minutes: '00',
        seconds: '00',
        timer: null,
        fileName: '',
        filePreviewUrl: null,
        qrisModal: null,
        showBatalModal: false,
        previewBuktiModal: false,
        modalSetujuiHasil: false,
        modalKomplain: false,
        modalKonfirmasiSelesai: false,
        previewBuktiSelesaiModal: false,
        reviewRating: 5,
        reviewHover: 5,

        init() {
            this.updateDisplay();
            if (this.remainingSeconds > 0) {
                this.timer = setInterval(() => {
                    this.remainingSeconds--;
                    if (this.remainingSeconds <= 0) {
                        clearInterval(this.timer);
                        this.remainingSeconds = 0;
                        this.updateDisplay();
                        // Refresh halaman ketika waktu habis agar status kedaluwarsa diperbarui
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        this.updateDisplay();
                    }
                }, 1000);
            }
        },

        updateDisplay() {
            const h = Math.floor(this.remainingSeconds / 3600);
            const m = Math.floor((this.remainingSeconds % 3600) / 60);
            const s = this.remainingSeconds % 60;
            this.hours = String(h).padStart(2, '0');
            this.minutes = String(m).padStart(2, '0');
            this.seconds = String(s).padStart(2, '0');
        },

        previewImage(e) {
            const file = e.target.files[0];
            if (file) {
                this.fileName = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                if (this.filePreviewUrl) {
                    URL.revokeObjectURL(this.filePreviewUrl);
                }
                this.filePreviewUrl = URL.createObjectURL(file);
            } else {
                this.fileName = '';
                this.filePreviewUrl = null;
            }
        },

        hapusFileTerpilih() {
            const input = document.getElementById('bukti_transfer');
            if (input) input.value = '';
            if (this.filePreviewUrl) {
                URL.revokeObjectURL(this.filePreviewUrl);
            }
            this.fileName = '';
            this.filePreviewUrl = null;
        },

        salinRekening(nomor, bank) {
            navigator.clipboard.writeText(nomor).then(() => {
                alert('Nomor rekening ' + bank + ' (' + nomor + ') berhasil disalin ke clipboard!');
            }).catch(() => {
                prompt('Salin nomor rekening secara manual:', nomor);
            });
        }
    };
}
</script>
@endpush
@endsection
