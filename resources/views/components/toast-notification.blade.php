@php
    $initialToasts = [];
    if (session('success')) {
        $initialToasts[] = [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => (string) session('success'),
        ];
    }
    if (session('error')) {
        $initialToasts[] = [
            'type' => 'error',
            'title' => 'Terjadi Kesalahan',
            'message' => (string) session('error'),
        ];
    }
    if (session('status_error')) {
        $initialToasts[] = [
            'type' => 'error',
            'title' => 'Perhatian',
            'message' => (string) session('status_error'),
        ];
    }
    if (session('warning')) {
        $initialToasts[] = [
            'type' => 'warning',
            'title' => 'Peringatan',
            'message' => (string) session('warning'),
        ];
    }
    if (session('status') === 'profile-updated') {
        $initialToasts[] = [
            'type' => 'success',
            'title' => 'Profil Diperbarui',
            'message' => 'Data profil akun Anda telah berhasil disimpan.',
        ];
    } elseif (session('status') === 'password-updated') {
        $initialToasts[] = [
            'type' => 'success',
            'title' => 'Kata Sandi Diperbarui',
            'message' => 'Kata sandi akun Anda telah berhasil diperbarui.',
        ];
    } elseif (session('status') && is_string(session('status'))) {
        $initialToasts[] = [
            'type' => 'info',
            'title' => 'Informasi',
            'message' => (string) session('status'),
        ];
    }
@endphp

<div x-data="toastManager(@json($initialToasts))"
     @toast.window="add($event.detail)"
     aria-live="polite"
     class="fixed bottom-5 right-5 sm:top-5 sm:bottom-auto z-[9999] pointer-events-none flex flex-col gap-2.5 max-w-sm w-full px-4 sm:px-0">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-cloak
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @mouseenter="pauseToast(toast)"
             @mouseleave="resumeToast(toast)"
             class="pointer-events-auto relative overflow-hidden rounded-2xl border p-4 shadow-2xl backdrop-blur-md transition-all flex items-start gap-3.5"
             :class="colorClasses(toast.type)">
             
             <!-- Icon Container -->
             <div class="shrink-0 mt-0.5">
                 <template x-if="toast.type === 'success'">
                     <span class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                     </span>
                 </template>
                 <template x-if="toast.type === 'error'">
                     <span class="w-7 h-7 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                     </span>
                 </template>
                 <template x-if="toast.type === 'warning'">
                     <span class="w-7 h-7 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                     </span>
                 </template>
                 <template x-if="toast.type === 'info'">
                     <span class="w-7 h-7 rounded-xl bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/30 flex items-center justify-center">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                     </span>
                 </template>
             </div>

             <!-- Content: 100% XSS-Safe via x-text -->
             <div class="flex-1 min-w-0 pr-1">
                 <h4 class="text-xs font-bold text-white tracking-wide" x-text="toast.title || defaultTitle(toast.type)"></h4>
                 <p class="text-xs text-slate-300 mt-0.5 leading-relaxed break-words" x-text="toast.message"></p>
             </div>

             <!-- Close Button -->
             <button type="button" 
                     @click="remove(toast.id)"
                     class="shrink-0 text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10"
                     aria-label="Tutup notifikasi">
                 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
             </button>
        </div>
    </template>
</div>
