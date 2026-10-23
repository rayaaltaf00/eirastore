<x-guest-layout>
    <!-- Header Form -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-white tracking-tight">Daftar Akun Baru</h1>
        <p class="text-xs text-slate-400 mt-1.5">Buat akun EiraStore untuk memesan dan melacak proses joki.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="nama" value="Nama Lengkap" />
            <div class="relative mt-1">
                <x-text-input id="nama" class="w-full pl-10" type="text" name="nama" :value="old('nama')" required autofocus autocomplete="name" maxlength="100" placeholder="Contoh: Alex Pratama" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('nama')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <div class="relative mt-1">
                <x-text-input id="email" class="w-full pl-10" type="email" name="email" :value="old('email')" required autocomplete="username" maxlength="100" placeholder="nama@email.com" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Nomor HP / WhatsApp -->
        <div>
            <x-input-label for="no_hp" value="Nomor WhatsApp / HP" />
            <div class="relative mt-1">
                <x-text-input id="no_hp" class="w-full pl-10" type="text" name="no_hp" :value="old('no_hp')" required autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="15" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Nomor ini digunakan untuk update pesanan via WhatsApp.</p>
            <x-input-error :messages="$errors->get('no_hp')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi (Min. 8 Karakter)" />
            <div class="relative mt-1">
                <x-text-input id="password" class="w-full pl-10" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Konfirmasi Password -->
        <div>
            <x-input-label for="password_confirmation" value="Ulangi Kata Sandi" />
            <div class="relative mt-1">
                <x-text-input id="password_confirmation" class="w-full pl-10" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <x-primary-button class="w-full py-3 text-sm">
                Daftar Akun
            </x-primary-button>
        </div>

        <!-- Login Link -->
        <div class="pt-3 text-center border-t border-brand-border/60">
            <p class="text-xs text-slate-400">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-brand-cyan hover:underline ml-1">
                    Masuk di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
