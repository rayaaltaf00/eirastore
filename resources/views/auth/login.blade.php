<x-guest-layout>
    <!-- Header Form -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-white tracking-tight">Selamat Datang</h1>
        <p class="text-xs text-slate-400 mt-1.5">Masuk ke akun Anda untuk memantau status joki & transaksi.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <div class="relative mt-1">
                <x-text-input id="email" class="w-full pl-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <div class="relative mt-1">
                <x-text-input id="password" class="w-full pl-10" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Ingat Saya -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded bg-brand-surface border-brand-border text-brand-blue shadow-sm focus:ring-brand-blue" name="remember">
                <span class="ms-2 text-xs text-slate-400">Ingat saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <x-primary-button class="w-full py-3 text-sm">
                Masuk Sekarang
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="pt-3 text-center border-t border-brand-border/60">
            <p class="text-xs text-slate-400">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-bold text-brand-cyan hover:underline ml-1">
                    Daftar di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
