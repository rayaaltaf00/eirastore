<x-admin-layout title="Laporan Transaksi - EiraStore">

    <style>
        @media print {
            .no-print, nav, aside, header, footer {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .print-only {
                display: block !important;
            }
            .print-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #ddd !important;
                color: black !important;
                padding: 6px 10px !important;
            }
            th {
                background-color: #f3f4f6 !important;
            }
        }
        .print-only {
            display: none;
        }
    </style>

    <div class="space-y-6">

        <!-- ============================================== -->
        <!-- HEADER & BREADCRUMBS (No Print)                -->
        <!-- ============================================== -->
        <div class="no-print flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-cyan transition-colors">Admin</a>
                    <span>/</span>
                    <span class="text-white font-medium">Laporan Transaksi</span>
                </div>
                <h1 class="text-2xl font-black text-white">Laporan Transaksi & Pendapatan (A-30)</h1>
                <p class="text-xs text-slate-400 mt-1">Laporan rekapitulasi pesanan joki berstatus selesai dan total perolehan transaksi.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="window.print()"
                        class="px-5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs font-bold hover:bg-white/10 flex items-center gap-2 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- HEADER KHUSUS CETAK PRINT                      -->
        <!-- ============================================== -->
        <div class="print-only mb-6">
            <h1 class="text-2xl font-bold text-black uppercase">EiraStore - Laporan Transaksi Joki Game</h1>
            <p class="text-sm text-gray-600">
                Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
                <br>
                Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </p>
            <hr class="my-3 border-gray-300">
        </div>

        <!-- ============================================== -->
        <!-- FILTER PERIODE FORM (No Print)                 -->
        <!-- ============================================== -->
        <div class="no-print bg-brand-card/90 border border-brand-border rounded-2xl p-5 shadow-card">
            <form action="{{ route('admin.laporan-transaksi.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="flex-1 w-full sm:w-auto">
                    <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Mulai:</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $tanggalMulai }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                </div>

                <div class="flex-1 w-full sm:w-auto">
                    <label for="tanggal_akhir" class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Akhir:</label>
                    <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-white text-xs focus:border-brand-cyan focus:outline-none">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-brand-blue hover:bg-brand-blueHover text-white text-xs font-bold transition-all shadow-glow-blue flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Tampilkan</span>
                    </button>

                    <a href="{{ route('admin.laporan-transaksi.index') }}"
                       class="px-4 py-2.5 rounded-xl bg-brand-surface border border-brand-border text-slate-400 hover:text-white text-xs font-semibold transition-colors"
                       title="Reset Periode Bulan Ini">
                        Reset
                    </a>
                </div>
            </form>

            @error('tanggal_akhir')
                <p class="text-rose-400 text-xs font-medium mt-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <!-- ============================================== -->
        <!-- KARTU RINGKASAN METRIK (3 STATS)               -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- 1. Total Pesanan Selesai -->
            <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-5 shadow-card">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pesanan Selesai</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-2xl sm:text-3xl font-black text-white">{{ number_format($totalPesanan, 0, ',', '.') }}</span>
                    <span class="text-xs text-slate-400">transaksi</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Periode terpilih</p>
            </div>

            <!-- 2. Total Pendapatan -->
            <div class="bg-brand-card/90 border border-emerald-500/30 rounded-2xl p-5 shadow-card">
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">Total Pendapatan</span>
                <div class="flex items-baseline gap-1 mt-2">
                    <span class="text-xs font-bold text-emerald-400">Rp</span>
                    <span class="text-2xl sm:text-3xl font-black text-white">{{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Akumulasi nilai pesanan selesai</p>
            </div>

            <!-- 3. Rata-Rata Transaksi -->
            <div class="bg-brand-card/90 border border-brand-blue/30 rounded-2xl p-5 shadow-card">
                <span class="text-xs font-bold text-brand-cyan uppercase tracking-wider block">Rata-Rata Transaksi</span>
                <div class="flex items-baseline gap-1 mt-2">
                    <span class="text-xs font-bold text-brand-cyan">Rp</span>
                    <span class="text-2xl sm:text-3xl font-black text-white">{{ number_format($rataRataTransaksi, 0, ',', '.') }}</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Rata-rata pendapatan per pesanan</p>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TABEL LAPORAN TRANSAKSI                        -->
        <!-- ============================================== -->
        <div class="bg-brand-card/90 border border-brand-border rounded-2xl overflow-hidden shadow-card print-container">
            <div class="p-5 border-b border-brand-border flex items-center justify-between no-print">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Daftar Transaksi Selesai</span>
                </h3>
                <span class="text-xs text-slate-400 font-mono">{{ $totalPesanan }} Data Ditemukan</span>
            </div>

            @if($pesananList->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-brand-surface border border-brand-border text-slate-500 flex items-center justify-center mx-auto">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-white">Tidak ada transaksi</p>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Tidak ditemukan pesanan berstatus selesai pada rentang tanggal yang dipilih.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-brand-border bg-brand-surface/60 text-slate-400 text-[11px] uppercase font-bold tracking-wider">
                                <th class="py-3.5 px-4">No</th>
                                <th class="py-3.5 px-4">Kode Pesanan</th>
                                <th class="py-3.5 px-4">Tanggal Selesai</th>
                                <th class="py-3.5 px-4">Customer</th>
                                <th class="py-3.5 px-4">Game & Layanan</th>
                                <th class="py-3.5 px-4">Worker</th>
                                <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border/40 text-slate-300">
                            @foreach($pesananList as $index => $item)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="py-3.5 px-4 font-mono text-slate-500 text-center w-12">{{ $pesananList instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pesananList->firstItem() + $index : $index + 1 }}</td>
                                    
                                    <td class="py-3.5 px-4 font-mono font-bold text-brand-cyan">
                                        <a href="{{ route('admin.pesanan.show', $item->kode_pesanan) }}" class="hover:underline">
                                            {{ $item->kode_pesanan }}
                                        </a>
                                    </td>

                                    <td class="py-3.5 px-4 text-slate-300">
                                        {{ ($item->selesai_at ?? $item->updated_at)?->format('d M Y, H:i') }} WIB
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-white">{{ $item->customer?->nama ?? 'Tamu' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $item->customer?->email ?? '-' }}</div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-white">{{ $item->layanan?->nama_layanan ?? 'Layanan' }}</div>
                                        <div class="text-[11px] text-brand-cyan font-bold">{{ $item->layanan?->game?->nama_game ?? '-' }}</div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        @if($item->worker)
                                            <span class="text-white font-medium">{{ $item->worker->nama }}</span>
                                        @else
                                            <span class="text-slate-500 italic">-</span>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-black text-white font-mono">
                                        Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-brand-surface border-t-2 border-brand-border font-bold text-white">
                                <td colspan="6" class="py-4 px-4 text-right uppercase tracking-wider text-xs">Total Pendapatan Bersih:</td>
                                <td class="py-4 px-4 text-right font-black font-mono text-emerald-400 text-sm">
                                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($pesananList instanceof \Illuminate\Pagination\LengthAwarePaginator && $pesananList->hasPages())
                    <div class="p-4 border-t border-brand-border no-print">
                        {{ $pesananList->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>

</x-admin-layout>
