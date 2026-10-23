<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pesanan Saya
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Selamat datang, {{ Auth::user()->nama }}! Anda login sebagai <strong>Worker</strong>.
                    <p class="mt-2 text-gray-500">Belum ada pesanan yang ditugaskan.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
