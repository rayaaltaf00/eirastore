@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.metode-pembayaran.index') }}" class="hover:text-brand-cyan transition-colors">Rekening Pembayaran</a>
                <span>/</span>
                <span class="text-white">Ubah</span>
            </div>
            <h2 class="text-xl font-bold text-white">Ubah Rekening / QRIS</h2>
        </div>
        <a href="{{ route('admin.metode-pembayaran.index') }}" 
           class="px-4 py-2 rounded-xl bg-brand-surface text-slate-300 text-xs font-semibold hover:bg-brand-surface/80 border border-brand-border transition-colors">
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 shadow-card">
        <form action="{{ route('admin.metode-pembayaran.update', $metode) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Metode -->
            <div>
                <label for="nama_metode" class="block text-xs font-semibold text-slate-300 mb-2">
                    Nama Bank / E-Wallet / Metode <span class="text-rose-400">*</span>
                </label>
                <input type="text" id="nama_metode" name="nama_metode" value="{{ old('nama_metode', $metode->nama_metode) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none focus:ring-1 focus:ring-brand-cyan transition-colors">
                @error('nama_metode')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Kategori -->
                <div>
                    <label for="kategori" class="block text-xs font-semibold text-slate-300 mb-2">
                        Kategori Pembayaran <span class="text-rose-400">*</span>
                    </label>
                    <select id="kategori" name="kategori" required
                            class="w-full px-4 py-3 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none focus:ring-1 focus:ring-brand-cyan transition-colors">
                        <option value="bank" {{ old('kategori', $metode->kategori) === 'bank' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="ewallet" {{ old('kategori', $metode->kategori) === 'ewallet' ? 'selected' : '' }}>E-Wallet (Dana/GoPay/OVO)</option>
                        <option value="qris" {{ old('kategori', $metode->kategori) === 'qris' ? 'selected' : '' }}>QRIS (Scan All Bank)</option>
                    </select>
                    @error('kategori')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Urutan -->
                <div>
                    <label for="urutan" class="block text-xs font-semibold text-slate-300 mb-2">
                        Urutan Tampilan
                    </label>
                    <input type="number" id="urutan" name="urutan" value="{{ old('urutan', $metode->urutan) }}" min="0"
                           class="w-full px-4 py-3 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('urutan')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nomor Rekening -->
                <div>
                    <label for="nomor_rekening" class="block text-xs font-semibold text-slate-300 mb-2">
                        Nomor Rekening / No. HP E-Wallet
                    </label>
                    <input type="text" id="nomor_rekening" name="nomor_rekening" value="{{ old('nomor_rekening', $metode->nomor_rekening) }}"
                           placeholder="Kosongkan jika hanya scan QRIS"
                           class="w-full px-4 py-3 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('nomor_rekening')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Atas Nama -->
                <div>
                    <label for="atas_nama" class="block text-xs font-semibold text-slate-300 mb-2">
                        Atas Nama Rekening / Merchant <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="atas_nama" name="atas_nama" value="{{ old('atas_nama', $metode->atas_nama) }}" required
                           class="w-full px-4 py-3 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('atas_nama')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Upload Logo Bank/E-wallet -->
            <div class="p-4 rounded-xl bg-brand-surface/60 border border-brand-border space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-white">Logo Bank / E-Wallet</label>
                    @if($metode->logo_url)
                        <img src="{{ $metode->logo_url }}" alt="Logo Saat Ini" class="w-8 h-8 object-contain rounded bg-white/5 p-0.5 border border-brand-border">
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="logo" class="block text-[11px] text-slate-400 mb-1">Ganti Berkas Logo (Max 2MB)</label>
                        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp"
                               class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-blue/20 file:text-brand-cyan hover:file:bg-brand-blue/30 cursor-pointer">
                    </div>
                    <div>
                        <label for="logo_url" class="block text-[11px] text-slate-400 mb-1">Atau Perbarui Tautan URL</label>
                        <input type="url" id="logo_url" name="logo_url" value="{{ old('logo_url', str_starts_with($metode->logo ?? '', 'http') ? $metode->logo : '') }}"
                               placeholder="https://..."
                               class="w-full px-3 py-2 rounded-lg bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                    </div>
                </div>
                @error('logo') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
                @error('logo_url') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            <!-- Upload Barcode QRIS -->
            <div class="p-4 rounded-xl bg-brand-surface/60 border border-brand-border space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-white">Gambar Barcode QRIS</label>
                    @if($metode->gambar_qris_url)
                        <img src="{{ $metode->gambar_qris_url }}" alt="QRIS Saat Ini" class="w-10 h-10 object-contain rounded bg-white p-0.5 border border-brand-border">
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="gambar_qris" class="block text-[11px] text-slate-400 mb-1">Ganti Berkas QRIS (Max 2MB)</label>
                        <input type="file" id="gambar_qris" name="gambar_qris" accept="image/png,image/jpeg,image/webp"
                               class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-blue/20 file:text-brand-cyan hover:file:bg-brand-blue/30 cursor-pointer">
                    </div>
                    <div>
                        <label for="gambar_qris_url" class="block text-[11px] text-slate-400 mb-1">Atau Perbarui Tautan URL</label>
                        <input type="url" id="gambar_qris_url" name="gambar_qris_url" value="{{ old('gambar_qris_url', str_starts_with($metode->gambar_qris ?? '', 'http') ? $metode->gambar_qris : '') }}"
                               placeholder="https://..."
                               class="w-full px-3 py-2 rounded-lg bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                    </div>
                </div>
                @error('gambar_qris') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
                @error('gambar_qris_url') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            <!-- Petunjuk Transfer -->
            <div>
                <label for="petunjuk_transfer" class="block text-xs font-semibold text-slate-300 mb-2">
                    Petunjuk / Catatan Transfer (Opsional)
                </label>
                <textarea id="petunjuk_transfer" name="petunjuk_transfer" rows="3"
                          class="w-full px-4 py-3 rounded-xl bg-brand-surface border border-brand-border text-white text-sm focus:border-brand-cyan focus:outline-none focus:ring-1 focus:ring-brand-cyan transition-colors">{{ old('petunjuk_transfer', $metode->petunjuk_transfer) }}</textarea>
                @error('petunjuk_transfer')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-3">
                <input type="checkbox" id="aktif" name="aktif" value="1" {{ old('aktif', $metode->aktif ? '1' : '0') == '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-brand-surface border-brand-border text-brand-blue focus:ring-brand-cyan">
                <label for="aktif" class="text-sm font-semibold text-white cursor-pointer">
                    Metode Aktif (Dapat dipilih customer saat checkout)
                </label>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                <a href="{{ route('admin.metode-pembayaran.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-brand-surface text-slate-300 text-xs font-semibold hover:bg-brand-surface/80 border border-brand-border transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold hover:opacity-95 shadow-glow-blue transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
