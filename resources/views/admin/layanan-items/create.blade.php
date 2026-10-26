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
                <span class="text-brand-cyan font-medium">Tambah Item</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Tambah Item Layanan
            </h2>
            <p class="text-xs text-slate-400">
                Layanan: <span class="text-brand-cyan font-semibold">{{ $layanan->nama_layanan }}</span> ({{ $layanan->game->nama_game }}) • Metode: <span class="text-white font-medium">{{ $layanan->tipe_harga->label() }}</span>
            </p>
        </div>
        <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" 
              action="{{ route('admin.layanan.items.store', $layanan) }}" 
              class="space-y-6"
              x-data="{ 
                  jenis: '{{ old('jenis', 'utama') }}',
                  tipeHarga: '{{ $layanan->tipe_harga->value }}'
              }">
            @csrf

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
                    <p class="text-[11px] text-slate-400 mt-1.5">
                        <span x-show="jenis === 'utama'">Item tingkatan rank utama atau paket yang wajib dihitung.</span>
                        <span x-show="jenis === 'tambahan'">Opsi ekstra opsional (contoh: request hero, streaming, fast-track).</span>
                    </p>
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
                           value="{{ old('kategori') }}" 
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
                            <option value="{{ $pc->id }}" {{ old('parent_id') == $pc->id ? 'selected' : '' }}>
                                {{ $pc->nama }} (Induk Region)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400">
                        Pilih Region induk jika item ini merupakan wilayah turunan. Jika item ini adalah Region penampung, biarkan kosong (harga otomatis Rp 0).
                    </p>
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
                           value="{{ old('nama') }}" 
                           placeholder="{{ $layanan->tipe_harga->value === 'tangga' ? 'Contoh: Grandmaster, Epic, Legend' : 'Contoh: City of Freedom, Request Hero Khusus' }}" 
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
                           value="{{ old('urutan', $nextUrutan) }}" 
                           required 
                           min="0"
                           class="w-full bg-brand-surface border {{ $errors->has('urutan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1.5">Urutan tampilan dari bawah ke atas.</p>
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
                               value="{{ old('harga', '0') }}" 
                               required 
                               min="0"
                               step="100"
                               class="w-full bg-brand-surface border {{ $errors->has('harga') ? 'border-red-500' : 'border-brand-border' }} rounded-xl pl-10 pr-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-brand-cyan transition-colors">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">
                        @if($layanan->tipe_harga->value === 'tangga')
                            Tarif per 1 bintang / 1 langkah.
                        @elseif($layanan->tipe_harga->value === 'progres')
                            Tarif dasar 100% (isi 0 jika induk).
                        @else
                            Tarif per item / per paket.
                        @endif
                    </p>
                    @error('harga')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nilai Maks (Langkah / Limit) -->
                <div>
                    <label for="nilai_maks" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nilai Maksimum
                        @if($layanan->tipe_harga->value === 'tangga')
                            <span class="text-red-400">*</span>
                        @endif
                    </label>
                    <input type="number" 
                           id="nilai_maks" 
                           name="nilai_maks" 
                           value="{{ old('nilai_maks', $layanan->tipe_harga->value === 'tangga' ? '5' : '') }}" 
                           min="1"
                           {{ $layanan->tipe_harga->value === 'progres' ? 'disabled' : '' }}
                           class="w-full bg-brand-surface border {{ $errors->has('nilai_maks') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-cyan transition-colors {{ $layanan->tipe_harga->value === 'progres' ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <p class="text-[11px] text-slate-400 mt-1.5">
                        @if($layanan->tipe_harga->value === 'tangga')
                            Jumlah bintang per tingkat (misal: 4, 5, atau 25).
                        @elseif($layanan->tipe_harga->value === 'progres')
                            Terkunci (otomatis persentase 0-100%).
                        @else
                            Batas maks kuantitas jika ada satuan.
                        @endif
                    </p>
                    @error('nilai_maks')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Satuan (Opsional) -->
                <div>
                    <label for="satuan" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nama Satuan
                    </label>
                    <input type="text" 
                           id="satuan" 
                           name="satuan" 
                           value="{{ old('satuan', $layanan->tipe_harga->value === 'tangga' ? 'Bintang' : '') }}" 
                           placeholder="{{ $layanan->tipe_harga->value === 'tangga' ? 'Bintang' : 'Hari / Match / Pcs' }}" 
                           maxlength="50"
                           class="w-full bg-brand-surface border {{ $errors->has('satuan') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1.5">Contoh: Bintang, Hari, Pertandingan.</p>
                    @error('satuan')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Status Aktif -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-brand-surface/40 border border-brand-border">
                <div>
                    <span class="block text-sm font-semibold text-white">Status Aktif Item</span>
                    <span class="block text-xs text-slate-400">Jika aktif, item ini akan muncul dalam kalkulator dan formulir order pemesan.</span>
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
                <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
                   class="px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                    Simpan Item
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
