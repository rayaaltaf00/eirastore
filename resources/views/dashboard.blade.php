<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-brand-card border border-brand-border overflow-hidden shadow-card rounded-2xl">
                <div class="p-6 text-slate-200">
                    {{ __("Anda telah berhasil masuk ke EiraStore.") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
