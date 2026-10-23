<x-storefront-layout title="Pesanan Saya - EiraStore">
    <div class="max-w-5xl mx-auto py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-black text-white">Pesanan Saya</h1>
                <p class="text-xs text-slate-400 mt-1">Daftar riwayat transaksi joki akun Anda.</p>
            </div>
            <a href="{{ route('beranda') }}#katalog-game" class="px-4 py-2.5 rounded-xl font-bold text-xs bg-brand-blue hover:bg-brand-blueHover text-white shadow-glow-blue transition-all">
                + Pesan Joki Baru
            </a>
        </div>

        <div class="p-10 rounded-2xl bg-brand-card border border-brand-border text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-surface border border-brand-border flex items-center justify-center text-slate-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Belum Ada Riwayat Pesanan</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mb-6">Pilih game favoritmu dan pesan paket joki sekarang untuk mulai menaikkan rank akunmu.</p>
            <a href="{{ route('beranda') }}#katalog-game" class="inline-flex items-center text-xs font-bold text-brand-cyan hover:underline">
                Lihat Katalog Game →
            </a>
        </div>
    </div>
</x-storefront-layout>
