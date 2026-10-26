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
                <a href="{{ route('admin.layanan.items.index', $layanan) }}" class="hover:text-white transition-colors">{{ $layanan->nama_layanan }}</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">Ubah Item</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Ubah Item: {{ $item->nama }}
            </h2>
            <p class="text-xs text-slate-400">
                Layanan: <span class="text-brand-cyan font-semibold">{{ $layanan->nama_layanan }}</span> • Metode: <span class="text-white font-medium">{{ $layanan->tipe_harga->label() }}</span>
            </p>
        </div>
        <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Alert / Notice A-13 -->
    <div class="p-4 rounded-xl bg-blue-950/40 border border-brand-cyan/30 text-slate-300 text-xs flex items-center space-x-3">
        <svg class="w-5 h-5 text-brand-cyan shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
        <span>
            <strong>Catatan:</strong> Perubahan tarif atau nilai pada item ini hanya akan berlaku untuk <strong>pesanan baru berikutnya</strong>. Rincian dan harga pesanan yang sudah berjalan tidak akan berubah karena tersimpan mandiri di riwayat transaksi.
        </span>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" 
              action="{{ route('admin.layanan.items.update', [$layanan, $item]) }}" 
              class="space-y-6"
              x-data="{ 
                  jenis: '{{ old('jenis', $item->jenis) }}',
                  tipeHarga: '{{ $layanan->tipe_harga->value }}'
              }">
            @csrf
            @method('PUT')

            <!-- Jenis Item & Kategori -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Jenis Item -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Jenis Item <span class="text-red-400">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label @click="jenis = 'utama'" 
                               :class="jenis === 'utama' ? 'border-brand-cyan bg-brand-blue/20 text-white font-bold' : 'border-brand-border bg-brand-surface text-slate-400'"
                               class="flex items-center justify-center p-3 rounded-xl border text-xs cursor-pointer transition-all">
                            <input type="radio" name="jenis" value="utama" x-model="jenis" class="sr-only">
                            Item Utama
                        </label>
                        <label @click="jenis = 'tambahan'" 
                               :class="jenis === 'tambahan' ? 'border-purple-500 bg-purple-500/20 text-white font-bold' : 'border-brand-border bg-brand-surface text-slate-400'"
                               class="flex items-center justify-center p-3 rounded-xl border text-xs cursor-pointer transition-all">
                            <input type="radio" name="jenis" value="tambahan" x-model="jenis" class="sr-only">
                            Item Tambahan
                        </label>
                    </div>
                    @error('jenis')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori Item -->
                <div>
                    <label for="kategori" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Kategori / Pengelompokan
                    </label>
                    <input type="text" 
                           id="kategori" 
                           name="kategori" 
                           list="item-kategori-list"
                           autocomplete="off"
                           value="{{ old('kategori', $item->kategori) }}" 
                           placeholder="Contoh: Archon Quest, World Quest, Story Character Quest" 
                           maxlength="100"
                           class="w-full bg-brand-surface border {{ $errors->has('kategori') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    <datalist id="item-kategori-list">
                        @if(isset($existingKategori) && $existingKategori->isNotEmpty())
                            @foreach($existingKategori as $ek)
                                <option value="{{ $ek }}"></option>
                            @endforeach
                        @endif
                    </datalist>
                    <p class="text-[11px] text-slate-400 mt-1.5">Opsional. Ketik kategori baru atau pilih dari kategori yang sudah ada di layanan ini.</p>
                    @error('kategori')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Parent (Khusus Tipe Progres) -->
            @if($layanan->tipe_harga->value === 'progres')
                <div x-show="jenis === 'utama'" class="p-4 rounded-xl bg-brand-surface/40 border border-brand-border space-y-2">
                    <label for="parent_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Item Induk (Region)
                    </label>
                    <select id="parent_id" 
                            name="parent_id" 
                            class="w-full bg-brand-surface border {{ $errors->has('parent_id') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan">
                        <option value="">-- Tanpa Induk (Item ini sebagai Region Induk) --</option>
                        @foreach($parentCandidates as $pc)
                            <option value="{{ $pc->id }}" {{ old('parent_id', $item->parent_id) == $pc->id ? 'selected' : '' }}>
                                {{ $pc->nama }} (Induk Region)
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Nama Item & Urutan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Nama Item -->
                <div class="sm:col-span-2">
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nama Item / Tingkatan <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           value="{{ old('nama', $item->nama) }}" 
                           required 
                           maxlength="150"
                           class="w-full bg-brand-surface border {{ $errors->has('nama') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    @error('nama')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Urutan -->
                <div>
                    <label for="urutan" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nomor Urutan <span class="text-red-400">*</span>
                    </label>
                    <input type="number" 
                           id="urutan" 
                           name="urutan" 
                           value="{{ old('urutan', $item->urutan) }}" 
                           required 
                           min="0"
                           class="w-full bg-brand-surface border {{ $errors->has('urutan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan transition-colors">
                    @error('urutan')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Harga & Parameter Khusus Tipe Harga (Grid 3 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                
                <!-- Harga -->
                <div>
                    <label for="harga" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Harga (Rp) <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">Rp</span>
                        <input type="number" 
                               id="harga" 
                               name="harga" 
                               value="{{ old('harga', (int)$item->harga) }}" 
                               required 
                               min="0"
                               step="100"
                               class="w-full bg-brand-surface border {{ $errors->has('harga') ? 'border-red-500' : 'border-brand-border' }} rounded-xl pl-10 pr-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-brand-cyan transition-colors">
                    </div>
                    @error('harga')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nilai Maks (Langkah / Limit) -->
                <div>
                    <label for="nilai_maks" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nilai Maksimum
                    </label>
                    <input type="number" 
                           id="nilai_maks" 
                           name="nilai_maks" 
                           value="{{ old('nilai_maks', $item->nilai_maks) }}" 
                           min="1"
                           {{ $layanan->tipe_harga->value === 'progres' ? 'disabled' : '' }}
                           class="w-full bg-brand-surface border {{ $errors->has('nilai_maks') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan transition-colors {{ $layanan->tipe_harga->value === 'progres' ? 'opacity-50 cursor-not-allowed' : '' }}">
                    @error('nilai_maks')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Satuan -->
                <div>
                    <label for="satuan" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nama Satuan
                    </label>
                    <input type="text" 
                           id="satuan" 
                           name="satuan" 
                           value="{{ old('satuan', $item->satuan) }}" 
                           placeholder="{{ $layanan->tipe_harga->value === 'tangga' ? 'Bintang' : 'Hari / Match' }}" 
                           maxlength="50"
                           class="w-full bg-brand-surface border {{ $errors->has('satuan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    @error('satuan')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Status Aktif -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface/40 border border-brand-border">
                <div>
                    <span class="block text-sm font-semibold text-white">Status Aktif Item</span>
                    <span class="block text-xs text-slate-400">Nonaktifkan untuk menyembunyikan item ini dari kalkulator tanpa menghapusnya dari database.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="aktif" 
                           value="1" 
                           {{ old('aktif', $item->aktif) ? 'checked' : '' }} 
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-brand-border">
                <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
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
