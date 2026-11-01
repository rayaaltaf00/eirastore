{{--
    Halaman Formulir Pemesanan Joki (Storefront Order Form)
    - Menampilkan opsi konfigurasi pesanan dinamis (Tangga/Rank, Progres/Eksplorasi, atau Pilihan/Quest)
    - Menyediakan kalkulator estimasi harga instan berbasis Alpine.js (orderCalculator)
    - Mendukung Guest Checkout dengan pengisian form sebelum login dan pemulihan draf
--}}
@extends('layouts.storefront')

@section('content')
<div class="py-8 lg:py-12" x-data="orderCalculator()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb Nav -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('beranda') }}" class="hover:text-brand-cyan transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('game.show', $layanan->game) }}" class="hover:text-brand-cyan transition-colors">{{ $layanan->game->nama_game }}</a>
            <span>/</span>
            <span class="text-white font-medium">Pesan {{ $layanan->nama_layanan }}</span>
        </nav>

        <!-- Service Header Banner -->
        <div class="relative rounded-3xl overflow-hidden border border-brand-border bg-gradient-to-r from-brand-card/90 via-brand-surface/90 to-brand-card/90 p-6 sm:p-8 mb-8 shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <img src="{{ $layanan->gambar_url }}" alt="{{ $layanan->nama_layanan }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-brand-border shadow-card shrink-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 uppercase tracking-wider">
                                {{ $layanan->game->nama_game }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white/5 text-slate-300 border border-brand-border">
                                Tipe: {{ ucfirst($layanan->tipe_harga->value) }}
                            </span>
                            @if(! $layanan->butuh_login_game)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                    ✓ Joki Gendong / Mabar (Tanpa Password)
                                </span>
                            @endif
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-white">{{ $layanan->nama_layanan }}</h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl line-clamp-2">{{ $layanan->deskripsi }}</p>
                    </div>
                </div>

                @if($layanan->estimasi_waktu)
                    <div class="px-4 py-3 rounded-2xl bg-brand-surface border border-brand-border flex items-center gap-3 shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-brand-blue/15 border border-brand-blue/30 flex items-center justify-center text-brand-cyan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Estimasi Waktu</span>
                            <span class="text-xs sm:text-sm font-bold text-white">{{ $layanan->estimasi_waktu }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-brand-blue/10 border border-brand-blue/30 text-brand-cyan text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs space-y-1">
                <p class="font-bold text-sm mb-1">Periksa kembali data isian Anda:</p>
                @foreach($errors->all() as $err)
                    <p>• {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <!-- Form Pemesanan -->
        <form action="{{ route('pesanan.store', $layanan) }}" method="POST" id="order-form" data-loading data-loading-text="Memproses Pesanan...">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- SISI KIRI: Pilihan Layanan & Data Akun (Cols 8) -->
                <div class="lg:col-span-8 space-y-8">

                    <!-- ========================================== -->
                    <!-- 1. PILIHAN LAYANAN (BERDASARKAN TIPE HARGA) -->
                    <!-- ========================================== -->
                    <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                        <div class="flex items-center gap-3 pb-4 border-b border-brand-border">
                            <span class="w-8 h-8 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 flex items-center justify-center font-black text-sm">1</span>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-white">Konfigurasi Pilihan Joki</h2>
                                <p class="text-xs text-slate-400">Sesuaikan target pengerjaan joki yang Anda inginkan</p>
                            </div>
                        </div>

                        <!-- ────────────────────────────────────────── -->
                        <!-- TIPE 1: TANGGA (RANK / BINTANG)             -->
                        <!-- ────────────────────────────────────────── -->
                        @if($layanan->tipe_harga->value === 'tangga')
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <!-- Tingkat Awal -->
                                    <div class="p-5 rounded-2xl bg-brand-surface border border-brand-border space-y-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                                            <label class="text-xs font-bold text-white uppercase tracking-wider">Tingkat / Rank Saat Ini</label>
                                        </div>

                                        <div>
                                            <label for="tier_awal_id" class="block text-[11px] text-slate-400 mb-1.5 font-medium">Pilih Rank Awal</label>
                                            <select id="tier_awal_id" name="tier_awal_id" x-model="tierAwalId" @change="updateTierAwal()" required
                                                    class="w-full px-3.5 py-2.5 rounded-xl bg-brand-card border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                                @foreach($itemsUtama as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->nama }} (Rp {{ number_format($item->harga, 0, ',', '.') }}/★)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label for="bintang_awal" class="text-[11px] text-slate-400 font-medium">
                                                    Jumlah Bintang Saat Ini
                                                </label>
                                                <span class="text-[11px] text-slate-400">
                                                    Maks: <strong class="text-brand-cyan" x-text="tierAwalMaks + ' ★'"></strong>
                                                </span>
                                            </div>

                                            <!-- Input Jumlah Bintang Awal (Ketik Langsung & Tombol +/-) -->
                                            <div class="flex items-center gap-2">
                                                <button type="button" 
                                                        @click="bintangAwal = Math.max(0, (parseInt(bintangAwal) || 0) - 1)"
                                                        :disabled="bintangAwal <= 0"
                                                        class="w-10 h-10 rounded-xl bg-brand-card border border-brand-border text-slate-300 hover:text-white hover:border-brand-cyan disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center font-bold text-lg transition-all shrink-0">
                                                    -
                                                </button>
                                                <div class="relative flex-1">
                                                    <input type="number" id="bintang_awal" name="bintang_awal" 
                                                           min="0" :max="tierAwalMaks" 
                                                           x-model.number="bintangAwal"
                                                           @input="if (bintangAwal < 0) bintangAwal = 0; if (bintangAwal > tierAwalMaks) bintangAwal = tierAwalMaks;"
                                                           placeholder="0"
                                                           class="w-full text-center font-black text-base py-2 px-3 rounded-xl bg-brand-card border border-brand-border text-brand-cyan focus:border-brand-cyan focus:outline-none">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500 pointer-events-none">★</span>
                                                </div>
                                                <button type="button" 
                                                        @click="bintangAwal = Math.min(tierAwalMaks, (parseInt(bintangAwal) || 0) + 1)"
                                                        :disabled="bintangAwal >= tierAwalMaks"
                                                        class="w-10 h-10 rounded-xl bg-brand-card border border-brand-border text-slate-300 hover:text-white hover:border-brand-cyan disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center font-bold text-lg transition-all shrink-0">
                                                    +
                                                </button>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1.5">
                                                Ketik langsung atau klik +/- (0 s/d <span x-text="tierAwalMaks"></span> ★)
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Tingkat Tujuan -->
                                    <div class="p-5 rounded-2xl bg-brand-surface border border-brand-border space-y-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-brand-cyan animate-pulse"></span>
                                            <label class="text-xs font-bold text-white uppercase tracking-wider">Tingkat / Rank Target Tujuan</label>
                                        </div>

                                        <div>
                                            <label for="tier_tujuan_id" class="block text-[11px] text-slate-400 mb-1.5 font-medium">Pilih Rank Tujuan</label>
                                            <select id="tier_tujuan_id" name="tier_tujuan_id" x-model="tierTujuanId" @change="updateTierTujuan()" required
                                                    class="w-full px-3.5 py-2.5 rounded-xl bg-brand-card border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                                @foreach($itemsUtama as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->nama }} (Rp {{ number_format($item->harga, 0, ',', '.') }}/★)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label for="bintang_tujuan" class="text-[11px] text-slate-400 font-medium">
                                                    Target Jumlah Bintang
                                                </label>
                                                <span class="text-[11px] text-slate-400">
                                                    Maks: <strong class="text-brand-cyan" x-text="tierTujuanMaks + ' ★'"></strong>
                                                </span>
                                            </div>

                                            <!-- Input Jumlah Bintang Tujuan (Ketik Langsung & Tombol +/-) -->
                                            <div class="flex items-center gap-2">
                                                <button type="button" 
                                                        @click="bintangTujuan = Math.max(1, (parseInt(bintangTujuan) || 1) - 1)"
                                                        :disabled="bintangTujuan <= 1"
                                                        class="w-10 h-10 rounded-xl bg-brand-card border border-brand-border text-slate-300 hover:text-white hover:border-brand-cyan disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center font-bold text-lg transition-all shrink-0">
                                                    -
                                                </button>
                                                <div class="relative flex-1">
                                                    <input type="number" id="bintang_tujuan" name="bintang_tujuan" 
                                                           min="1" :max="tierTujuanMaks" 
                                                           x-model.number="bintangTujuan"
                                                           @input="if (bintangTujuan < 1) bintangTujuan = 1; if (bintangTujuan > tierTujuanMaks) bintangTujuan = tierTujuanMaks;"
                                                           placeholder="1"
                                                           class="w-full text-center font-black text-base py-2 px-3 rounded-xl bg-brand-card border border-brand-border text-brand-cyan focus:border-brand-cyan focus:outline-none">
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500 pointer-events-none">★</span>
                                                </div>
                                                <button type="button" 
                                                        @click="bintangTujuan = Math.min(tierTujuanMaks, (parseInt(bintangTujuan) || 1) + 1)"
                                                        :disabled="bintangTujuan >= tierTujuanMaks"
                                                        class="w-10 h-10 rounded-xl bg-brand-card border border-brand-border text-slate-300 hover:text-white hover:border-brand-cyan disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center font-bold text-lg transition-all shrink-0">
                                                    +
                                                </button>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1.5">
                                                Ketik langsung atau klik +/- (1 s/d <span x-text="tierTujuanMaks"></span> ★)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pesan Peringatan Jika Tujuan Tidak Valid -->
                                <div x-show="!isTanggaValid()" class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs flex items-center gap-2.5">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span x-text="tanggaErrorMsg"></span>
                                </div>
                            </div>
                        @endif

                        <!-- ────────────────────────────────────────── -->
                        <!-- TIPE 2: PROGRES (EKSPLORASI REGION/SUB)    -->
                        <!-- ────────────────────────────────────────── -->
                        @if($layanan->tipe_harga->value === 'progres')
                            <div class="space-y-6">
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    Pilih region di bawah ini, centang sub-wilayah yang ingin Anda joki, lalu atur slider progres akun Anda saat ini. Biaya dihitung adil berdasarkan sisa persentase pengerjaan:
                                </p>

                                @if($regions->isNotEmpty())
                                    <!-- Region Tabs Navigation -->
                                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                                        @foreach($regions as $reg)
                                            <button type="button"
                                                    @click="activeRegionId = '{{ $reg->id }}'"
                                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2 border cursor-pointer"
                                                    :class="String(activeRegionId) === '{{ $reg->id }}' ? 'bg-brand-blue text-white border-brand-cyan shadow-glow-blue' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white hover:bg-brand-surface/80'">
                                                <span>{{ $reg->nama }}</span>
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black"
                                                      :class="countSelectedInRegion('{{ $reg->id }}') > 0 ? 'bg-brand-cyan text-brand-dark' : 'bg-white/10 text-slate-400'"
                                                      x-text="countSelectedInRegion('{{ $reg->id }}')">
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Region Content Panes -->
                                    @foreach($regions as $reg)
                                        <div x-show="String(activeRegionId) === '{{ $reg->id }}'" class="space-y-4">
                                            <!-- Region Toolbar -->
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-brand-surface border border-brand-border">
                                                <div>
                                                    <span class="text-sm font-bold text-white block">{{ $reg->nama }}</span>
                                                    <span class="text-xs text-slate-400">{{ $reg->sub_wilayah->count() }} Sub-Wilayah Tersedia</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="selectAllInRegion('{{ $reg->id }}')" class="px-3 py-1.5 rounded-xl bg-brand-card border border-brand-border text-xs font-semibold text-brand-cyan hover:text-white transition-colors cursor-pointer">
                                                        Pilih Semua
                                                    </button>
                                                    <button type="button" @click="deselectAllInRegion('{{ $reg->id }}')" class="px-3 py-1.5 rounded-xl bg-brand-card border border-brand-border text-xs font-semibold text-slate-400 hover:text-rose-400 transition-colors cursor-pointer">
                                                        Batal Pilih
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Sub-Wilayah Grid -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                @foreach($reg->sub_wilayah as $sub)
                                                    <div class="p-4 rounded-2xl border transition-all"
                                                         :class="progresSelected['{{ $sub->id }}'] ? 'bg-brand-surface border-brand-cyan shadow-glow-blue/20' : 'bg-brand-surface/40 border-brand-border opacity-75 hover:opacity-100'">
                                                        
                                                        <div class="flex items-start justify-between gap-3 mb-3">
                                                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                                                <input type="checkbox" name="progres_selected[]" value="{{ $sub->id }}"
                                                                       x-model="progresSelected['{{ $sub->id }}']"
                                                                       @change="onToggleProgresItem('{{ $sub->id }}', {{ $sub->harga }})"
                                                                       class="w-4 h-4 rounded bg-brand-card border-brand-border text-brand-cyan focus:ring-brand-cyan cursor-pointer">
                                                                <div>
                                                                    <span class="text-xs font-bold text-white block">{{ $sub->nama }}</span>
                                                                    <span class="text-[11px] text-slate-400">Biaya 100%: Rp {{ number_format($sub->harga, 0, ',', '.') }}</span>
                                                                </div>
                                                            </label>

                                                            <span class="text-xs font-bold text-brand-cyan whitespace-nowrap"
                                                                  x-show="progresSelected['{{ $sub->id }}']"
                                                                  x-text="formatRupiah(calculateSubItemCost('{{ $sub->id }}', {{ $sub->harga }}))">
                                                            </span>
                                                        </div>

                                                        <!-- Input & Slider Progres Akun (Aktif saat dicentang) -->
                                                        <div x-show="progresSelected['{{ $sub->id }}']" class="pt-3 border-t border-brand-border/60 space-y-2.5">
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="text-slate-400 font-medium">Progres Akun Saat Ini:</span>
                                                                <div class="flex items-center gap-1.5">
                                                                    <input type="number" name="progres[{{ $sub->id }}]" min="0" max="95" step="1"
                                                                           x-model.number="progres['{{ $sub->id }}']"
                                                                           :disabled="!progresSelected['{{ $sub->id }}']"
                                                                           @input="if (progres['{{ $sub->id }}'] < 0) progres['{{ $sub->id }}'] = 0; if (progres['{{ $sub->id }}'] > 95) progres['{{ $sub->id }}'] = 95;"
                                                                           class="w-16 px-2 py-1 text-center font-black text-xs rounded-lg bg-brand-card border border-brand-border text-brand-cyan focus:border-brand-cyan focus:outline-none">
                                                                    <span class="text-brand-cyan font-bold text-xs">%</span>
                                                                </div>
                                                            </div>

                                                            <input type="range" min="0" max="95" step="1"
                                                                   x-model.number="progres['{{ $sub->id }}']"
                                                                   :disabled="!progresSelected['{{ $sub->id }}']"
                                                                   class="w-full h-2 bg-brand-border rounded-lg appearance-none cursor-pointer accent-brand-cyan">

                                                            <div class="flex items-center justify-between text-[10px] text-slate-500">
                                                                <span>0%</span>
                                                                <span class="text-slate-300 font-medium">
                                                                    Sisa Joki: <strong class="text-white" x-text="(100 - (progres['{{ $sub->id }}'] || 0)) + '%'"></strong>
                                                                </span>
                                                                <span>95%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Fallback Flat Items Jika Tidak Ada Hierarki Region -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($itemsUtama as $item)
                                            @if($item->harga > 0)
                                                <div class="p-4 rounded-2xl border transition-all"
                                                     :class="progresSelected['{{ $item->id }}'] ? 'bg-brand-surface border-brand-cyan shadow-glow-blue/20' : 'bg-brand-surface/40 border-brand-border opacity-75 hover:opacity-100'">
                                                    <div class="flex items-start justify-between gap-3 mb-3">
                                                        <label class="flex items-center gap-3 cursor-pointer select-none">
                                                            <input type="checkbox" name="progres_selected[]" value="{{ $item->id }}"
                                                                   x-model="progresSelected['{{ $item->id }}']"
                                                                   @change="onToggleProgresItem('{{ $item->id }}', {{ $item->harga }})"
                                                                   class="w-4 h-4 rounded bg-brand-card border-brand-border text-brand-cyan focus:ring-brand-cyan cursor-pointer">
                                                            <div>
                                                                <span class="text-xs font-bold text-white block">{{ $item->nama }}</span>
                                                                <span class="text-[11px] text-slate-400">Biaya 100%: Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                                            </div>
                                                        </label>
                                                        <span class="text-xs font-bold text-brand-cyan whitespace-nowrap"
                                                              x-show="progresSelected['{{ $item->id }}']"
                                                              x-text="formatRupiah(calculateSubItemCost('{{ $item->id }}', {{ $item->harga }}))">
                                                        </span>
                                                    </div>

                                                    <div x-show="progresSelected['{{ $item->id }}']" class="pt-3 border-t border-brand-border/60 space-y-2">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-slate-400">Progres Akun Saat Ini:</span>
                                                            <span class="font-extrabold text-brand-cyan">
                                                                <span x-text="progres['{{ $item->id }}'] || 0"></span>%
                                                            </span>
                                                        </div>
                                                        <input type="range" name="progres[{{ $item->id }}]" min="0" max="95" step="5"
                                                               x-model.number="progres['{{ $item->id }}']"
                                                               :disabled="!progresSelected['{{ $item->id }}']"
                                                               class="w-full h-2 bg-brand-border rounded-lg appearance-none cursor-pointer accent-brand-cyan">
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Indikator Pemilihan Minimal -->
                                <div x-show="totalSelectedProgresCount() === 0" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs flex items-center gap-2.5">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Silakan centang minimal satu sub-wilayah yang ingin Anda joki untuk melanjutkan pesanan.</span>
                                </div>
                            </div>
                        @endif

                        <!-- ────────────────────────────────────────── -->
                        <!-- TIPE 3: PILIHAN (PAKET / MATCH / ITEM)      -->
                        <!-- ────────────────────────────────────────── -->
                        @if($layanan->tipe_harga->value === 'pilihan')
                            <div class="space-y-5">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <p class="text-xs text-slate-400">Pilih salah satu atau beberapa paket / quest yang Anda butuhkan:</p>
                                    @if(isset($categories) && $categories->count() > 1)
                                        <div class="flex items-center gap-2 text-[11px]">
                                            <button type="button" @click="selectAllInCategory(activeCategory)" class="text-brand-cyan hover:underline font-semibold transition-colors">
                                                Pilih Semua
                                            </button>
                                            <span class="text-slate-600">•</span>
                                            <button type="button" @click="deselectAllInCategory(activeCategory)" class="text-slate-400 hover:text-white transition-colors">
                                                Batal Semua
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                @if(isset($categories) && $categories->count() > 1)
                                    <!-- TAB FILTER KATEGORI (Archon Quest, World Quest, Story Quest, dll) -->
                                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                                        <button type="button" 
                                                @click="activeCategory = 'Semua'"
                                                :class="activeCategory === 'Semua' ? 'bg-gradient-to-r from-brand-blue to-brand-cyan text-white shadow-glow-blue' : 'bg-brand-surface border border-brand-border text-slate-400 hover:text-white'"
                                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
                                            <span>Semua Pilihan</span>
                                            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-black/30 text-brand-cyan font-mono" 
                                                  x-text="countSelectedInCategory('Semua')">0</span>
                                        </button>
                                        @foreach($categories as $cat)
                                            <button type="button" 
                                                    @click="activeCategory = '{{ $cat }}'"
                                                    :class="activeCategory === '{{ $cat }}' ? 'bg-gradient-to-r from-brand-blue to-brand-cyan text-white shadow-glow-blue' : 'bg-brand-surface border border-brand-border text-slate-400 hover:text-white'"
                                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-2">
                                                <span>{{ $cat }}</span>
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-black/30 text-brand-cyan font-mono" 
                                                      x-text="countSelectedInCategory('{{ $cat }}')">0</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($itemsUtama as $item)
                                        <div class="p-4 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between"
                                             x-show="activeCategory === 'Semua' || activeCategory === '{{ $item->kategori }}'"
                                             :class="selectedItems['{{ $item->id }}'] ? 'bg-brand-blue/10 border-brand-cyan/50 shadow-glow-blue' : 'bg-brand-surface border-brand-border hover:border-slate-600'"
                                             @click="toggleItem('{{ $item->id }}', {{ $item->harga }})">
                                            
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-start gap-3">
                                                    <input type="checkbox" :checked="selectedItems['{{ $item->id }}']" 
                                                           class="w-4 h-4 mt-0.5 rounded bg-brand-card border-brand-border text-brand-blue focus:ring-brand-cyan cursor-pointer">
                                                    <div>
                                                        <span class="text-xs font-bold text-white block leading-relaxed">{{ $item->nama }}</span>
                                                        @if($item->kategori)
                                                            <span class="inline-flex items-center px-2 py-0.5 mt-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                                                {{ str_contains($item->kategori, 'Archon') ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20' : '' }}
                                                                {{ str_contains($item->kategori, 'World') ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : '' }}
                                                                {{ str_contains($item->kategori, 'Story') ? 'bg-indigo-500/10 text-indigo-300 border border-indigo-500/20' : '' }}
                                                                {{ !str_contains($item->kategori, 'Archon') && !str_contains($item->kategori, 'World') && !str_contains($item->kategori, 'Story') ? 'bg-slate-800 text-slate-300 border border-slate-700' : '' }}">
                                                                {{ $item->kategori }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-xs font-bold text-brand-cyan shrink-0 whitespace-nowrap">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                            </div>

                                            @if($item->nilai_maks && $item->nilai_maks > 1)
                                                <div class="mt-3 pt-3 border-t border-brand-border/60 flex items-center justify-between" @click.stop>
                                                    <span class="text-[11px] text-slate-400">Jumlah ({{ $item->satuan ?? 'x' }}):</span>
                                                    <input type="number" name="items[{{ $item->id }}]" min="1" max="{{ $item->nilai_maks }}"
                                                           x-model.number="itemQty['{{ $item->id }}']"
                                                           :disabled="!selectedItems['{{ $item->id }}']"
                                                           class="w-20 px-2 py-1 rounded-lg bg-brand-card border border-brand-border text-white text-xs text-center focus:border-brand-cyan focus:outline-none disabled:opacity-40">
                                                </div>
                                            @else
                                                <input type="hidden" name="items[{{ $item->id }}]" value="1" :disabled="!selectedItems['{{ $item->id }}']">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Item Tambahan Opsional (Hero Request dsb) -->
                        @if($itemsTambahan->isNotEmpty())
                            <div class="pt-6 border-t border-brand-border space-y-3">
                                <label class="block text-xs font-bold text-white uppercase tracking-wider">Fasilitas Tambahan (Opsional)</label>
                                <div class="space-y-2">
                                    @foreach($itemsTambahan as $tambahan)
                                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-brand-surface border border-brand-border cursor-pointer hover:border-slate-600 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="item_tambahan_ids[]" value="{{ $tambahan->id }}"
                                                       @change="toggleTambahan('{{ $tambahan->id }}', {{ $tambahan->harga }})"
                                                       class="w-4 h-4 rounded bg-brand-card border-brand-border text-brand-blue focus:ring-brand-cyan">
                                                <span class="text-xs font-medium text-slate-200">{{ $tambahan->nama }}</span>
                                            </div>
                                            <span class="text-xs font-bold text-slate-300">+ Rp {{ number_format($tambahan->harga, 0, ',', '.') }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- ========================================== -->
                    <!-- 2. DATA AKUN GAME / DATA MABAR             -->
                    <!-- ========================================== -->
                    @if(! $layanan->butuh_login_game)
                        <!-- TAMPILAN KHUSUS JOKI GENDONG / MABAR PRO (TANPA PASSWORD) -->
                        <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                            <div class="flex items-center gap-3 pb-4 border-b border-brand-border">
                                <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-black text-sm">2</span>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-white">Data Akun Mabar / Party</h2>
                                    <p class="text-xs text-slate-400">Layanan Gendong tidak memerlukan email & password. Cukup lengkapi data profil game dan kontak WhatsApp Anda.</p>
                                </div>
                            </div>

                            <!-- Banner Informasi Keamanan Mabar -->
                            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-start gap-3">
                                <svg class="w-5 h-5 shrink-0 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <div>
                                    <span class="font-bold block text-white mb-0.5">Akun Anda 100% Aman di Tangan Anda Sendiri</span>
                                    <span>Worker kami akan mengundang akun Anda langsung ke dalam lobby room game. Kami tidak akan pernah meminta kata sandi / password akun Anda untuk layanan gendong ini.</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- ID Akun Game -->
                                <div>
                                    <label for="id_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        User ID Game <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" id="id_akun_game" name="id_akun_game" value="{{ old('id_akun_game', $draftData['id_akun_game'] ?? '') }}" required
                                           placeholder="Contoh: 12345678"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>

                                <!-- Server / Zone ID -->
                                <div>
                                    <label for="zone_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Server / Zone ID (Opsional)
                                    </label>
                                    <input type="text" id="zone_id" name="zone_id" value="{{ old('zone_id', $draftData['zone_id'] ?? '') }}"
                                           placeholder="Contoh: 2024 atau Asia"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nickname Game -->
                                <div>
                                    <label for="nickname_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Nickname Dalam Game <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" id="nickname_game" name="nickname_game" value="{{ old('nickname_game', $draftData['nickname_game'] ?? '') }}" required
                                           placeholder="Contoh: EiraGamer"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Diperlukan agar Worker mengenali dan mengundang akun Anda.</span>
                                </div>

                                <!-- No WhatsApp Customer -->
                                <div>
                                    <label for="no_wa_customer" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Nomor WhatsApp Aktif <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="tel" id="no_wa_customer" name="no_wa_customer" value="{{ old('no_wa_customer', $draftData['no_wa_customer'] ?? (auth()->user()?->no_hp ?? '')) }}" required
                                           placeholder="Contoh: 081234567890"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Format Indonesia untuk koordinasi jadwal mabar bersama Worker.</span>
                                </div>
                            </div>

                            <!-- Catatan Mabar / Jadwal -->
                            <div>
                                <label for="catatan_customer" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Jadwal / Jam Kosong Mabar & Catatan Lain (Opsional)
                                </label>
                                <textarea id="catatan_customer" name="catatan_customer" rows="2"
                                          placeholder="Contoh: Siap mabar jam 19.30 - 22.00 WIB malam ini. Role utama roamer / mid."
                                          class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">{{ old('catatan_customer', $draftData['catatan_customer'] ?? '') }}</textarea>
                            </div>
                        </div>

                    @else
                        <!-- TAMPILAN NORMAL JOKI AKUN (LOGIN LANGSUNG OLEH WORKER) -->
                        <div class="bg-brand-card/90 border border-brand-border rounded-3xl p-6 sm:p-8 shadow-card space-y-6">
                            <div class="flex items-center gap-3 pb-4 border-b border-brand-border">
                                <span class="w-8 h-8 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 flex items-center justify-center font-black text-sm">2</span>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-white">Data Akun Game</h2>
                                    <p class="text-xs text-slate-400">Kredensial disimpan terenkripsi dengan AES-256 dan hanya dapat dibaca oleh Worker yang bertugas</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- ID Akun Game -->
                                <div>
                                    <label for="id_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        ID Akun Game <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" id="id_akun_game" name="id_akun_game" value="{{ old('id_akun_game', $draftData['id_akun_game'] ?? '') }}" required
                                           placeholder="Contoh: 12345678"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>

                                <!-- Server / Zone ID -->
                                <div>
                                    <label for="zone_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Server / Zone ID (Opsional)
                                    </label>
                                    <input type="text" id="zone_id" name="zone_id" value="{{ old('zone_id', $draftData['zone_id'] ?? '') }}"
                                           placeholder="Contoh: 2024 atau Asia"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Login Via (Dinamis Sesuai Game) -->
                                <div>
                                    <label for="login_via" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Metode Login Akun <span class="text-rose-400">*</span>
                                    </label>
                                    <select id="login_via" name="login_via" required
                                            class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                        @php
                                            $opsiLogin = $layanan->game->opsi_login;
                                            $selectedLogin = old('login_via', $draftData['login_via'] ?? ($opsiLogin[0] ?? ''));
                                        @endphp
                                        @foreach($opsiLogin as $opsi)
                                            <option value="{{ $opsi }}" {{ $selectedLogin === $opsi ? 'selected' : '' }}>
                                                {{ $opsi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Nickname Game -->
                                <div>
                                    <label for="nickname_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Nickname Dalam Game (Opsional)
                                    </label>
                                    <input type="text" id="nickname_game" name="nickname_game" value="{{ old('nickname_game', $draftData['nickname_game'] ?? '') }}"
                                           placeholder="Contoh: EiraGamer"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Username / Email Akun -->
                                <div>
                                    <label for="username_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Username / Email Login <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" id="username_akun_game" name="username_akun_game" value="{{ old('username_akun_game', $draftData['username_akun_game'] ?? '') }}" required
                                           placeholder="Email atau username akun game"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>

                                <!-- Password Akun Game -->
                                <div x-data="{ showPass: false }">
                                    <label for="password_akun_game" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Password Akun Game <span class="text-rose-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showPass ? 'text' : 'password'" id="password_akun_game" name="password_akun_game" value="{{ old('password_akun_game', $draftData['password_akun_game'] ?? '') }}" required
                                               placeholder="Password akun game Anda"
                                               class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- No WhatsApp Customer -->
                                <div>
                                    <label for="no_wa_customer" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Nomor WhatsApp Aktif <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="tel" id="no_wa_customer" name="no_wa_customer" value="{{ old('no_wa_customer', $draftData['no_wa_customer'] ?? (auth()->user()?->no_hp ?? '')) }}" required
                                           placeholder="Contoh: 081234567890"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Format Indonesia untuk Worker konfirmasi 2FA / update pesanan.</span>
                                </div>

                                <!-- Catatan Login & 2FA -->
                                <div>
                                    <label for="catatan_login" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                        Catatan Login / Kode 2FA (Opsional)
                                    </label>
                                    <input type="text" id="catatan_login" name="catatan_login" value="{{ old('catatan_login', $draftData['catatan_login'] ?? '') }}"
                                           placeholder="Contoh: Verifikasi WhatsApp aktif"
                                           class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">
                                </div>
                            </div>

                            <!-- Catatan Worker -->
                            <div>
                                <label for="catatan_customer" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Catatan Khusus Worker (Opsional)
                                </label>
                                <textarea id="catatan_customer" name="catatan_customer" rows="2"
                                          placeholder="Contoh: Request hero Ling / Fanny, jangan gunakan diamond"
                                          class="w-full px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none">{{ old('catatan_customer', $draftData['catatan_customer'] ?? '') }}</textarea>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- SISI KANAN: Ringkasan & Tombol Checkout (Cols 4 Sticky) -->
                <div class="lg:col-span-4 lg:sticky lg:top-24 space-y-6">
                    
                    <div class="bg-brand-card/95 border border-brand-border rounded-3xl p-6 shadow-card space-y-6">
                        <h3 class="text-base font-bold text-white flex items-center justify-between pb-3 border-b border-brand-border">
                            <span>Estimasi Total</span>
                            <span class="text-xs font-semibold text-slate-400">Otomatis Terhitung</span>
                        </h3>

                        <!-- Rincian Item Alpine Live -->
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between text-slate-400">
                                <span>Layanan:</span>
                                <span class="font-bold text-white text-right">{{ $layanan->nama_layanan }}</span>
                            </div>

                            <div class="flex justify-between text-slate-400">
                                <span>Estimasi Pengerjaan:</span>
                                <span class="font-bold text-slate-200">{{ $layanan->estimasi_waktu ?? '1 - 2 Hari' }}</span>
                            </div>

                            <div class="pt-3 border-t border-brand-border/60 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Subtotal Item:</span>
                                    <span class="font-bold text-white" x-text="formatRupiah(subtotalUtama)"></span>
                                </div>

                                <div class="flex items-center justify-between" x-show="subtotalTambahan > 0">
                                    <span class="text-slate-400">Item Tambahan:</span>
                                    <span class="font-bold text-white" x-text="formatRupiah(subtotalTambahan)"></span>
                                </div>
                            </div>

                            <!-- Rincian Item Pilihan Terpilih (Khusus Tipe Pilihan/Quest/Abyss) -->
                            <template x-if="tipeHarga === 'pilihan' && selectedItemsList.length > 0">
                                <div class="pt-3 border-t border-brand-border/60 space-y-2">
                                    <span class="text-[11px] font-semibold text-slate-400 block">Item/Quest Dipilih (<span x-text="selectedItemsList.length"></span>):</span>
                                    <div class="max-h-36 overflow-y-auto space-y-1 pr-1 text-[11px] scrollbar-thin">
                                        <template x-for="item in selectedItemsList" :key="item.id">
                                            <div class="flex items-center justify-between text-slate-300 bg-brand-surface/70 px-2.5 py-1.5 rounded-lg border border-brand-border/40">
                                                <div class="truncate max-w-[170px]">
                                                    <span class="font-medium text-white block truncate" x-text="item.nama"></span>
                                                    <span class="text-[10px] text-slate-400" x-text="item.kategori || 'Paket'"></span>
                                                </div>
                                                <span class="font-bold text-brand-cyan shrink-0 ml-2" x-text="formatRupiah(item.subtotal)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Total Akhir -->
                            <div class="pt-4 border-t border-brand-border flex items-baseline justify-between">
                                <span class="text-sm font-bold text-slate-300">Total Biaya:</span>
                                <span class="text-2xl font-black text-brand-cyan" x-text="formatRupiah(totalHarga)"></span>
                            </div>
                        </div>

                        <!-- Proteksi Info -->
                        <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border text-[11px] text-slate-400 space-y-1">
                            <p class="flex items-center gap-1.5 text-slate-300 font-semibold">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Garansi Akun 100% Aman
                            </p>
                            <p>Kredensial Anda dienkripsi AES-256 dan otomatis dihapus saat pesanan selesai.</p>
                        </div>

                        <!-- Tombol Submit -->
                        <button type="submit" :disabled="!isFormValid()"
                                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-bold shadow-glow-blue hover:opacity-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            @auth
                                Lanjut ke Pembayaran
                            @else
                                Pesan Sekarang (Masuk Akun)
                            @endauth
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
function orderCalculator() {
    const rawProgres = @json($draftData['progres'] ?? []);
    const initialSelectedProgres = {};
    for (const key in rawProgres) {
        initialSelectedProgres[key] = true;
    }

    const regionsData = @json($regions ?? []);

    return {
        tipeHarga: '{{ $layanan->tipe_harga->value }}',
        itemsUtama: @json($itemsUtama),
        regions: regionsData,
        activeRegionId: '{{ $regions->first()->id ?? '' }}',

        // Tangga
        tierAwalId: '{{ $draftData['tier_awal_id'] ?? ($itemsUtama->first()->id ?? '') }}',
        bintangAwal: {{ (int) ($draftData['bintang_awal'] ?? 0) }},
        tierTujuanId: '{{ $draftData['tier_tujuan_id'] ?? ($itemsUtama->last()->id ?? '') }}',
        bintangTujuan: {{ (int) ($draftData['bintang_tujuan'] ?? ($itemsUtama->last()->nilai_maks ?? 1)) }},
        tierAwalMaks: {{ (int) ($itemsUtama->first()->nilai_maks ?? 5) }},
        tierTujuanMaks: {{ (int) ($itemsUtama->last()->nilai_maks ?? 5) }},
        tanggaErrorMsg: '',

        // Progres
        progres: rawProgres,
        progresSelected: initialSelectedProgres,

        // Pilihan
        activeCategory: 'Semua',
        selectedItems: @json(isset($draftData['items']) ? array_fill_keys(array_keys($draftData['items']), true) : ($itemsUtama->first() ? [$itemsUtama->first()->id => true] : [])),
        itemQty: @json($draftData['items'] ?? ($itemsUtama->first() ? [$itemsUtama->first()->id => 1] : [])),

        // Tambahan
        tambahanTotal: 0,
        selectedTambahan: {},

        init() {
            if (this.tipeHarga === 'tangga') {
                this.updateTierAwal();
                this.updateTierTujuan();
            }
        },

        updateTierAwal() {
            const item = this.itemsUtama.find(i => String(i.id) === String(this.tierAwalId));
            if (item) {
                this.tierAwalMaks = item.nilai_maks || 5;
                if (this.bintangAwal > this.tierAwalMaks) {
                    this.bintangAwal = this.tierAwalMaks;
                }
            }
        },

        updateTierTujuan() {
            const item = this.itemsUtama.find(i => String(i.id) === String(this.tierTujuanId));
            if (item) {
                this.tierTujuanMaks = item.nilai_maks || 5;
                if (this.bintangTujuan > this.tierTujuanMaks) {
                    this.bintangTujuan = this.tierTujuanMaks;
                }
            }
        },

        isTanggaValid() {
            if (this.tipeHarga !== 'tangga') return true;
            const awal = this.itemsUtama.find(i => String(i.id) === String(this.tierAwalId));
            const tujuan = this.itemsUtama.find(i => String(i.id) === String(this.tierTujuanId));
            if (!awal || !tujuan) return false;

            if (tujuan.urutan < awal.urutan) {
                this.tanggaErrorMsg = 'Rank tujuan harus lebih tinggi dari rank saat ini.';
                return false;
            }
            if (tujuan.urutan === awal.urutan && this.bintangTujuan <= this.bintangAwal) {
                this.tanggaErrorMsg = 'Target bintang harus lebih banyak dari bintang saat ini.';
                return false;
            }
            this.tanggaErrorMsg = '';
            return true;
        },

        // Helper Progres
        countSelectedInRegion(regId) {
            const reg = this.regions.find(r => String(r.id) === String(regId));
            if (!reg || !reg.sub_wilayah) return 0;
            return reg.sub_wilayah.filter(sub => !!this.progresSelected[sub.id]).length;
        },

        selectAllInRegion(regId) {
            const reg = this.regions.find(r => String(r.id) === String(regId));
            if (!reg || !reg.sub_wilayah) return;
            reg.sub_wilayah.forEach(sub => {
                this.progresSelected[sub.id] = true;
                if (this.progres[sub.id] === undefined) {
                    this.progres[sub.id] = 0;
                }
            });
        },

        deselectAllInRegion(regId) {
            const reg = this.regions.find(r => String(r.id) === String(regId));
            if (!reg || !reg.sub_wilayah) return;
            reg.sub_wilayah.forEach(sub => {
                delete this.progresSelected[sub.id];
            });
        },

        onToggleProgresItem(itemId, harga) {
            if (this.progresSelected[itemId] && this.progres[itemId] === undefined) {
                this.progres[itemId] = 0;
            }
        },

        calculateSubItemCost(itemId, harga) {
            const p = parseFloat(this.progres[itemId] || 0);
            const sisa = Math.max(0, 100 - p);
            return Math.round((sisa / 100) * parseFloat(harga));
        },

        totalSelectedProgresCount() {
            let count = 0;
            for (const id in this.progresSelected) {
                if (this.progresSelected[id]) count++;
            }
            return count;
        },

        // Helper Pilihan (Quest, Paket, Story)
        countSelectedInCategory(kategori) {
            return this.itemsUtama.filter(i => {
                if (!this.selectedItems[i.id]) return false;
                if (kategori === 'Semua') return true;
                return i.kategori === kategori;
            }).length;
        },

        selectAllInCategory(kategori) {
            this.itemsUtama.forEach(i => {
                if (kategori === 'Semua' || i.kategori === kategori) {
                    this.selectedItems[i.id] = true;
                    if (!this.itemQty[i.id]) {
                        this.itemQty[i.id] = 1;
                    }
                }
            });
        },

        deselectAllInCategory(kategori) {
            this.itemsUtama.forEach(i => {
                if (kategori === 'Semua' || i.kategori === kategori) {
                    this.selectedItems[i.id] = false;
                }
            });
        },

        get selectedItemsList() {
            return this.itemsUtama
                .filter(i => !!this.selectedItems[i.id])
                .map(i => {
                    const qty = this.itemQty[i.id] || 1;
                    return {
                        id: i.id,
                        nama: i.nama,
                        kategori: i.kategori,
                        qty: qty,
                        harga: parseFloat(i.harga),
                        subtotal: qty * parseFloat(i.harga)
                    };
                });
        },

        toggleItem(id, harga) {
            this.selectedItems[id] = !this.selectedItems[id];
            if (!this.itemQty[id]) {
                this.itemQty[id] = 1;
            }
        },

        toggleTambahan(id, harga) {
            if (this.selectedTambahan[id]) {
                delete this.selectedTambahan[id];
            } else {
                this.selectedTambahan[id] = harga;
            }
        },

        get subtotalUtama() {
            if (this.tipeHarga === 'tangga') {
                if (!this.isTanggaValid()) return 0;
                const awal = this.itemsUtama.find(i => String(i.id) === String(this.tierAwalId));
                const tujuan = this.itemsUtama.find(i => String(i.id) === String(this.tierTujuanId));
                if (!awal || !tujuan) return 0;

                if (awal.id === tujuan.id) {
                    return Math.max(0, this.bintangTujuan - this.bintangAwal) * parseFloat(awal.harga);
                }

                // Tier awal
                let total = Math.max(0, (awal.nilai_maks || 5) - this.bintangAwal) * parseFloat(awal.harga);
                
                // Tier antara
                this.itemsUtama.forEach(item => {
                    if (item.urutan > awal.urutan && item.urutan < tujuan.urutan) {
                        total += (item.nilai_maks || 5) * parseFloat(item.harga);
                    }
                });

                // Tier tujuan
                total += this.bintangTujuan * parseFloat(tujuan.harga);
                return total;
            }

            if (this.tipeHarga === 'progres') {
                let total = 0;
                for (const id in this.progresSelected) {
                    if (this.progresSelected[id]) {
                        const item = this.itemsUtama.find(i => String(i.id) === String(id));
                        if (item && parseFloat(item.harga) > 0) {
                            const p = parseFloat(this.progres[id] || 0);
                            if (p < 100) {
                                total += ((100 - p) / 100) * parseFloat(item.harga);
                            }
                        }
                    }
                }
                return Math.round(total);
            }

            if (this.tipeHarga === 'pilihan') {
                let total = 0;
                this.itemsUtama.forEach(item => {
                    if (this.selectedItems[item.id]) {
                        const qty = this.itemQty[item.id] || 1;
                        total += qty * parseFloat(item.harga);
                    }
                });
                return total;
            }

            return 0;
        },

        get subtotalTambahan() {
            let total = 0;
            for (const id in this.selectedTambahan) {
                total += parseFloat(this.selectedTambahan[id]);
            }
            return total;
        },

        get totalHarga() {
            return this.subtotalUtama + this.subtotalTambahan;
        },

        isFormValid() {
            if (this.totalHarga <= 0) return false;
            if (this.tipeHarga === 'tangga') return this.isTanggaValid();
            if (this.tipeHarga === 'progres') return this.totalSelectedProgresCount() > 0;
            if (this.tipeHarga === 'pilihan') return this.selectedItemsList.length > 0;
            return true;
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    };
}
window.orderCalculator = orderCalculator;
</script>
@endpush
@endsection
