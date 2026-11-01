@extends('layouts.storefront')

@section('title', 'Pengaturan Profil - EiraStore')

@section('content')
<div class="py-8 lg:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Breadcrumb & Header -->
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                <a href="{{ route('beranda') }}" class="hover:text-brand-cyan transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-200">Pengaturan Profil</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white flex items-center gap-3">
                        <span>Pengaturan Akun</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-blue/20 text-brand-cyan border border-brand-blue/30 capitalize">
                            {{ $user->role }}
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Kelola data identitas, kontak WhatsApp, dan keamanan akun EiraStore Anda</p>
                </div>

                @if($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-slate-300 hover:text-white hover:border-slate-600 text-xs font-semibold transition-all self-start sm:self-auto">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Kembali ke Panel Admin</span>
                    </a>
                @elseif($user->isWorker())
                    <a href="{{ route('worker.pesanan') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-slate-300 hover:text-white hover:border-slate-600 text-xs font-semibold transition-all self-start sm:self-auto">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Pesanan Saya (Worker)</span>
                    </a>
                @else
                    <a href="{{ route('pesanan.saya') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-surface border border-brand-border text-slate-300 hover:text-white hover:border-slate-600 text-xs font-semibold transition-all self-start sm:self-auto">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Pesanan Saya</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- User Quick Card -->
        <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-brand-card via-brand-surface to-brand-card border border-brand-border flex items-center gap-4 sm:gap-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-blue to-brand-cyan flex items-center justify-center text-white text-xl font-black shadow-glow-blue shrink-0">
                {{ strtoupper(substr($user->nama, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-base sm:text-lg font-bold text-white truncate">{{ $user->nama }}</div>
                <div class="text-xs sm:text-sm text-slate-400 truncate">{{ $user->email }}</div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-3">
                    <span>Bergabung sejak {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    @if($user->no_hp)
                        <span>•</span>
                        <span class="text-emerald-400 font-medium">WA: {{ $user->no_hp }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 1: Update Profile Info -->
        <div class="p-6 sm:p-8 bg-brand-card border border-brand-border rounded-3xl shadow-card relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-brand-blue to-brand-cyan"></div>
            @include('profile.partials.update-profile-information-form')
        </div>

        <!-- Section 2: Update Password -->
        <div class="p-6 sm:p-8 bg-brand-card border border-brand-border rounded-3xl shadow-card relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-brand-cyan to-indigo-500"></div>
            @include('profile.partials.update-password-form')
        </div>

        <!-- Section 3: Delete Account -->
        @if(! $user->isAdmin() && ! $user->isWorker())
            <div class="p-6 sm:p-8 bg-brand-card border border-red-500/30 rounded-3xl shadow-card relative overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-red-500 to-rose-600"></div>
                @include('profile.partials.delete-user-form')
            </div>
        @endif

    </div>
</div>
@endsection
