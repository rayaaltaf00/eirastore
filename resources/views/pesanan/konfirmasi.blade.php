@extends('layouts.storefront')

@section('content')
<div class="py-12 lg:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 text-xs font-bold uppercase tracking-wider mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Langkah Terakhir
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-white">Konfirmasi Draf Pesanan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">Periksa kembali rincian pemesanan Anda sebelum membuat pesanan resmi.</p>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
            
            <!-- Layanan & Game -->
            <div class="flex items-center gap-4 pb-6 border-b border-brand-border">
                <img src="{{ $layanan->poster_url }}" alt="{{ $layanan->nama_layanan }}" class="w-16 h-16 rounded-2xl object-cover border border-brand-border shrink-0">
                <div>
                    <span class="text-[11px] font-bold text-brand-cyan uppercase tracking-wider">{{ $layanan->game->nama_game }}</span>
                    <h2 class="text-lg font-bold text-white">{{ $layanan->nama_layanan }}</h2>
                    <span class="text-xs text-slate-400">Estimasi: {{ $layanan->estimasi_waktu ?? '1 - 2 Hari' }}</span>
                </div>
            </div>

            <!-- Rincian Item yang Dipesan -->
            <div class="space-y-3 pb-6 border-b border-brand-border">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Rincian Item Joki</h3>
                <div class="space-y-2">
                    @foreach($calcResult['items'] as $item)
                        <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-xl bg-brand-surface border border-brand-border">
                            <div>
                                <span class="font-bold text-white">{{ $item['nama_item'] }}</span>
                                <span class="text-[11px] text-slate-400 block">({{ $item['peran'] }})</span>
                            </div>
                            <span class="font-bold text-brand-cyan">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Data Akun Game (Disamarkan untuk keamanan) -->
            <div class="space-y-3 pb-6 border-b border-brand-border text-xs">
                <h3 class="font-bold text-white uppercase tracking-wider">Data Akun Game</h3>
                <div class="grid grid-cols-2 gap-3 bg-brand-surface p-4 rounded-2xl border border-brand-border">
                    <div>
                        <span class="text-slate-400 block">ID Akun Game:</span>
                        <span class="font-semibold text-white">{{ $draft['id_akun_game'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Metode Login:</span>
                        <span class="font-semibold text-white">{{ $draft['login_via'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Username / Email:</span>
                        <span class="font-semibold text-white">{{ $draft['username_akun_game'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Password:</span>
                        <span class="font-semibold text-slate-400">•••••••• (Tersimpan Aman)</span>
                    </div>
                    @if(!empty($draft['nickname_game']))
                        <div>
                            <span class="text-slate-400 block">Nickname:</span>
                            <span class="font-semibold text-white">{{ $draft['nickname_game'] }}</span>
                        </div>
                    @endif
                    @if(!empty($draft['zone_id']))
                        <div>
                            <span class="text-slate-400 block">Server / Zone:</span>
                            <span class="font-semibold text-white">{{ $draft['zone_id'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Total Akhir -->
            <div class="flex items-center justify-between py-2">
                <div>
                    <span class="text-xs text-slate-400 block">Total yang Harus Dibayar:</span>
                    <span class="text-2xl font-black text-brand-cyan">Rp {{ number_format($calcResult['total_harga'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex flex-col sm:flex-row items-center gap-3">
                <form action="{{ route('pesanan.konfirmasi-draf.proses') }}" method="POST" class="w-full sm:flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-bold shadow-glow-blue hover:opacity-95 transition-all text-center">
                        Konfirmasi & Buat Pesanan
                    </button>
                </form>

                <form action="{{ route('pesanan.konfirmasi-draf.batal') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-brand-surface border border-brand-border text-slate-400 hover:text-white text-sm font-semibold hover:border-slate-600 transition-colors">
                        Batalkan Draf
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
