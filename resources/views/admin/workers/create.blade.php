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
                <span class="text-brand-cyan font-medium">Tambah Worker</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Pendaftaran Akun Worker Baru
            </h2>
        </div>
        <a href="{{ route('admin.workers.index') }}" 
           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white hover:bg-brand-surface/80 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Info Box -->
    <div class="p-4 rounded-2xl bg-brand-blue/10 border border-brand-blue/30 flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-brand-blue/20 flex items-center justify-center text-brand-cyan shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="text-xs text-slate-300 leading-relaxed">
            <p class="font-semibold text-white">Ketentuan Akun Worker</p>
            <p class="text-slate-400 mt-0.5">Akun yang didaftarkan akan otomatis memiliki role <span class="text-brand-cyan font-semibold">Worker</span> dan berstatus <span class="text-emerald-400 font-semibold">Aktif</span>. Worker dapat langsung login ke portal pengerjaan pesanan menggunakan email dan password ini.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-6 sm:p-8 shadow-card">
        <form method="POST" action="{{ route('admin.workers.store') }}" class="space-y-6">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Nama Lengkap Worker <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="nama" 
                       name="nama" 
                       value="{{ old('nama') }}" 
                       placeholder="Contoh: Budi Santoso" 
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
                           value="{{ old('email') }}" 
                           placeholder="worker@eirastore.id" 
                           required 
                           maxlength="255"
                           class="w-full bg-brand-surface border {{ $errors->has('email') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-500 mt-1">Digunakan untuk login ke panel pengerjaan.</p>
                </div>

                <!-- Nomor HP / WhatsApp -->
                <div>
                    <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Nomor WhatsApp / HP <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="no_hp" 
                           name="no_hp" 
                           value="{{ old('no_hp') }}" 
                           placeholder="081234567890" 
                           required 
                           maxlength="20"
                           class="w-full bg-brand-surface border {{ $errors->has('no_hp') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('no_hp')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-500 mt-1">Format Indonesia (awali 08 atau 62). Untuk koordinasi tugas.</p>
                </div>
            </div>

            <!-- Password & Konfirmasi (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2 border-t border-brand-border/60">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Password Akun <span class="text-red-400">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Minimal 8 karakter" 
                           required 
                           minlength="8"
                           class="w-full bg-brand-surface border {{ $errors->has('password') ? 'border-red-500' : 'border-brand-border' }} rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
                    @error('password')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Ulangi Password <span class="text-red-400">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="Ketik ulang password" 
                           required 
                           minlength="8"
                           class="w-full bg-brand-surface border border-brand-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
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
                    Daftarkan Worker
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
