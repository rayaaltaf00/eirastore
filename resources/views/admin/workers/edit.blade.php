@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.workers.index') }}" class="hover:text-white transition-colors">Manajemen Worker</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">Ubah Data</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Ubah Profil Worker: {{ $worker->nama }}
            </h2>
        </div>
        <a href="{{ route('admin.workers.index') }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Status Beban Kerja Card -->
    @php
        $activeOrders = $worker->pesanan_aktif_count ?? $worker->jumlah_pesanan_aktif;
    @endphp
    @if($activeOrders > 0)
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="text-xs text-slate-300 leading-relaxed">
                <p class="font-semibold text-amber-400">Worker Sedang Memiliki {{ $activeOrders }} Pesanan Aktif</p>
                <p class="text-slate-400 mt-0.5">Worker ini tidak dapat dinonaktifkan sampai seluruh pesanan yang sedang ditangani selesai diproses atau dialihkan ke worker lain.</p>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" action="{{ route('admin.workers.update', $worker) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Nama Lengkap Worker <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="nama" 
                       name="nama" 
                       value="{{ old('nama', $worker->nama) }}" 
                       required 
                       maxlength="255"
                       class="w-full bg-brand-surface border {{ $errors->has('nama') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                @error('nama')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email & Nomor WhatsApp (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Alamat Email <span class="text-red-400">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $worker->email) }}" 
                           required 
                           maxlength="255"
                           class="w-full bg-brand-surface border {{ $errors->has('email') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor HP / WhatsApp -->
                <div>
                    <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nomor WhatsApp / HP <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                       id="no_hp" 
                       name="no_hp" 
                       value="{{ old('no_hp', $worker->no_hp) }}" 
                       required 
                       maxlength="20"
                       class="w-full bg-brand-surface border {{ $errors->has('no_hp') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('no_hp')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Akun Worker -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Status Akun <span class="text-red-400">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex items-center p-3.5 rounded-xl border {{ old('status_akun', $worker->status_akun) === 'aktif' ? 'border-emerald-500 bg-emerald-500/10' : 'border-brand-border bg-brand-surface' }} cursor-pointer hover:border-emerald-500/50 transition-colors">
                        <input type="radio" 
                               name="status_akun" 
                               value="aktif" 
                               {{ old('status_akun', $worker->status_akun) === 'aktif' ? 'checked' : '' }} 
                               class="text-emerald-500 focus:ring-emerald-500 h-4 w-4 bg-brand-surface border-slate-700">
                        <div class="ml-3">
                            <span class="block text-sm font-bold text-white">Aktif</span>
                            <span class="block text-xs text-slate-400">Dapat menerima penugasan joki baru</span>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3.5 rounded-xl border {{ old('status_akun', $worker->status_akun) === 'nonaktif' ? 'border-red-500 bg-red-500/10' : 'border-brand-border bg-brand-surface' }} cursor-pointer hover:border-red-500/50 transition-colors">
                        <input type="radio" 
                               name="status_akun" 
                               value="nonaktif" 
                               {{ old('status_akun', $worker->status_akun) === 'nonaktif' ? 'checked' : '' }} 
                               class="text-red-500 focus:ring-red-500 h-4 w-4 bg-brand-surface border-slate-700">
                        <div class="ml-3">
                            <span class="block text-sm font-bold text-white">Nonaktif</span>
                            <span class="block text-xs text-slate-400">Tidak dapat login & tidak menerima pesanan</span>
                        </div>
                    </label>
                </div>
                @error('status_akun')
                    <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ganti Password (Opsional) -->
            <div class="pt-4 border-t border-brand-border/60">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-white">Ganti Password (Opsional)</h3>
                    <p class="text-xs text-slate-400">Biarkan kedua bidang di bawah kosong jika Anda tidak ingin mengganti password worker.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Password Baru
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Minimal 8 karakter" 
                               minlength="8"
                               class="w-full bg-brand-surface border {{ $errors->has('password') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                        @error('password')
                            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Ulangi Password Baru
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Ketik ulang password baru" 
                               minlength="8"
                               class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                <a href="{{ route('admin.workers.index') }}" 
                   class="px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-sm font-semibold text-slate-400 hover:text-white hover:bg-brand-surface/80 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
