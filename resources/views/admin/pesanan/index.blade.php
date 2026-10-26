@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-cyan transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-white font-medium">Manajemen Pesanan</span>
            </nav>
            <h1 class="text-2xl font-black text-white">Daftar Pesanan Joki Game</h1>
            <p class="text-xs text-slate-400 mt-1">Verifikasi pembayaran transfer, validasi akun game customer, dan pantau penugasan worker.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pesanan.index', ['status' => 'semua']) }}" 
               class="px-4 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-slate-300 hover:text-white text-xs font-bold transition-colors">
                Semua Riwayat
            </a>
            <a href="{{ route('admin.pesanan.index') }}" 
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-bold shadow-glow-blue hover:opacity-95 transition-all">
                Antrean Tindakan Admin
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="p-5 rounded-2xl bg-brand-card/90 border border-brand-border shadow-card space-y-4">
        <form method="GET" action="{{ route('admin.pesanan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            
            <!-- Pencarian Kata Kunci (Col 5) -->
            <div class="sm:col-span-2 lg:col-span-5 relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Kode Pesanan, Pelanggan, atau No WA..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Filter Status Pesanan (Col 3) -->
            <div class="lg:col-span-3">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                    <option value="">Default: Antrean Tindakan Admin</option>
                    <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status Pesanan</option>
                    @foreach(\App\Enums\StatusPesanan::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Pembayaran (Col 2) -->
            <div class="lg:col-span-2">
                <select name="pembayaran" class="w-full px-3 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                    <option value="">Semua Bayar</option>
                    <option value="menunggu_verifikasi" {{ request('pembayaran') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="disetujui" {{ request('pembayaran') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('pembayaran') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Tombol Submit & Reset (Col 2) -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-3 rounded-xl bg-brand-blue hover:bg-blue-600 text-white text-xs font-bold transition-colors text-center">
                    Filter
                </button>
                <a href="{{ route('admin.pesanan.index') }}" class="py-2.5 px-3 rounded-xl bg-brand-surface border border-brand-border text-slate-300 hover:text-white text-xs font-semibold transition-colors" title="Reset Filter">
                    Reset
                </a>
            </div>

        </form>

        <!-- Quick Status Pills -->
        <div class="flex items-center gap-2 pt-2 border-t border-brand-border/60 overflow-x-auto text-[11px] pb-1">
            <span class="text-slate-400 font-semibold shrink-0">Filter Cepat:</span>
            
            <a href="{{ route('admin.pesanan.index') }}" 
               class="px-2.5 py-1 rounded-lg border shrink-0 transition-colors {{ !request()->filled('status') && !request()->filled('filter') ? 'bg-brand-blue/20 text-brand-cyan border-brand-blue/50 font-bold' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white' }}">
                ⚡ Butuh Tindakan
            </a>

            <a href="{{ route('admin.pesanan.index', ['status' => 'menunggu_verifikasi']) }}" 
               class="px-2.5 py-1 rounded-lg border shrink-0 transition-colors {{ request('status') === 'menunggu_verifikasi' ? 'bg-amber-500/20 text-amber-400 border-amber-500/50 font-bold' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white' }}">
                Menunggu Verifikasi
            </a>

            <a href="{{ route('admin.pesanan.index', ['status' => 'siap_ditugaskan']) }}" 
               class="px-2.5 py-1 rounded-lg border shrink-0 transition-colors {{ request('status') === 'siap_ditugaskan' ? 'bg-blue-500/20 text-blue-400 border-blue-500/50 font-bold' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white' }}">
                Siap Ditugaskan
            </a>

            <a href="{{ route('admin.pesanan.index', ['status' => 'diproses']) }}" 
               class="px-2.5 py-1 rounded-lg border shrink-0 transition-colors {{ request('status') === 'diproses' ? 'bg-indigo-500/20 text-indigo-400 border-indigo-500/50 font-bold' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white' }}">
                Diproses
            </a>

            <a href="{{ route('admin.pesanan.index', ['status' => 'sedang_dikerjakan']) }}" 
               class="px-2.5 py-1 rounded-lg border shrink-0 transition-colors {{ request('status') === 'sedang_dikerjakan' ? 'bg-purple-500/20 text-purple-400 border-purple-500/50 font-bold' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white' }}">
                Sedang Dikerjakan
            </a>

            <a href="{{ route('admin.pesanan.index', ['status' => 'selesai']) }}" 
               class="px-2.5 py-1 rounded-lg border shrink-0 transition-colors {{ request('status') === 'selesai' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50 font-bold' : 'bg-brand-surface text-slate-400 border-brand-border hover:text-white' }}">
                Selesai
            </a>
        </div>
    </div>

    <!-- Tabel Daftar Pesanan -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl shadow-card overflow-hidden">
        @if($pesananList->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-brand-surface/80 border-b border-brand-border text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                        <tr>
                            <th class="py-3.5 px-4">Kode & Tanggal</th>
                            <th class="py-3.5 px-4">Customer</th>
                            <th class="py-3.5 px-4">Layanan Game</th>
                            <th class="py-3.5 px-4">Pembayaran</th>
                            <th class="py-3.5 px-4">Worker Ditugaskan</th>
                            <th class="py-3.5 px-4 text-center">Status Pesanan</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/60">
                        @foreach($pesananList as $pesanan)
                            <tr class="hover:bg-brand-surface/30 transition-colors">
                                <!-- Kode & Tanggal -->
                                <td class="py-4 px-4">
                                    <span class="font-mono font-bold text-white text-xs block">{{ $pesanan->kode_pesanan }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $pesanan->created_at->format('d/m/Y H:i') }}</span>
                                </td>

                                <!-- Customer -->
                                <td class="py-4 px-4">
                                    <p class="font-bold text-white text-xs">{{ $pesanan->customer->nama ?? 'Tamu' }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $pesanan->no_wa_customer ?? '-' }}</p>
                                </td>

                                <!-- Layanan Game -->
                                <td class="py-4 px-4">
                                    <span class="text-xs font-semibold text-slate-200 block">{{ $pesanan->layanan->nama_layanan ?? '-' }}</span>
                                    <span class="text-[11px] text-brand-cyan">{{ $pesanan->layanan->game->nama_game ?? '-' }}</span>
                                </td>

                                <!-- Total & Status Pembayaran -->
                                <td class="py-4 px-4">
                                    <span class="text-xs font-bold text-white block">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                                    @if($pesanan->pembayaran)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border mt-1 {{ $pesanan->pembayaran->status->badgeColor() }}">
                                            {{ $pesanan->pembayaran->status->label() }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-500">Belum transfer</span>
                                    @endif
                                </td>

                                <!-- Worker Ditugaskan -->
                                <td class="py-4 px-4">
                                    @if($pesanan->worker)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 font-bold text-[10px] flex items-center justify-center">
                                                {{ strtoupper(substr($pesanan->worker->nama, 0, 1)) }}
                                            </div>
                                            <span class="text-xs font-semibold text-white">{{ $pesanan->worker->nama }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500 italic">Belum ada</span>
                                    @endif
                                </td>

                                <!-- Status Pesanan -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $pesanan->status->badgeColor() }}">
                                        {{ $pesanan->status->label() }}
                                    </span>
                                </td>

                                <!-- Tombol Aksi -->
                                <td class="py-4 px-4 text-right">
                                    <a href="{{ route('admin.pesanan.show', $pesanan->kode_pesanan) }}" 
                                       class="inline-flex items-center px-3 py-1.5 rounded-xl bg-brand-surface border border-brand-border text-xs font-bold text-brand-cyan hover:bg-brand-blue hover:text-white hover:border-transparent transition-all shadow-sm">
                                        Periksa & Kelola &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="p-4 border-t border-brand-border/60">
                {{ $pesananList->links() }}
            </div>
        @else
            <!-- State Kosong -->
            <div class="p-12 text-center space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-brand-surface border border-brand-border text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-base font-bold text-white">Tidak Ada Pesanan Ditemukan</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Tidak ada pesanan yang sesuai dengan kriteria filter yang Anda tentukan.</p>
                <a href="{{ route('admin.pesanan.index', ['status' => 'semua']) }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-brand-surface text-brand-cyan text-xs font-bold border border-brand-border hover:bg-brand-blue hover:text-white transition-colors">
                    Lihat Semua Riwayat Pesanan
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
