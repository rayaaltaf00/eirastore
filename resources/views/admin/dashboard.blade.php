<x-admin-layout header="Dashboard Utama">
    <div class="space-y-6">
        
        <!-- Welcome Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-card to-brand-surface border border-brand-border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">Selamat Datang, {{ Auth::user()->nama }}! 👋</h2>
                <p class="text-sm text-slate-400 mt-1">Sistem siap memproses transaksi joki game dan memonitor kinerja tim.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    ● Sistem Online
                </span>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-brand-card border border-brand-border">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Pesanan</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-brand-cyan flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-white">0</span>
                    <span class="text-xs text-slate-500">Bulan ini</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-brand-card border border-brand-border">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Menunggu Verifikasi</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-white">0</span>
                    <span class="text-xs text-amber-400 font-medium">Perlu Tindakan</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-brand-card border border-brand-border">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Sedang Dikerjakan</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-white">0</span>
                    <span class="text-xs text-slate-500">Oleh Worker</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-brand-card border border-brand-border">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-white">Rp 0</span>
                    <span class="text-xs text-emerald-400 font-medium">Bulan ini</span>
                </div>
            </div>
        </div>

        <!-- Quick Access Guidance -->
        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border">
            <h3 class="text-base font-bold text-white mb-2">Panduan Pengoperasian Panel Admin</h3>
            <p class="text-sm text-slate-400 mb-4">Gunakan navigasi di sebelah kiri untuk mengelola data katalog game, mengonfirmasi bukti bayar, dan menugaskan worker.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-brand-surface border border-brand-border">
                    <span class="text-xs font-bold text-brand-cyan">Langkah 1</span>
                    <p class="text-sm font-semibold text-white mt-1">Input Game & Layanan</p>
                    <p class="text-xs text-slate-400 mt-1">Tambahkan game yang tersedia beserta paket joki rank/paketannya.</p>
                </div>
                <div class="p-4 rounded-xl bg-brand-surface border border-brand-border">
                    <span class="text-xs font-bold text-brand-cyan">Langkah 2</span>
                    <p class="text-sm font-semibold text-white mt-1">Verifikasi Pembayaran</p>
                    <p class="text-xs text-slate-400 mt-1">Periksa mutasi transfer / QRIS yang diunggah customer.</p>
                </div>
                <div class="p-4 rounded-xl bg-brand-surface border border-brand-border">
                    <span class="text-xs font-bold text-brand-cyan">Langkah 3</span>
                    <p class="text-sm font-semibold text-white mt-1">Tugaskan Worker</p>
                    <p class="text-xs text-slate-400 mt-1">Pilih worker yang kompeten untuk memproses pesanan customer.</p>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
