@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.games.index') }}" class="hover:text-white transition-colors">Kelola Game</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">Ubah Game</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Ubah Data Game: {{ $game->nama_game }}
            </h2>
        </div>
        <a href="{{ route('admin.games.index') }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" 
              action="{{ route('admin.games.update', $game) }}" 
              enctype="multipart/form-data" 
              class="space-y-6"
              x-data="{ 
                  imageType: 'upload', 
                  previewUrl: '{{ $game->gambar_url }}',
                  imageUrlInput: '{{ str_starts_with($game->gambar, 'http') ? $game->gambar : '' }}',
                  handleFileUpload(event) {
                      const file = event.target.files[0];
                      if (file) {
                          this.previewUrl = URL.createObjectURL(file);
                      }
                  }
              }">
            @csrf
            @method('PUT')

            <!-- Nama Game & Kategori (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Nama Game -->
                <div>
                    <label for="nama_game" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nama Game <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="nama_game" 
                           name="nama_game" 
                           value="{{ old('nama_game', $game->nama_game) }}" 
                           required 
                           maxlength="100"
                           class="w-full bg-brand-surface border {{ $errors->has('nama_game') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('nama_game')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori Game -->
                <div>
                    <label for="kategori" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Kategori Game
                    </label>
                    <input type="text" 
                           id="kategori" 
                           name="kategori" 
                           list="kategori-list"
                           value="{{ old('kategori', $game->kategori) }}" 
                           placeholder="Pilih atau ketik kategori..." 
                           maxlength="50"
                           class="w-full bg-brand-surface border {{ $errors->has('kategori') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    <datalist id="kategori-list">
                        <option value="MOBA">
                        <option value="RPG / Open World">
                        <option value="FPS / Tactical Shooter">
                        <option value="Battle Royale">
                        <option value="Turn-based RPG">
                        <option value="Gacha Game">
                    </datalist>
                    @error('kategori')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Deskripsi Game -->
            <div>
                <label for="deskripsi" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Deskripsi Game
                </label>
                <textarea id="deskripsi" 
                          name="deskripsi" 
                          rows="3" 
                          placeholder="Penjelasan ringkas tentang game..." 
                          class="w-full bg-brand-surface border {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-brand-border' }} rounded-xl p-4 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">{{ old('deskripsi', $game->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bagian Gambar Game (Dual Opsi: Upload File atau Masukkan URL) -->
            <div class="border border-brand-border/80 rounded-2xl p-5 bg-brand-surface/30 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200">
                            Cover Gambar Game
                        </label>
                        <p class="text-[11px] text-slate-400">Kosongkan jika tidak ingin mengubah cover gambar saat ini.</p>
                    </div>

                    <!-- Toggle Pilihan Metode -->
                    <div class="inline-flex p-1 rounded-xl bg-brand-surface border border-brand-border text-xs">
                        <button type="button" 
                                @click="imageType = 'upload'" 
                                :class="imageType === 'upload' ? 'bg-brand-blue text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg font-medium transition-all">
                            Unggah Berkas Baru
                        </button>
                        <button type="button" 
                                @click="imageType = 'url'" 
                                :class="imageType === 'url' ? 'bg-brand-blue text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg font-medium transition-all">
                            Ganti URL Gambar
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start pt-2">
                    
                    <!-- Input Opsi Berkas -->
                    <div x-show="imageType === 'upload'" class="md:col-span-2 space-y-3">
                        <input type="file" 
                               name="gambar_upload" 
                               accept="image/jpeg,image/png,image/webp" 
                               @change="handleFileUpload"
                               class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-blue file:text-white hover:file:opacity-90 file:cursor-pointer cursor-pointer bg-brand-surface rounded-xl border border-brand-border p-2">
                        <p class="text-[11px] text-slate-500">JPG, PNG, atau WebP (Maksimal 2 MB). Bila diganti, berkas lama otomatis dihapus dari server.</p>
                        @error('gambar_upload')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input Opsi URL Gambar -->
                    <div x-show="imageType === 'url'" class="md:col-span-2 space-y-3">
                        <input type="url" 
                               name="gambar_url" 
                               x-model="imageUrlInput"
                               placeholder="https://images.unsplash.com/... atau https://..." 
                               class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                        <p class="text-[11px] text-slate-500">Tautan gambar online langsung (HTTPS).</p>
                        @error('gambar_url')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Live Image Preview Box -->
                    <div class="bg-brand-surface border border-brand-border rounded-xl p-3 text-center">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pratinjau Cover</span>
                        <div class="w-full h-32 rounded-lg bg-brand-card overflow-hidden border border-brand-border flex items-center justify-center">
                            <img :src="imageType === 'upload' ? previewUrl : (imageUrlInput ? imageUrlInput : previewUrl)" 
                                 alt="{{ $game->nama_game }}" 
                                 class="w-full h-full object-cover">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface/40 border border-brand-border">
                <div class="pr-4">
                    <span class="block text-sm font-semibold text-white">Status Publikasi Game</span>
                    <span class="block text-xs text-slate-400 mt-0.5">
                        Mengubah status menjadi <strong class="text-slate-300">nonaktif</strong> akan menyembunyikan game beserta seluruh layanannya dari katalog publik beranda; data layanan dan pesanan tetap aman tersimpan.
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" 
                           name="aktif" 
                           value="1" 
                           {{ old('aktif', $game->aktif) ? 'checked' : '' }} 
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-brand-border">
                <a href="{{ route('admin.games.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
