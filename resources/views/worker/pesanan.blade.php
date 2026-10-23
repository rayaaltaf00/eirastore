<x-worker-layout header="Pesanan Ditugaskan">
    <div class="space-y-6">
        
        <!-- Welcome Banner -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-brand-card to-brand-surface border border-brand-border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">Halo, {{ Auth::user()->nama }}! 🎮</h2>
                <p class="text-sm text-slate-400 mt-1">Daftar akun game yang perlu dikerjakan dan dilaporkan bukti penyelesaiannya.</p>
            </div>
            <div>
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    ● Status: Siap Menerima Order
                </span>
            </div>
        </div>

        <!-- Task List Placeholder / Empty State -->
        <div class="p-12 rounded-2xl bg-brand-card border border-brand-border text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-surface flex items-center justify-center text-slate-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Belum Ada Pesanan Ditugaskan</h3>
            <p class="text-sm text-slate-400 max-w-md mx-auto">Saat Admin menetapkan pesanan joki kepada Anda, data akun target dan detail paket akan muncul secara otomatis di sini.</p>
        </div>

    </div>
</x-worker-layout>
