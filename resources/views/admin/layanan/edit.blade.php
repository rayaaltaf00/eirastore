@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.layanan.index', ['game_id' => $layanan->game_id]) }}" class="hover:text-white transition-colors">Kelola Layanan</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">Ubah Layanan</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Ubah Layanan: {{ $layanan->nama_layanan }}
            </h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 text-xs font-semibold hover:bg-brand-blue/30 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Kelola Item Layanan ({{ $layanan->items->count() }})
            </a>
            <a href="{{ route('admin.layanan.index', ['game_id' => $layanan->game_id]) }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                Kembali
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" 
              action="{{ route('admin.layanan.update', $layanan) }}" 
              enctype="multipart/form-data"
              class="space-y-6"
              x-data="{
                  imageType: '{{ str_starts_with($layanan->gambar ?? '', 'http') ? 'url' : 'upload' }}',
                  previewUrl: '{{ $layanan->gambar_url }}',
                  imageUrlInput: '{{ str_starts_with($layanan->gambar ?? '', 'http') ? $layanan->gambar : '' }}',
                  handleFileUpload(e) {
                      const file = e.target.files[0];
                      if (file) {
                          this.previewUrl = URL.createObjectURL(file);
                      }
                  }
              }">
            @csrf
            @method('PUT')

            <!-- Game Induk & Nama Layanan (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Game Induk -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="game_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Game Induk <span class="text-red-400">*</span>
                        </label>
                        @if($isGameLocked)
                            <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                                Terkunci (Ada Pesanan)
                            </span>
                        @endif
                    </div>

                    @if($isGameLocked)
                        <input type="hidden" name="game_id" value="{{ $layanan->game_id }}">
                        <div class="w-full bg-brand-surface/40 border border-brand-border/60 rounded-xl px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed">
                            {{ $layanan->game->nama_game }}
                        </div>
                        <p class="text-[11px] text-amber-400/80 mt-1.5">Game induk tidak dapat dipindah karena layanan ini memiliki riwayat transaksi.</p>
                    @else
                        <select id="game_id" 
                                name="game_id" 
                                required 
                                class="w-full bg-brand-surface border {{ $errors->has('game_id') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan">
                            @foreach($games as $g)
                                <option value="{{ $g->id }}" {{ (old('game_id', $layanan->game_id) == $g->id) ? 'selected' : '' }}>
                                    {{ $g->nama_game }}
                                </option>
                            @endforeach
                        </select>
                    @endif
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
                           value="{{ old('nama_layanan', $layanan->nama_layanan) }}" 
                           required 
                           maxlength="100"
                           class="w-full bg-brand-surface border {{ $errors->has('nama_layanan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    @error('nama_layanan')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tipe Perhitungan Harga -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Metode Perhitungan Harga <span class="text-red-400">*</span>
                    </label>
                    @if($isTipeHargaLocked)
                        <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                            Terkunci (Memiliki {{ $layanan->items->count() }} Item / Pesanan)
                        </span>
                    @endif
                </div>

                @if($isTipeHargaLocked)
                    <input type="hidden" name="tipe_harga" value="{{ $layanan->tipe_harga->value }}">
                    <div class="p-4 rounded-xl bg-brand-surface/40 border border-brand-border flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white text-sm block">
                                {{ $layanan->tipe_harga->label() }}
                            </span>
                            <span class="text-xs text-slate-400 block mt-0.5">
                                {{ $layanan->tipe_harga->deskripsi() }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-500 bg-brand-surface px-3 py-1.5 rounded-lg border border-brand-border">
                            Tidak dapat diubah
                        </span>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-data="{ selectedTipe: '{{ old('tipe_harga', $layanan->tipe_harga->value) }}' }">
                        @foreach($tipeHargaList as $tipe)
                            <label @click="selectedTipe = '{{ $tipe->value }}'" 
                                   :class="selectedTipe === '{{ $tipe->value }}' ? 'border-brand-cyan bg-brand-blue/15 shadow-glow-blue' : 'border-brand-border bg-brand-surface/40 hover:border-slate-500'"
                                   class="relative border rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">{{ $tipe->label() }}</span>
                                        <input type="radio" name="tipe_harga" value="{{ $tipe->value }}" x-model="selectedTipe" class="text-brand-cyan focus:ring-0">
                                    </div>
                                    <p class="text-xs text-slate-300">
                                        {{ $tipe->deskripsi() }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
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
                           value="{{ old('estimasi_waktu', $layanan->estimasi_waktu) }}" 
                           placeholder="Contoh: 1 - 2 Hari, 3 - 6 Jam" 
                           maxlength="50"
                           class="w-full bg-brand-surface border {{ $errors->has('estimasi_waktu') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
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
                              placeholder="Deskripsi singkat layanan..." 
                              class="w-full bg-brand-surface border {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-brand-border' }} rounded-xl p-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">{{ old('deskripsi', $layanan->deskripsi) }}</textarea>
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
                            Poster / Artwork Layanan
                        </label>
                        <p class="text-[11px] text-slate-400">Kosongkan jika tidak ingin mengubah poster saat ini.</p>
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
                        <p class="text-[11px] text-slate-500">JPG, PNG, atau WebP (Maksimal 2 MB). Bila diganti, berkas lokal lama otomatis dibersihkan.</p>
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
                        <p class="text-[11px] text-slate-500">Tautan gambar online langsung (HTTPS) beresolusi portrait tajam.</p>
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
                                <img :src="imageUrlInput ? imageUrlInput : '{{ $layanan->gambar_url }}'" 
                                     alt="Preview" 
                                     class="w-full h-full object-cover"
                                     x-on:error="$el.src = 'https://placehold.co/240x320/0a1128/ef4444?text=URL+Gagal'">
                            </template>
                        </div>
                    </div>

                </div>

                @if($layanan->gambar)
                    <div class="pt-3 border-t border-brand-border/50">
                        <label class="inline-flex items-center space-x-2 text-xs text-amber-400 hover:text-amber-300 cursor-pointer">
                            <input type="checkbox" name="hapus_gambar_khusus" value="1" class="rounded border-brand-border bg-brand-surface text-amber-500 focus:ring-0">
                            <span>Hapus poster khusus ini dan kembali gunakan gambar default dari game induk</span>
                        </label>
                    </div>
                @endif
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface/40 border border-brand-border">
                <div class="pr-4">
                    <span class="block text-sm font-semibold text-white">Status Publikasi Layanan</span>
                    <span class="block text-xs text-slate-400 mt-0.5">
                        Menonaktifkan layanan ini akan menyembunyikannya dari katalog customer tanpa menghapus data item atau riwayat transaksi.
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" 
                           name="aktif" 
                           value="1" 
                           {{ old('aktif', $layanan->aktif) ? 'checked' : '' }} 
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-brand-border">
                <a href="{{ route('admin.layanan.index', ['game_id' => $layanan->game_id]) }}" 
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
