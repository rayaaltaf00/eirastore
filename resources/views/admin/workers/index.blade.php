@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Manajemen Tim Worker
            </h2>
            <p class="text-xs text-slate-400 mt-1">Kelola akun worker, pantau beban pengerjaan joki, dan atur status ketersediaan tim</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.workers.create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Tambah Worker Baru
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Worker -->
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Akun Worker</span>
                <p class="text-2xl font-black text-white mt-1">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-brand-blue/15 border border-brand-blue/30 flex items-center justify-center text-brand-cyan">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>

        <!-- Worker Aktif -->
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Worker Aktif</span>
                <p class="text-2xl font-black text-emerald-400 mt-1">{{ $stats['aktif'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Worker Nonaktif -->
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Worker Nonaktif</span>
                <p class="text-2xl font-black text-slate-400 mt-1">{{ $stats['nonaktif'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-500/15 border border-slate-500/30 flex items-center justify-center text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
        </div>

        <!-- Worker Sedang Bekerja -->
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4 flex items-center justify-between shadow-card">
            <div>
                <span class="text-xs text-slate-400 font-medium">Sedang Memproses Pesanan</span>
                <p class="text-2xl font-black text-amber-400 mt-1">{{ $stats['sedang_bekerja'] ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-4">
        <form method="GET" action="{{ route('admin.workers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Cari nama worker, email, atau nomor HP..." 
                       class="w-full bg-brand-surface border border-brand-border rounded-xl pl-10 pr-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan transition-colors">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" 
                        onchange="this.form.submit()"
                        class="w-full bg-brand-surface border border-brand-border rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-brand-cyan">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Hanya Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Hanya Nonaktif</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-brand-surface hover:bg-brand-surface/80 border border-brand-border text-sm font-medium text-slate-200 transition-colors">
                Filter
            </button>

            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.workers.index') }}" class="px-4 py-2 rounded-xl bg-brand-surface hover:bg-brand-surface/80 border border-brand-border text-sm font-medium text-slate-400 hover:text-white transition-colors text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl overflow-hidden shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-brand-surface/60 border-b border-brand-border text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Worker</th>
                        <th class="py-3.5 px-4">Kontak (WhatsApp)</th>
                        <th class="py-3.5 px-4 text-center">Status Akun</th>
                        <th class="py-3.5 px-4 text-center">Pesanan Aktif</th>
                        <th class="py-3.5 px-4">Bergabung</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($workers as $worker)
                        <tr class="hover:bg-brand-surface/30 transition-colors">
                            <!-- Worker Info -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-blue/30 to-brand-cyan/20 border border-brand-blue/30 flex items-center justify-center font-bold text-brand-cyan shrink-0">
                                        {{ strtoupper(substr($worker->nama, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate">{{ $worker->nama }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ $worker->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak WhatsApp -->
                            <td class="py-4 px-4">
                                @if($worker->no_hp)
                                    @php
                                        $waTarget = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $worker->no_hp));
                                    @endphp
                                    <a href="https://wa.me/{{ $waTarget }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-lg transition-colors"
                                       title="Chat via WhatsApp">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        {{ $worker->no_hp }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500 italic">-</span>
                                @endif
                            </td>

                            <!-- Status Akun -->
                            <td class="py-4 px-4 text-center">
                                @if($worker->status_akun === 'aktif')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-500/10 text-slate-400 border border-slate-500/30">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Pesanan Aktif -->
                            <td class="py-4 px-4 text-center">
                                @php
                                    $activeCount = $worker->pesanan_aktif_count ?? $worker->jumlah_pesanan_aktif;
                                @endphp
                                @if($activeCount > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30" title="Worker sedang mengerjakan {{ $activeCount }} pesanan">
                                        <svg class="w-3.5 h-3.5 mr-1 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $activeCount }} Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                                        Standby (0)
                                    </span>
                                @endif
                            </td>

                            <!-- Tanggal Bergabung -->
                            <td class="py-4 px-4 text-xs text-slate-400">
                                {{ $worker->created_at ? $worker->created_at->translatedFormat('d M Y') : '-' }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.workers.edit', $worker) }}" 
                                       class="p-2 rounded-lg bg-brand-surface border border-brand-border text-slate-300 hover:text-brand-cyan hover:border-brand-cyan/50 transition-colors"
                                       title="Ubah Data Worker">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <!-- Tombol Toggle Status -->
                                    <form method="POST" 
                                          action="{{ route('admin.workers.toggle-status', $worker) }}" 
                                          onsubmit="return confirm('{{ $worker->status_akun === 'aktif' ? 'Apakah Anda yakin ingin menonaktifkan worker ' . addslashes($worker->nama) . '?' : 'Aktifkan kembali worker ' . addslashes($worker->nama) . '?' }}')">
                                        @csrf
                                        @method('PATCH')

                                        @if($worker->status_akun === 'aktif')
                                            @if($activeCount > 0)
                                                <button type="submit" 
                                                        class="p-2 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 hover:bg-amber-500/20 transition-colors"
                                                        title="Nonaktifkan (Perhatian: Worker memiliki {{ $activeCount }} pesanan aktif)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            @else
                                                <button type="submit" 
                                                        class="p-2 rounded-lg bg-brand-surface border border-brand-border text-slate-400 hover:text-red-400 hover:border-red-500/40 transition-colors"
                                                        title="Nonaktifkan Worker">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                            @endif
                                        @else
                                            <button type="submit" 
                                                    class="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 transition-colors"
                                                    title="Aktifkan Kembali Worker">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-brand-surface border border-brand-border flex items-center justify-center text-slate-500">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-300">Belum Ada Data Worker</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    @if(request()->hasAny(['q', 'status']))
                                        Tidak ada worker yang sesuai dengan kriteria pencarian / filter Anda.
                                    @else
                                        Mulai dengan mendaftarkan worker pertama Anda untuk mengerjakan pesanan joki.
                                    @endif
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('admin.workers.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-blue text-xs font-semibold text-white shadow-glow-blue hover:opacity-90 transition-all">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Tambah Worker Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workers->hasPages())
            <div class="p-4 border-t border-brand-border bg-brand-surface/30">
                {{ $workers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
