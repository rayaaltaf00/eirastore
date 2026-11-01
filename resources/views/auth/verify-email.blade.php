<x-guest-layout>
    <!-- Header Form -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-white tracking-tight">Verifikasi Email</h1>
        <p class="text-xs text-slate-400 mt-1.5">Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat surel Anda melalui tautan yang baru saja kami kirimkan.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs font-semibold text-emerald-400">
            Tautan verifikasi baru telah berhasil dikirim ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="POST" action="{{ Route::has('verification.send') ? route('verification.send') : '#' }}" class="w-full sm:w-auto">
            @csrf

            <div>
                <x-primary-button class="w-full sm:w-auto">
                    Kirim Ulang Email Verifikasi
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
            @csrf

            <button type="submit" class="text-xs text-slate-400 hover:text-white underline transition-colors">
                Keluar Akun
            </button>
        </form>
    </div>
</x-guest-layout>
