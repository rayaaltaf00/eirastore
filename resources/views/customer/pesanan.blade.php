@extends('layouts.storefront')

@section('content')
<div class="py-8 lg:py-12" x-data="{ tab: 'aktif' }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-white">Pesanan Saya</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Pantau status pengerjaan joki dan riwayat transaksi akun Anda</p>
            </div>
            <a href="{{ route('beranda') }}#katalog-game"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold shadow-glow-blue hover:opacity-95 transition-all self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Pesan Joki Baru</span>
            </a>
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

        <!-- Tabs Nav -->
        <div class="flex items-center gap-2 border-b border-brand-border pb-px">
            <button type="button" @click="tab = 'aktif'"
                    class="flex items-center gap-2 px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-colors"
                    :class="tab === 'aktif' ? 'border-brand-cyan text-brand-cyan' : 'border-transparent text-slate-400 hover:text-white'">
                <span>Pesanan Aktif</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-brand-surface border border-brand-border text-slate-300">
                    {{ $pesananAktif->count() }}
                </span>
            </button>

            <button type="button" @click="tab = 'riwayat'"
                    class="flex items-center gap-2 px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-colors"
                    :class="tab === 'riwayat' ? 'border-brand-cyan text-brand-cyan' : 'border-transparent text-slate-400 hover:text-white'">
                <span>Riwayat & Selesai</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-brand-surface border border-brand-border text-slate-300">
                    {{ $pesananRiwayat->total() }}
                </span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: PESANAN AKTIF                           -->
        <!-- ============================================== -->
        <div x-show="tab === 'aktif'" class="space-y-4">
            @forelse($pesananAktif as $pesanan)
                <div class="bg-brand-card/90 border {{ $pesanan->status === \App\Enums\StatusPesanan::MenungguKonfirmasi ? 'border-emerald-500/60 ring-1 ring-emerald-500/30' : ($pesanan->status === \App\Enums\StatusPesanan::HasilDisetujui ? 'border-teal-500/60 ring-1 ring-teal-500/30' : ($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData ? 'border-amber-500/60 ring-1 ring-amber-500/30' : 'border-brand-border')) }} rounded-3xl p-5 sm:p-6 shadow-card hover:border-slate-500 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-brand-border">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-black text-white">{{ $pesanan->kode_pesanan }}</span>
                            <span class="text-xs text-slate-500">• {{ $pesanan->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border self-start sm:self-auto {{ $pesanan->status->badgeColor() }}">
                            {{ $pesanan->status->label() }}
                        </span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $pesanan->layanan->poster_url }}" alt="{{ $pesanan->layanan->nama_layanan }}" class="w-14 h-14 rounded-2xl object-cover border border-brand-border shrink-0">
                            <div>
                                <span class="text-[10px] font-bold text-brand-cyan uppercase tracking-wider">{{ $pesanan->layanan->game->nama_game }}</span>
                                <h3 class="text-sm sm:text-base font-bold text-white">{{ $pesanan->layanan->nama_layanan }}</h3>
                                <p class="text-xs text-slate-400">Akun: {{ $pesanan->username_akun_game }} ({{ $pesanan->login_via }})</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-6">
                            <div class="sm:text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Tagihan</span>
                                <span class="text-base sm:text-lg font-black text-brand-cyan">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                            </div>

                            @if($pesanan->status === \App\Enums\StatusPesanan::MenungguKonfirmasi)
                                <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-black shadow-glow-emerald hover:opacity-95 transition-all">
                                    <span>Konfirmasi Hasil</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </a>
                            @elseif($pesanan->status === \App\Enums\StatusPesanan::HasilDisetujui)
                                <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-black shadow-glow-emerald hover:opacity-95 transition-all">
                                    <span>Konfirmasi Selesai</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </a>
                            @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData)
                                <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-brand-bg text-xs font-black shadow-glow-orange hover:opacity-95 transition-all">
                                    <span>Revisi Data Akun</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            @elseif($pesanan->status === \App\Enums\StatusPesanan::MenungguPembayaran)
                                <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold hover:opacity-95 shadow-glow-blue transition-all">
                                    <span>Bayar Sekarang</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-slate-200 text-xs font-bold hover:text-white hover:border-brand-cyan transition-all">
                                    <span>Lihat Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($pesanan->status === \App\Enums\StatusPesanan::MenungguKonfirmasi)
                        <div class="mt-4 pt-3.5 border-t border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-emerald-400 bg-emerald-500/10 -mx-5 -mb-5 sm:-mx-6 sm:-mb-6 p-3.5 sm:px-6 rounded-b-3xl">
                            <span class="flex items-center gap-2 font-bold">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                </span>
                                <span>Hasil joki sudah selesai! Silakan periksa akun game dan konfirmasi hasil sekarang.</span>
                            </span>
                            <span class="inline-flex items-center gap-1 font-extrabold text-teal-300 shrink-0 uppercase tracking-wider text-[11px]">
                                Konfirmasi Sekarang &rarr;
                            </span>
                        </div>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::HasilDisetujui)
                        <div class="mt-4 pt-3.5 border-t border-teal-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-teal-300 bg-teal-500/10 -mx-5 -mb-5 sm:-mx-6 sm:-mb-6 p-3.5 sm:px-6 rounded-b-3xl">
                            <span class="flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Hasil telah disetujui! Segera selesaikan pesanan dan berikan ulasan Anda.</span>
                            </span>
                            <span class="inline-flex items-center gap-1 font-extrabold text-emerald-400 shrink-0 uppercase tracking-wider text-[11px]">
                                Konfirmasi Selesai &rarr;
                            </span>
                        </div>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiData)
                        <div class="mt-4 pt-3.5 border-t border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-amber-400 bg-amber-500/10 -mx-5 -mb-5 sm:-mx-6 sm:-mb-6 p-3.5 sm:px-6 rounded-b-3xl">
                            <span class="flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Admin meminta perbaikan data akun game Anda. Mohon segera perbarui.</span>
                            </span>
                            <span class="inline-flex items-center gap-1 font-extrabold text-amber-300 shrink-0 uppercase tracking-wider text-[11px]">
                                Perbarui Data &rarr;
                            </span>
                        </div>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::MenungguPembayaran)
                        <div class="pt-3 border-t border-brand-border/60 flex items-center justify-between text-xs text-amber-400">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Segera lakukan pembayaran sebelum batas waktu 2 jam berakhir.
                            </span>
                            <span class="font-bold">Batas: {{ $pesanan->batas_waktu_bayar?->format('H:i') }} WIB</span>
                        </div>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::SedangDikerjakan)
                        <div class="pt-3 border-t border-brand-border/60 flex items-center justify-between text-xs text-slate-400">
                            <span class="flex items-center gap-2 text-brand-cyan font-medium">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-cyan opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-cyan"></span>
                                </span>
                                Worker sedang aktif mengerjakan pesanan in-game Anda.
                            </span>
                        </div>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::Dikomplain)
                        <div class="pt-3 border-t border-brand-border/60 flex items-center justify-between text-xs text-rose-300">
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Komplain Anda sedang ditinjau oleh Admin.
                            </span>
                        </div>
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
                        <div class="pt-3 border-t border-brand-border/60 flex items-center justify-between text-xs text-amber-300">
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Worker sedang mengerjakan revisi perbaikan hasil joki.
                            </span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 rounded-3xl bg-brand-card/60 border border-brand-border text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-surface border border-brand-border flex items-center justify-center text-slate-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Tidak Ada Pesanan Aktif</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Saat ini Anda tidak memiliki pesanan yang sedang menunggu pembayaran atau dalam pengerjaan worker.</p>
                </div>
            @endforelse
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: RIWAYAT PESANAN                         -->
        <!-- ============================================== -->
        <div x-show="tab === 'riwayat'" x-cloak class="space-y-4">
            @forelse($pesananRiwayat as $pesanan)
                <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-5 sm:p-6 shadow-card hover:border-slate-600 transition-all opacity-85 hover:opacity-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-brand-border">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-black text-white">{{ $pesanan->kode_pesanan }}</span>
                            <span class="text-xs text-slate-500">• {{ $pesanan->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border self-start sm:self-auto {{ $pesanan->status->badgeColor() }}">
                            {{ $pesanan->status->label() }}
                        </span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $pesanan->layanan->poster_url }}" alt="{{ $pesanan->layanan->nama_layanan }}" class="w-14 h-14 rounded-2xl object-cover border border-brand-border shrink-0 grayscale">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $pesanan->layanan->game->nama_game }}</span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-300">{{ $pesanan->layanan->nama_layanan }}</h3>
                                <p class="text-xs text-slate-500">ID: {{ $pesanan->id_akun_game }} • Total: Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-slate-300 hover:text-white transition-colors self-end sm:self-auto">
                            <span>Lihat Invoice</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-12 rounded-3xl bg-brand-card/60 border border-brand-border text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-surface border border-brand-border flex items-center justify-center text-slate-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Belum Ada Riwayat Pesanan</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Pesanan yang selesai, dibatalkan, atau kedaluwarsa akan diarsipkan di sini.</p>
                </div>
            @endforelse

            <div class="pt-4">
                {{ $pesananRiwayat->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
