@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.layanan.index') }}" class="hover:text-white transition-colors">Kelola Layanan</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">Tambah Layanan</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Tambah Layanan Joki Baru
            </h2>
        </div>
        <a href="{{ route('admin.layanan.index') }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" 
              action="{{ route('admin.layanan.store') }}" 
              enctype="multipart/form-data"
              class="space-y-6"
              x-data="{ 
                  selectedTipe: '{{ old('tipe_harga', 'tangga') }}',
                  imageType: 'url',
                  previewUrl: '{{ old('gambar_url') }}',
                  imageUrlInput: '{{ old('gambar_url') }}',
                  handleFileUpload(e) {
                      const file = e.target.files[0];
                      if (file) {
                          this.previewUrl = URL.createObjectURL(file);
                      }
                  }
              }">
            @csrf

            <!-- Game Induk & Nama Layanan (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Game Induk -->
                <div>
                    <label for="game_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Game Induk <span class="text-red-400">*</span>
                    </label>
                    <select id="game_id" 
                            name="game_id" 
                            required 
                            class="w-full bg-brand-surface border {{ $errors->has('game_id') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan">
                        <option value="">-- Pilih Game --</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}" {{ (old('game_id', $selectedGameId) == $g->id) ? 'selected' : '' }}>
                                {{ $g->nama_game }}
                            </option>
                        @endforeach
                    </select>
                    @error('game_id')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Layanan -->
                <div>
                    <label for="nama_layanan" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nama Layanan Joki <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="nama_layanan" 
                           name="nama_layanan" 
                           value="{{ old('nama_layanan') }}" 
                           placeholder="Contoh: Joki Rank (Per Bintang), Eksplorasi Map 100%" 
                           required 
                           maxlength="100"
                           class="w-full bg-brand-surface border {{ $errors->has('nama_layanan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1.5">Nama layanan harus unik di dalam game yang sama.</p>
                    @error('nama_layanan')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tipe Perhitungan Harga (Radio Cards Interaktif) -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Metode Perhitungan Harga <span class="text-red-400">*</span>
                </label>
                <p class="text-[11px] text-slate-400 mb-3">
                    Perhatian: Tipe harga menentukan format input dan perhitungan di kalkulator pesanan. Tipe ini akan <strong>terkunci</strong> setelah item layanan ditambahkan.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Opsi Tangga -->
                    <label @click="selectedTipe = 'tangga'" 
                           :class="selectedTipe === 'tangga' ? 'border-brand-cyan bg-brand-blue/15 shadow-glow-blue' : 'border-brand-border bg-brand-surface/40 hover:border-slate-500'"
                           class="relative border rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-white uppercase tracking-wider">Tangga (Rank)</span>
                                <input type="radio" name="tipe_harga" value="tangga" x-model="selectedTipe" class="text-brand-cyan focus:ring-0">
                            </div>
                            <p class="text-xs text-slate-300">
                                Dihitung dari <strong>selisih tingkat/posisi awal ke tujuan</strong> secara bertahap.
                            </p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-brand-border/60 text-[11px] text-indigo-400 font-medium">
                            Contoh: Joki Rank MLBB, Valorant, Free Fire.
                        </div>
                    </label>

                    <!-- Opsi Progres -->
                    <label @click="selectedTipe = 'progres'" 
                           :class="selectedTipe === 'progres' ? 'border-brand-cyan bg-brand-blue/15 shadow-glow-blue' : 'border-brand-border bg-brand-surface/40 hover:border-slate-500'"
                           class="relative border rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-white uppercase tracking-wider">Progres (%)</span>
                                <input type="radio" name="tipe_harga" value="progres" x-model="selectedTipe" class="text-brand-cyan focus:ring-0">
                            </div>
                            <p class="text-xs text-slate-300">
                                Berdasarkan <strong>persentase capaian</strong> dari harga paket penuh (0% sampai 100%).
                            </p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-brand-border/60 text-[11px] text-cyan-400 font-medium">
                            Contoh: Eksplorasi Map Genshin Impact, Wuthering Waves.
                        </div>
                    </label>

                    <!-- Opsi Pilihan -->
                    <label @click="selectedTipe = 'pilihan'" 
                           :class="selectedTipe === 'pilihan' ? 'border-brand-cyan bg-brand-blue/15 shadow-glow-blue' : 'border-brand-border bg-brand-surface/40 hover:border-slate-500'"
                           class="relative border rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-white uppercase tracking-wider">Pilihan (Item)</span>
                                <input type="radio" name="tipe_harga" value="pilihan" x-model="selectedTipe" class="text-brand-cyan focus:ring-0">
                            </div>
                            <p class="text-xs text-slate-300">
                                Customer <strong>mencentang item-item lepas</strong> atau paket yang dapat dikalikan jumlah.
                            </p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-brand-border/60 text-[11px] text-emerald-400 font-medium">
                            Contoh: Quest Harian, Winrate, Spiral Abyss, Material.
                        </div>
                    </label>

                </div>
                @error('tipe_harga')
                    <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estimasi Waktu & Deskripsi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Estimasi Waktu -->
                <div>
                    <label for="estimasi_waktu" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Estimasi Waktu Pengerjaan
                    </label>
                    <input type="text" 
                           id="estimasi_waktu" 
                           name="estimasi_waktu" 
                           value="{{ old('estimasi_waktu') }}" 
                           placeholder="Contoh: 1 - 2 Hari, 3 - 6 Jam" 
                           maxlength="50"
                           class="w-full bg-brand-surface border {{ $errors->has('estimasi_waktu') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1.5">Opsional. Membantu customer memperkirakan durasi pengerjaan.</p>
                    @error('estimasi_waktu')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi Layanan -->
                <div>
                    <label for="deskripsi" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Deskripsi / Petunjuk Layanan
                    </label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              rows="3" 
                              placeholder="Deskripsi singkat, syarat akun, atau ketentuan khusus layanan..." 
                              class="w-full bg-brand-surface border {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-brand-border' }} rounded-xl p-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Bagian Poster Gambar Layanan (Dual Opsi: Upload File atau Masukkan URL) -->
            <div class="border border-brand-border/80 rounded-2xl p-5 bg-brand-surface/30 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200">
                            Poster / Artwork Layanan <span class="text-xs text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <p class="text-[11px] text-slate-400">Gunakan poster vertikal (rasio ~3:4 / karakter game). Jika dikosongkan, akan otomatis menggunakan gambar game induk.</p>
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
                        <p class="text-[11px] text-slate-500">Gunakan link gambar artwork joki langsung (HTTPS) beresolusi portrait tajam.</p>
                        @error('gambar_url')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Live Image Preview Box -->
                    <div class="bg-brand-surface border border-brand-border rounded-xl p-3 text-center">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pratinjau Poster</span>
                        <div class="w-28 h-36 mx-auto rounded-xl bg-brand-card overflow-hidden border border-brand-border flex items-center justify-center shadow-md">
                            <!-- Preview File Upload -->
                            <template x-if="imageType === 'upload'">
                                <img :src="previewUrl ? previewUrl : 'https://placehold.co/240x320/0a1128/38bdf8?text=Poster+Joki'" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover">
                            </template>

                            <!-- Preview URL Input -->
                            <template x-if="imageType === 'url'">
                                <img :src="imageUrlInput ? imageUrlInput : 'https://placehold.co/240x320/0a1128/38bdf8?text=Poster+Joki'" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover"
                                     x-on:error="$el.src = 'https://placehold.co/240x320/0a1128/ef4444?text=URL+Gagal'">
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface/40 border border-brand-border">
                <div>
                    <span class="block text-sm font-semibold text-white">Status Publikasi Layanan</span>
                    <span class="block text-xs text-slate-400">Jika aktif, layanan ini akan dapat dipilih oleh customer pada halaman game.</span>
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
                <a href="{{ route('admin.layanan.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                    Lanjut: Tambah Item Layanan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
