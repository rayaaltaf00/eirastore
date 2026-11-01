@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.banners.index') }}" class="hover:text-white transition-colors">Kelola Banner</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">Edit Banner</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Edit Banner: {{ $banner->judul }}
            </h2>
        </div>
        <a href="{{ route('admin.banners.index') }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" 
              action="{{ route('admin.banners.update', $banner) }}" 
              enctype="multipart/form-data" 
              class="space-y-6"
              x-data="{ 
                  previewUrl: '{{ $banner->gambar_url }}',
                  hasNewFile: false,
                  handleFileUpload(event) {
                      const file = event.target.files[0];
                      if (file) {
                          this.previewUrl = URL.createObjectURL(file);
                          this.hasNewFile = true;
                      }
                  }
              }">
            @csrf
            @method('PUT')

            <!-- Judul & Urutan (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Judul Banner -->
                <div>
                    <label for="judul" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Judul Banner <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="judul" 
                           name="judul" 
                           value="{{ old('judul', $banner->judul) }}" 
                           placeholder="Contoh: Diskon 20% Joki Rank" 
                           required 
                           maxlength="100"
                           class="w-full bg-brand-surface border {{ $errors->has('judul') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1.5">Judul ini hanya untuk identifikasi di panel admin, tidak tampil di website.</p>
                    @error('judul')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Urutan -->
                <div>
                    <label for="urutan" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Urutan Tampil <span class="text-red-400">*</span>
                    </label>
                    <input type="number" 
                           id="urutan" 
                           name="urutan" 
                           value="{{ old('urutan', $banner->urutan) }}" 
                           min="0" 
                           max="255" 
                           required
                           class="w-full bg-brand-surface border {{ $errors->has('urutan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1.5">Angka lebih kecil = tampil lebih dulu di carousel.</p>
                    @error('urutan')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Subjudul -->
            <div>
                <label for="subjudul" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Subjudul / Keterangan
                </label>
                <input type="text" 
                       id="subjudul" 
                       name="subjudul" 
                       value="{{ old('subjudul', $banner->subjudul) }}" 
                       placeholder="Opsional — keterangan singkat untuk identifikasi" 
                       maxlength="255"
                       class="w-full bg-brand-surface border {{ $errors->has('subjudul') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                @error('subjudul')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Upload Gambar Banner -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Gambar Banner
                </label>

                <div class="space-y-3">
                    <!-- Preview Gambar Saat Ini -->
                    <div class="relative w-full aspect-[3/1] rounded-xl overflow-hidden border border-brand-border bg-brand-surface">
                        <img :src="previewUrl" alt="Preview Banner" class="w-full h-full object-cover">
                        <div x-show="!hasNewFile" class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-black/60 text-[10px] font-medium text-slate-300">Gambar saat ini</span>
                        </div>
                        <div x-show="hasNewFile" class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-brand-blue/80 text-[10px] font-medium text-white">Gambar baru</span>
                        </div>
                    </div>

                    <!-- Upload Baru -->
                    <label for="gambar" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-sm font-medium text-slate-300 hover:text-white hover:bg-brand-surface/80 cursor-pointer transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Ganti Gambar
                        <input type="file" 
                               id="gambar" 
                               name="gambar" 
                               accept="image/jpeg,image/png,image/webp"
                               @change="handleFileUpload($event)"
                               class="hidden">
                    </label>

                    <!-- Panduan Dimensi -->
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-brand-blue/10 border border-brand-blue/20">
                        <svg class="w-4 h-4 text-brand-cyan shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[11px] text-slate-300">Rekomendasi: <strong class="text-brand-cyan">1200 × 400 px</strong> (rasio 3:1). Biarkan kosong jika tidak ingin mengganti gambar.</span>
                    </div>
                </div>

                @error('gambar')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-3 p-4 rounded-xl bg-brand-surface/50 border border-brand-border">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_aktif" value="0">
                    <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $banner->is_aktif) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-10 h-5 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-blue"></div>
                </label>
                <div>
                    <span class="text-sm font-medium text-white">Tampilkan di Halaman Utama</span>
                    <p class="text-[11px] text-slate-400">Jika diaktifkan, banner ini akan muncul di carousel halaman utama.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.banners.index') }}" class="px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-sm font-medium text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Perbarui Banner
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
