<x-worker-layout :header="$tab === 'selesai' ? 'Riwayat Pesanan Selesai' : 'Pesanan Ditugaskan'">
    <div class="space-y-6">

        <!-- Welcome Banner & Summary -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-card to-brand-surface border border-brand-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span>Halo, {{ Auth::user()->nama }}</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-brand-blue/20 text-brand-cyan border border-brand-blue/40 font-mono">
                        Worker #{{ Auth::id() }}
                    </span>
                </h2>
                <p class="text-sm text-slate-400 mt-1">
                    @if($tab === 'selesai')
                        Rekapitulasi seluruh tugas joki yang telah Anda selesaikan dan disetujui.
                    @else
                        Daftar tugas joki yang dialokasikan kepada Anda. Kerjakan dengan teliti dan unggah bukti kemenangan.
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-3.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Akun Worker Aktif
                </div>
            </div>
        </div>

        <!-- Filter Sub-Nav & Pencarian Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-brand-border pb-4">
            <!-- Navigasi Tab Sesuai Konteks Halaman -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                @if($tab === 'selesai')
                    <!-- Di Halaman Riwayat Selesai -->
                    <a href="{{ route('worker.pesanan') }}"
                       class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-brand-surface hover:bg-white/10 text-slate-300 border border-brand-border transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Tugas Aktif</span>
                    </a>
                    <span class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-brand-blue text-white shadow-glow-blue flex items-center gap-2">
                        <span>Riwayat Selesai</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-white/20 text-white font-mono">
                            {{ $countSelesai }}
                        </span>
                    </span>
                @else
                    <!-- Di Halaman Pesanan Ditugaskan (Tugas Aktif & Revisi) -->
                    <a href="{{ route('worker.pesanan', ['tab' => 'aktif', 'q' => request('q')]) }}"
                       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center gap-2 whitespace-nowrap {{ $tab === 'aktif' ? 'bg-brand-blue text-white shadow-glow-blue' : 'bg-brand-surface text-slate-400 hover:text-white border border-brand-border' }}">
                        <span>Semua Tugas Aktif</span>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'aktif' ? 'bg-white/20 text-white' : 'bg-brand-card text-slate-400' }}">
                            {{ $countAktif }}
                        </span>
                    </a>

                    <a href="{{ route('worker.pesanan', ['tab' => 'revisi', 'q' => request('q')]) }}"
                       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center gap-2 whitespace-nowrap {{ $tab === 'revisi' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/20' : 'bg-brand-surface text-slate-400 hover:text-white border border-brand-border' }}">
                        <span>Perlu Revisi</span>
                        @if($countPerluRevisi > 0)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-white text-orange-600 font-black animate-pulse">
                                {{ $countPerluRevisi }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'revisi' ? 'bg-white/20 text-white' : 'bg-brand-card text-slate-400' }}">
                                0
                            </span>
                        @endif
                    </a>
                @endif
            </div>

            <!-- Form Pencarian -->
            <form method="GET" action="{{ route('worker.pesanan') }}" class="relative w-full md:w-80 shrink-0">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Cari kode, game, akun..."
                       class="w-full bg-brand-surface border border-brand-border rounded-xl pl-9 pr-8 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-brand-blue focus:ring-1 focus:ring-brand-blue transition-colors">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if($search)
                    <a href="{{ route('worker.pesanan', ['tab' => $tab]) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-xs">
                        ✕
                    </a>
                @endif
            </form>
        </div>

        <!-- Daftar Kartu Pesanan -->
        @if($daftarPesanan->count() > 0)
            <div class="grid grid-cols-1 gap-4">
                @foreach($daftarPesanan as $pesanan)
                    <div class="bg-brand-card/90 border border-brand-border hover:border-brand-blue/50 rounded-2xl p-5 sm:p-6 transition-all duration-200 shadow-card">
                        
                        <!-- Header Kartu: Info Game + Status + Aksi Utama -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-brand-border/60">
                            <div class="flex items-center gap-3.5 min-w-0">
                                @if($pesanan->layanan?->game?->gambar_url)
                                    <img src="{{ $pesanan->layanan->game->gambar_url }}"
                                         alt="{{ $pesanan->layanan->game->nama_game }}"
                                         class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover border border-brand-border shrink-0 shadow-md">
                                @else
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-brand-surface border border-brand-border flex items-center justify-center text-brand-cyan shrink-0 font-bold text-lg">
                                        {{ substr($pesanan->layanan?->game?->nama_game ?? 'G', 0, 2) }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="font-mono text-xs font-bold text-brand-cyan tracking-wider bg-brand-surface px-2.5 py-1 rounded-lg border border-brand-border/60">
                                            {{ $pesanan->kode_pesanan }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $pesanan->status->badgeColor() }}">
                                            {{ $pesanan->status->label() }}
                                        </span>
                                        @if($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 animate-pulse">
                                                ⚠️ Butuh Tindakan
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base sm:text-lg font-bold text-white tracking-tight">
                                        {{ $pesanan->layanan?->game?->nama_game ?? 'Game' }}
                                        <span class="text-slate-400 font-medium text-sm sm:text-base">— {{ $pesanan->layanan?->nama_layanan ?? 'Paket Joki' }}</span>
                                    </h3>
                                </div>
                            </div>

                            <!-- Tombol Aksi Utama -->
                            <div class="shrink-0">
                                @if($pesanan->status === \App\Enums\StatusPesanan::Diproses)
                                    <a href="{{ route('worker.pesanan.show', $pesanan->kode_pesanan) }}"
                                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-brand-blue hover:bg-brand-blue-hover text-white shadow-glow-blue transition-all">
                                        <span>Mulai Kerjakan</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @elseif($pesanan->status === \App\Enums\StatusPesanan::SedangDikerjakan)
                                    <a href="{{ route('worker.pesanan.show', $pesanan->kode_pesanan) }}"
                                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-purple-600 hover:bg-purple-500 text-white shadow-lg shadow-purple-600/30 transition-all">
                                        <span>Buka & Lapor Hasil</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @elseif($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
                                    <a href="{{ route('worker.pesanan.show', $pesanan->kode_pesanan) }}"
                                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-orange-600 hover:bg-orange-500 text-white shadow-lg shadow-orange-600/30 transition-all">
                                        <span>Perbaiki Sekarang</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @else
                                    <a href="{{ route('worker.pesanan.show', $pesanan->kode_pesanan) }}"
                                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-brand-surface hover:bg-white/10 border border-brand-border text-slate-200 transition-all">
                                        <span>Detail Lembar Kerja</span>
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Alert Permintaan Revisi Jika Ada -->
                        @if($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
                            <div class="mt-4 p-3.5 rounded-xl bg-orange-950/40 border border-orange-500/30 text-xs text-orange-200 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-orange-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <span class="font-bold text-orange-300">Catatan Revisi dari Admin:</span>
                                    <p class="mt-0.5 text-slate-300">Ada bagian pengerjaan atau bukti yang perlu diperbaiki. Buka detail lembar kerja untuk melihat instruksi koreksi dan mengunggah bukti perbaikan.</p>
                                </div>
                            </div>
                        @endif

                        <!-- Grid Rincian Akun & Item Target -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 text-xs">
                            <div class="bg-brand-surface/60 rounded-xl p-3 border border-brand-border/40">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block">ID & Server Akun</span>
                                <span class="font-mono text-white font-semibold mt-1 block truncate">
                                    {{ $pesanan->id_akun_game }}
                                    @if($pesanan->server_game)
                                        <span class="text-slate-400 font-normal">({{ $pesanan->server_game }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="bg-brand-surface/60 rounded-xl p-3 border border-brand-border/40">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block">Nickname Akun</span>
                                <span class="text-white font-semibold mt-1 block truncate">
                                    {{ $pesanan->nickname_game ?: '-' }}
                                </span>
                            </div>
                            <div class="bg-brand-surface/60 rounded-xl p-3 border border-brand-border/40">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block">Metode Login</span>
                                <span class="text-brand-cyan font-semibold mt-1 block truncate">
                                    {{ $pesanan->login_via ?: 'Game Login' }}
                                </span>
                            </div>
                            <div class="bg-brand-surface/60 rounded-xl p-3 border border-brand-border/40">
                                <span class="text-[10px] uppercase font-bold text-slate-500 block">Target / Item Joki</span>
                                @php
                                    $itemsList = $pesanan->pesananItems->isNotEmpty() ? $pesanan->pesananItems : $pesanan->items;
                                    $targetLabels = $itemsList->map(fn($item) => $item->nama_item ?: ($item->layananItem?->nama ?? null))->filter()->values();
                                    $targetText = $targetLabels->isNotEmpty() ? $targetLabels->join(', ') : ($pesanan->layanan?->nama_layanan ?? '-');
                                @endphp
                                <span class="text-amber-400 font-semibold mt-1 block truncate" title="{{ $targetText }}">
                                    {{ $targetText }}
                                </span>
                            </div>
                        </div>

                        <!-- Catatan Customer (Jika Ada) -->
                        @if($pesanan->catatan_customer)
                            <div class="mt-3 px-3 py-2 rounded-xl bg-brand-surface/40 border border-brand-border/30 text-xs text-slate-400 flex items-start gap-2">
                                <span class="text-amber-400 font-semibold shrink-0">Catatan Customer:</span>
                                <span class="italic text-slate-300 truncate">"{{ $pesanan->catatan_customer }}"</span>
                            </div>
                        @endif

                        <!-- Footer Kartu: Timestamp & Tautan Langsung -->
                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-brand-border/40 text-[11px] text-slate-500">
                            <span>Diperbarui {{ $pesanan->updated_at->diffForHumans() }}</span>
                            <a href="{{ route('worker.pesanan.show', $pesanan->kode_pesanan) }}" class="text-brand-cyan hover:underline font-medium inline-flex items-center gap-1">
                                <span>Buka Lembar Kerja Detail</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $daftarPesanan->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 rounded-2xl bg-brand-card border border-brand-border text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-surface flex items-center justify-center text-slate-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white mb-1">
                    @if($search)
                        Tidak Ada Pesanan yang Cocok
                    @elseif($tab === 'revisi')
                        Bagus! Tidak Ada Pesanan yang Perlu Direvisi
                    @elseif($tab === 'selesai')
                        Belum Ada Riwayat Pesanan Selesai
                    @else
                        Belum Ada Tugas Pesanan Aktif
                    @endif
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
                    @if($search)
                        Tidak ditemukan pesanan dengan kata kunci "{{ $search }}". Silakan periksa kembali filter pencarian Anda.
                    @elseif($tab === 'revisi')
                        Semua hasil pengerjaan Anda telah memenuhi standar atau belum ada permintaan revisi dari Admin.
                    @elseif($tab === 'selesai')
                        Seluruh pesanan yang Anda selesaikan dan disetujui akan tercatat secara permanen di sini.
                    @else
                        Saat Admin menetapkan pesanan joki kepada Anda, data akun target dan detail paket akan otomatis muncul di sini.
                    @endif
                </p>
                @if($search)
                    <div class="mt-4">
                        <a href="{{ route('worker.pesanan', ['tab' => $tab]) }}" class="inline-block px-4 py-2 rounded-xl text-xs font-semibold bg-brand-surface border border-brand-border text-slate-300 hover:text-white transition-colors">
                            Reset Pencarian
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-worker-layout>
