<x-guest-layout>
    <!-- Header Form -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-white tracking-tight">Lupa Kata Sandi?</h1>
        <p class="text-xs text-slate-400 mt-1.5">Masukkan alamat email Anda, kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ Route::has('password.email') ? route('password.email') : '#' }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email Akun" />
            <div class="relative mt-1">
                <x-text-input id="email" class="w-full pl-10" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-3 text-sm">
                Kirim Tautan Reset Sandi
            </x-primary-button>
        </div>

        <div class="pt-3 text-center border-t border-brand-border/60">
            <a href="{{ route('login') }}" class="text-xs text-brand-cyan hover:underline inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </form>
</x-guest-layout>
