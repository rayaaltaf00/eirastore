@extends('layouts.storefront')

@section('content')
<div class="py-8 lg:py-12" x-data="checkoutPage({{ $sisaDetik }})">
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
            
            <!-- SISI KIRI: Instruksi Rekening & Form Unggah Bukti (Cols 7) -->
            <div class="lg:col-span-7 space-y-8">
                
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

                                    @if($metode->instruksi)
                                        <p class="text-[11px] text-slate-500 pt-1">{{ $metode->instruksi }}</p>
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
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-brand-border border-dashed rounded-2xl bg-brand-surface/60 hover:border-slate-500 transition-colors">
                                    <div class="space-y-2 text-center">
                                        <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-xs text-slate-400 justify-center">
                                            <label for="bukti_transfer" class="relative cursor-pointer rounded-md font-bold text-brand-cyan hover:underline">
                                                <span>Pilih Berkas Gambar</span>
                                                <input id="bukti_transfer" name="bukti_transfer" type="file" accept="image/jpeg,image/png,image/webp" required class="sr-only" @change="previewImage">
                                            </label>
                                        </div>
                                        <p class="text-[11px] text-slate-500">JPG, PNG, atau WebP hingga 2MB</p>
                                        <p x-show="fileName" x-text="fileName" class="text-xs font-bold text-white pt-1"></p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-bold shadow-glow-blue hover:opacity-95 transition-all text-center">
                                Kirim Bukti Pembayaran
                            </button>
                        </form>
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
                            <div class="pt-2">
                                <a href="{{ route('pesanan.bukti.unduh', $pesanan->kode_pesanan) }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-slate-300 hover:text-white hover:border-slate-500 transition-colors">
                                    <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Struk Bukti Transfer</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- TIMELINE RIWAYAT STATUS PESANAN -->
                <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-4">
                    <h3 class="text-base font-bold text-white pb-3 border-b border-brand-border">Riwayat & Timeline Pesanan</h3>
                    
                    <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-brand-border">
                        @foreach($pesanan->riwayatStatus as $riwayat)
                            <div class="relative flex items-start gap-4">
                                <div class="w-7 h-7 rounded-full bg-brand-surface border-2 border-brand-cyan flex items-center justify-center shrink-0 z-10">
                                    <span class="w-2 h-2 rounded-full bg-brand-cyan"></span>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white uppercase">{{ str_replace('_', ' ', $riwayat->status_baru) }}</span>
                                        <span class="text-[10px] text-slate-500">• {{ $riwayat->created_at->format('d M, H:i') }} WIB</span>
                                    </div>
                                    @if($riwayat->catatan)
                                        <p class="text-xs text-slate-400">{{ $riwayat->catatan }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
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
        qrisModal: null,
        showBatalModal: false,

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
            }
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
