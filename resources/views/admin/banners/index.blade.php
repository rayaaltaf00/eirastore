@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Kelola Banner Promosi
            </h2>
            <p class="text-xs text-slate-400 mt-1">Atur banner carousel yang tampil di halaman utama EiraStore</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.banners.create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Banner
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Banner</span>
                <p class="text-2xl font-black text-white mt-1">{{ $banners->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-brand-blue/15 border border-brand-blue/30 flex items-center justify-center text-brand-cyan">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Banner Aktif</span>
                <p class="text-2xl font-black text-emerald-400 mt-1">{{ $bannersAktif->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Banner Nonaktif</span>
                <p class="text-2xl font-black text-amber-400 mt-1">{{ $banners->where('is_aktif', false)->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- LIVE PREVIEW CAROUSEL (sama persis dengan tampilan di beranda) -->
    <!-- ============================================================= -->
    @if($bannersAktif->isNotEmpty())
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-5 shadow-card space-y-3">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Live Preview Carousel</span>
            <span class="text-[10px] text-slate-500 ml-1">(Tampilan persis seperti di halaman utama)</span>
        </div>

        <div x-data="{
            currentSlide: 0,
            totalSlides: {{ $bannersAktif->count() }},
            autoplayInterval: null,
            init() {
                if (this.totalSlides > 1) {
                    this.autoplayInterval = setInterval(() => { this.next() }, 5000);
                }
            },
            next() {
                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
            },
            prev() {
                this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
            },
            goTo(index) {
                this.currentSlide = index;
                clearInterval(this.autoplayInterval);
                if (this.totalSlides > 1) {
                    this.autoplayInterval = setInterval(() => { this.next() }, 5000);
                }
            }
        }" class="relative rounded-2xl overflow-hidden border border-brand-border shadow-2xl">
            <div class="relative w-full aspect-[21/7] sm:aspect-[21/6] bg-brand-card">
                @foreach($bannersAktif as $index => $banner)
                <div x-show="currentSlide === {{ $index }}"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0">
                    <img src="{{ $banner->gambar_url }}" alt="{{ $banner->judul }}" class="w-full h-full object-cover">
                </div>
                @endforeach

                @if($bannersAktif->count() > 1)
                <!-- Prev/Next Buttons -->
                <button @click="prev(); goTo(currentSlide)" class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="next(); goTo(currentSlide)" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <!-- Dot Indicators -->
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center space-x-2 z-10">
                    <template x-for="i in totalSlides" :key="i">
                        <button @click="goTo(i - 1)" :class="currentSlide === (i - 1) ? 'bg-brand-cyan w-6' : 'bg-white/40 w-2 hover:bg-white/60'" class="h-2 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================================= -->
    <!-- TABEL DAFTAR BANNER                                           -->
    <!-- ============================================================= -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl shadow-card overflow-hidden">
        @if($banners->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-sm text-slate-400">Belum ada banner. Klik <strong class="text-brand-cyan">Tambah Banner</strong> untuk memulai.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-brand-surface/60 border-b border-brand-border">
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Urutan</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Preview</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Judul</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border">
                        @foreach($banners as $banner)
                        <tr class="hover:bg-brand-surface/30 transition-colors">
                            <!-- Urutan -->
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-surface border border-brand-border text-xs font-bold text-white">
                                    {{ $banner->urutan }}
                                </span>
                            </td>

                            <!-- Preview Thumbnail -->
                            <td class="px-5 py-3">
                                <div class="w-32 aspect-[3/1] rounded-lg overflow-hidden border border-brand-border bg-brand-surface">
                                    <img src="{{ $banner->gambar_url }}" alt="{{ $banner->judul }}" class="w-full h-full object-cover">
                                </div>
                            </td>

                            <!-- Judul & Subjudul -->
                            <td class="px-5 py-3">
                                <p class="font-semibold text-white">{{ $banner->judul }}</p>
                                @if($banner->subjudul)
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $banner->subjudul }}</p>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3">
                                @if($banner->is_aktif)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-1.5"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.banners.edit', $banner) }}" 
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-brand-surface border border-brand-border text-xs font-medium text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors"
                                       title="Edit banner">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" 
                                          onsubmit="return confirm('Yakin ingin menghapus banner \'{{ $banner->judul }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-500/20 transition-colors"
                                                title="Hapus banner">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Info Panduan -->
    <div class="bg-brand-surface/50 border border-brand-border rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-brand-cyan shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-xs text-slate-400 space-y-1">
            <p><strong class="text-slate-300">Panduan Banner:</strong></p>
            <ul class="list-disc list-inside space-y-0.5">
                <li>Ukuran rekomendasi: <strong class="text-brand-cyan">1200 × 400 px</strong> (rasio 3:1)</li>
                <li>Format: JPG, PNG, atau WebP — maksimal 3 MB</li>
                <li>Banner dengan urutan lebih kecil akan tampil lebih dulu di carousel</li>
                <li>Hanya banner berstatus <strong class="text-emerald-400">Aktif</strong> yang tampil di halaman utama</li>
            </ul>
        </div>
    </div>

</div>
@endsection
