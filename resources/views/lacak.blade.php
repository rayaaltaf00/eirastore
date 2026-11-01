<x-storefront-layout title="Lacak Pesanan - EiraStore">
    <div class="max-w-xl mx-auto py-8 sm:py-16">
        <div class="p-6 sm:p-8 rounded-3xl bg-brand-card border border-brand-border text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-brand-surface border border-brand-border flex items-center justify-center text-brand-cyan">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            
            <h1 class="text-2xl font-black text-white">Lacak Pesanan Joki</h1>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Masukkan Nomor Invoice / Kode Pesanan Anda untuk memeriksa progres pengerjaan.</p>

            @if(session('error'))
                <div class="mt-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs text-left flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('lacak.index') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2" data-loading data-loading-text="Mencari...">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        name="invoice" 
                        value="{{ request('invoice') }}"
                        placeholder="Contoh: ES-20261026-001" 
                        required
                        class="w-full bg-brand-surface border-brand-border text-slate-100 placeholder-slate-500 rounded-xl px-4 py-3 text-xs focus:ring-1 focus:ring-brand-blue focus:border-brand-blue transition-colors">
                </div>
                <button type="submit" class="px-5 py-3 rounded-xl font-bold text-xs bg-brand-blue hover:bg-brand-blueHover text-white shadow-glow-blue transition-all cursor-pointer">
                    Cari Pesanan
                </button>
            </form>

            <div class="mt-6 p-4 rounded-xl bg-brand-surface border border-brand-border text-left">
                <span class="text-xs font-bold text-slate-300">💡 Informasi Pelacakan</span>
                <p class="text-xs text-slate-400 mt-1">Nomor invoice pesanan dapat dilihat pada riwayat pesanan akun Anda atau pesan konfirmasi yang dikirimkan melalui WhatsApp.</p>
            </div>
        </div>
    </div>
</x-storefront-layout>
