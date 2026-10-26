@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Rekening Pembayaran Tujuan
            </h2>
            <p class="text-xs text-slate-400 mt-1">Kelola daftar bank, e-wallet, dan QRIS toko untuk menerima transfer pembayaran dari customer</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.metode-pembayaran.create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-sm font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Rekening / QRIS
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabel Rekening -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl overflow-hidden shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-brand-surface/80 text-xs uppercase text-slate-400 border-b border-brand-border">
                    <tr>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Metode / Bank</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Nomor Rekening</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Atas Nama</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">Urutan</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($metodes as $metode)
                        <tr class="hover:bg-brand-surface/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($metode->logo_url)
                                        <img src="{{ $metode->logo_url }}" alt="{{ $metode->nama_metode }}" class="w-10 h-10 object-contain rounded-lg bg-white/5 p-1 border border-brand-border">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-brand-surface border border-brand-border flex items-center justify-center text-brand-cyan text-xs font-bold">
                                            {{ substr($metode->nama_metode, 0, 3) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-bold text-white block">{{ $metode->nama_metode }}</span>
                                        @if($metode->petunjuk_transfer)
                                            <span class="text-xs text-slate-400 line-clamp-1 max-w-xs">{{ $metode->petunjuk_transfer }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($metode->kategori === 'bank')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 whitespace-nowrap">Transfer Bank</span>
                                @elseif($metode->kategori === 'ewallet')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20 whitespace-nowrap">E-Wallet</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 whitespace-nowrap">QRIS Scan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($metode->nomor_rekening)
                                    <code class="px-2.5 py-1 rounded-lg bg-brand-surface text-brand-cyan font-mono text-xs border border-brand-border font-bold">
                                        {{ $metode->nomor_rekening }}
                                    </code>
                                @else
                                    <span class="text-xs text-slate-500 italic">Scan QRIS (Tanpa Rekening)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-white font-medium">
                                {{ $metode->atas_nama }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-400">
                                {{ $metode->urutan }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($metode->aktif)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Nonaktif / Gangguan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.metode-pembayaran.edit', $metode) }}" 
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-brand-surface hover:bg-brand-surface/80 text-brand-cyan border border-brand-border transition-colors">
                                        Ubah
                                    </a>

                                    <form action="{{ route('admin.metode-pembayaran.destroy', $metode) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus metode pembayaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <p class="text-sm font-semibold">Belum ada metode pembayaran yang terdaftar</p>
                                <p class="text-xs text-slate-500 mt-1">Tambahkan rekening bank atau QRIS agar customer dapat melakukan pembayaran</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
