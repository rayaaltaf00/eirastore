<x-guest-layout>
    <!-- Header Form -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-white tracking-tight">Konfirmasi Keamanan</h1>
        <p class="text-xs text-slate-400 mt-1.5">Ini adalah area aman aplikasi. Harap masukkan kata sandi Anda sebelum melanjutkan.</p>
    </div>

    <form method="POST" action="{{ Route::has('password.confirm') ? route('password.confirm') : '#' }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi Anda" />
            <div class="relative mt-1">
                <x-text-input id="password" class="w-full pl-10" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full py-3 text-sm">
                Konfirmasi Lanjutkan
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
