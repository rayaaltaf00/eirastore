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
                <span class="text-brand-cyan font-medium">Tambah Game</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Tambah Game Baru
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
              action="{{ route('admin.games.store') }}" 
              enctype="multipart/form-data" 
              class="space-y-6"
              x-data="{ 
                  imageType: 'upload', 
                  previewUrl: '',
                  imageUrlInput: '{{ old('gambar_url', '') }}',
                  handleFileUpload(event) {
                      const file = event.target.files[0];
                      if (file) {
                          this.previewUrl = URL.createObjectURL(file);
                      }
                  }
              }">
            @csrf

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
                           value="{{ old('nama_game') }}" 
                           placeholder="Contoh: Mobile Legends, Genshin Impact" 
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
                           value="{{ old('kategori') }}" 
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
                    <p class="text-[11px] text-slate-400 mt-1.5">Opsional. Digunakan untuk filter pencarian katalog di beranda.</p>
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
                          placeholder="Penjelasan ringkas tentang game dan cakupan layanan yang tersedia..." 
                          class="w-full bg-brand-surface border {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-brand-border' }} rounded-xl p-4 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">{{ old('deskripsi') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1.5">Opsional. Tampil di kartu katalog dan halaman detail game.</p>
                @error('deskripsi')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bagian Gambar Game (Dual Opsi: Upload File atau Masukkan URL) -->
            <div class="border border-brand-border/80 rounded-2xl p-5 bg-brand-surface/30 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200">
                            Cover Gambar Game <span class="text-red-400">*</span>
                        </label>
                        <p class="text-[11px] text-slate-400">Pilih metode penyediaan cover gambar: unggah berkas lokal atau masukkan URL langsung.</p>
                    </div>

                    <!-- Toggle Pilihan Metode -->
                    <div class="inline-flex p-1 rounded-xl bg-brand-surface border border-brand-border text-xs">
                        <button type="button" 
                                @click="imageType = 'upload'" 
                                :class="imageType === 'upload' ? 'bg-brand-blue text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg font-medium transition-all">
                            Unggah Berkas
                        </button>
                        <button type="button" 
                                @click="imageType = 'url'" 
                                :class="imageType === 'url' ? 'bg-brand-blue text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg font-medium transition-all">
                            Input URL Gambar
                        </button>
                    </div>
                </div>

                @error('gambar')
                    <div class="p-3 rounded-xl bg-red-950/70 border border-red-500/30 text-xs text-red-300">
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start pt-2">
                    
                    <!-- Input Opsi Berkas -->
                    <div x-show="imageType === 'upload'" class="md:col-span-2 space-y-3">
                        <input type="file" 
                               name="gambar_upload" 
                               accept="image/jpeg,image/png,image/webp" 
                               @change="handleFileUpload"
                               class="block w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-blue file:text-white hover:file:opacity-90 file:cursor-pointer cursor-pointer bg-brand-surface rounded-xl border border-brand-border p-2">
                        <p class="text-[11px] text-slate-500">Format yang didukung: JPG, PNG, atau WebP (Maksimal 2 MB). Disimpan ke disk public storage.</p>
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
                        <p class="text-[11px] text-slate-500">Gunakan tautan gambar online langsung (HTTPS) beresolusi tajam.</p>
                        @error('gambar_url')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Live Image Preview Box -->
                    <div class="bg-brand-surface border border-brand-border rounded-xl p-3 text-center">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pratinjau Cover</span>
                        <div class="w-full h-32 rounded-lg bg-brand-card overflow-hidden border border-brand-border flex items-center justify-center">
                            <!-- Preview File Upload -->
                            <template x-if="imageType === 'upload'">
                                <img :src="previewUrl ? previewUrl : 'https://placehold.co/400x300/0a1128/38bdf8?text=Preview+Cover'" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover">
                            </template>
                            <!-- Preview URL Input -->
                            <template x-if="imageType === 'url'">
                                <img :src="imageUrlInput ? imageUrlInput : 'https://placehold.co/400x300/0a1128/38bdf8?text=Preview+Cover'" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover"
                                     x-on:error="$el.src = 'https://placehold.co/400x300/0a1128/ef4444?text=URL+Tidak+Valid'">
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface/40 border border-brand-border">
                <div>
                    <span class="block text-sm font-semibold text-white">Status Publikasi Game</span>
                    <span class="block text-xs text-slate-400">Jika aktif, game ini beserta layanannya akan langsung tampil pada katalog publik beranda.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="aktif" 
                           value="1" 
                           {{ old('aktif', '1') == '1' ? 'checked' : '' }} 
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
                    Simpan Game
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
