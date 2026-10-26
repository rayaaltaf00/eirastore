@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.layanan.index', ['game_id' => $layanan->game_id]) }}" class="hover:text-white transition-colors">Kelola Layanan</a>
                <span>/</span>
                <span class="text-brand-cyan font-medium">{{ $layanan->nama_layanan }}</span>
            </div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                Item & Tarif: {{ $layanan->nama_layanan }}
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.layanan.index', ['game_id' => $layanan->game_id]) }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-brand-surface border border-brand-border text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Layanan
            </a>
            <a href="{{ route('admin.layanan.items.create', $layanan) }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-gradient-to-r from-brand-blue to-brand-cyan text-white text-xs font-semibold hover:opacity-95 shadow-glow-blue transition-all">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Item Baru
            </a>
        </div>
    </div>

    <!-- Layanan Summary & Context Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl overflow-hidden bg-brand-surface border border-brand-border shrink-0">
                <img src="{{ $layanan->game->gambar_url }}" 
                     alt="{{ $layanan->game->nama_game }}" 
                     class="w-full h-full object-cover">
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-white text-base">{{ $layanan->game->nama_game }}</span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs font-semibold text-brand-cyan">{{ $layanan->nama_layanan }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider
                        {{ $layanan->tipe_harga->value === 'tangga' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : '' }}
                        {{ $layanan->tipe_harga->value === 'progres' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : '' }}
                        {{ $layanan->tipe_harga->value === 'pilihan' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}">
                        Metode: {{ $layanan->tipe_harga->label() }}
                    </span>
                    @if($layanan->estimasi_waktu)
                        <span class="text-xs text-slate-400">⏱ Estimasi: {{ $layanan->estimasi_waktu }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-xs text-slate-400 bg-brand-surface/60 border border-brand-border/60 rounded-xl p-3 max-w-md">
            @if($layanan->tipe_harga->value === 'tangga')
                💡 <strong>Tipe Tangga:</strong> Masukkan item berurutan dari tier terendah ke tertinggi. <em>Nilai Maks</em> adalah jumlah bintang/langkah per tier, dan <em>Harga</em> adalah tarif per langkah.
            @elseif($layanan->tipe_harga->value === 'progres')
                💡 <strong>Tipe Progres:</strong> Buat Region induk (harga 0), lalu buat item Wilayah turunan di bawahnya dengan harga dasar 100%. Customer akan menginput capaian 0 s.d 100%.
            @else
                💡 <strong>Tipe Pilihan:</strong> Masukkan paket/item lepas. Bila item dapat dikelompokkan (contoh: Archon Quest, World Quest, Story Quest), isi kolom Kategori agar tampil rapi per kategori di halaman checkout customer.
            @endif
        </div>
    </div>

    <!-- Filter Kategori Item (Jika Ada Kategori) -->
    @if(isset($kategoriList) && $kategoriList->isNotEmpty())
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="text-slate-400 font-semibold shrink-0">Filter Kategori:</span>
            <a href="{{ route('admin.layanan.items.index', $layanan) }}" 
               class="px-3 py-1.5 rounded-xl border transition-all {{ !request('kategori') ? 'bg-brand-blue/20 text-brand-cyan border-brand-cyan/40 font-bold' : 'bg-brand-surface border-brand-border text-slate-400 hover:text-white' }}">
                Semua Item ({{ $layanan->items()->count() }})
            </a>
            @foreach($kategoriList as $kat)
                <a href="{{ route('admin.layanan.items.index', [$layanan, 'kategori' => $kat]) }}" 
                   class="px-3 py-1.5 rounded-xl border transition-all {{ request('kategori') === $kat ? 'bg-brand-blue/20 text-brand-cyan border-brand-cyan/40 font-bold' : 'bg-brand-surface border-brand-border text-slate-400 hover:text-white' }}">
                    {{ $kat }} ({{ $layanan->items()->where('kategori', $kat)->count() }})
                </a>
            @endforeach
        </div>
    @endif

    <!-- Items Table Card -->
    <div class="bg-brand-card/90 border border-brand-border rounded-2xl overflow-hidden shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-brand-border bg-brand-surface/50 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3.5 px-4 w-12 text-center">Urutan</th>
                        <th class="py-3.5 px-4 text-center">Jenis</th>
                        <th class="py-3.5 px-4">Nama Item & Kategori</th>
                        <th class="py-3.5 px-4">Induk (Region)</th>
                        <th class="py-3.5 px-4 text-right">Tarif / Harga</th>
                        <th class="py-3.5 px-4 text-center">Nilai Maks / Satuan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-brand-surface/30 transition-colors {{ $item->parent_id ? 'bg-brand-surface/10' : '' }}">
                            
                            <!-- Urutan -->
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-xs text-slate-400">
                                #{{ $item->urutan }}
                            </td>

                            <!-- Jenis -->
                            <td class="py-3.5 px-4 text-center">
                                @if($item->jenis === 'utama')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-brand-blue/20 text-brand-cyan border border-brand-blue/30">
                                        Utama
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        Tambahan
                                    </span>
                                @endif
                            </td>

                            <!-- Nama Item & Kategori -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-2">
                                    @if($item->parent_id)
                                        <span class="text-slate-500 text-xs pl-2">↳</span>
                                    @endif
                                    <span class="font-bold text-white">{{ $item->nama }}</span>
                                </div>
                                @if($item->kategori)
                                    <span class="inline-flex items-center px-1.5 py-0.5 mt-1 rounded text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $item->kategori }}
                                    </span>
                                @endif
                            </td>

                            <!-- Induk (Parent) -->
                            <td class="py-3.5 px-4 text-xs text-slate-400">
                                @if($item->parent)
                                    <span class="text-cyan-400 font-medium">{{ $item->parent->nama }}</span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>

                            <!-- Harga / Tarif -->
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-100">
                                @if((float)$item->harga == 0 && ($item->children_count > 0))
                                    <span class="text-xs text-slate-400 font-sans font-normal italic">Penampung Region (Rp 0)</span>
                                @else
                                    Rp {{ number_format($item->harga, 0, ',', '.') }}
                                    @if($layanan->tipe_harga->value === 'tangga' && $item->jenis === 'utama')
                                        <span class="text-[10px] text-slate-400 font-sans font-normal block">/ langkah</span>
                                    @endif
                                @endif
                            </td>

                            <!-- Nilai Maks / Satuan -->
                            <td class="py-3.5 px-4 text-center text-xs">
                                @if($item->nilai_maks)
                                    <span class="font-semibold text-slate-200">
                                        {{ $item->nilai_maks }} {{ $item->satuan ?? ($layanan->tipe_harga->value === 'tangga' ? 'Bintang/Langkah' : '') }}
                                    </span>
                                @elseif($item->satuan)
                                    <span class="text-slate-400">{{ $item->satuan }}</span>
                                @elseif($layanan->tipe_harga->value === 'progres')
                                    <span class="text-slate-500">0 - 100%</span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>

                            <!-- Status Aktif -->
                            <td class="py-3.5 px-4 text-center">
                                @if($item->aktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Tombol Ubah -->
                                    <a href="{{ route('admin.layanan.items.edit', [$layanan, $item]) }}" 
                                       title="Ubah Item"
                                       class="p-2 rounded-lg bg-brand-surface text-amber-400 hover:text-amber-300 hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form method="POST" 
                                          action="{{ route('admin.layanan.items.destroy', [$layanan, $item]) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus item {{ $item->nama }}? Jika item ini memiliki turunan (anak), semua anak akan ikut terhapus.');"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus Item"
                                                class="p-2 rounded-lg bg-brand-surface text-red-400 hover:text-red-300 hover:bg-brand-surface/80 border border-brand-border transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-sm mx-auto text-slate-400 space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-brand-surface border border-brand-border flex items-center justify-center mx-auto text-slate-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-300">Belum ada item layanan</p>
                                    <p class="text-xs text-slate-500">Tambahkan tingkatan rank, wilayah eksplorasi, atau paket joki untuk layanan ini.</p>
                                    <div class="pt-2">
                                        <a href="{{ route('admin.layanan.items.create', $layanan) }}" class="inline-flex items-center text-xs font-semibold text-brand-cyan hover:underline">
                                            + Tambah Item Pertama Sekarang
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
