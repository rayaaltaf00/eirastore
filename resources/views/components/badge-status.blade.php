@props(['status'])

@php
    $value = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
    $label = is_object($status) && method_exists($status, 'label') ? $status->label() : ucwords(str_replace('_', ' ', $value));

    // Skema warna semantik
    $colors = match (strtolower($value)) {
        // Status Menunggu
        'menunggu_pembayaran', 'pending', 'menunggu_worker', 'menunggu'
            => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        
        // Status Verifikasi / Siap
        'menunggu_verifikasi', 'verifikasi', 'siap_dikerjakan'
            => 'bg-blue-500/10 text-brand-cyan border-blue-500/20',

        // Status Pengerjaan Aktif
        'sedang_dikerjakan', 'proses', 'dikerjakan'
            => 'bg-purple-500/10 text-purple-400 border-purple-500/20',

        // Status Sukses / Selesai
        'selesai', 'berhasil', 'diverifikasi', 'disetujui', 'aktif'
            => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',

        // Status Batal / Ditolak / Masalah
        'dibatalkan', 'ditolak', 'gagal', 'nonaktif', 'revisi'
            => 'bg-red-500/10 text-red-400 border-red-500/20',

        default
            => 'bg-slate-500/10 text-slate-300 border-slate-500/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {$colors}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-current"></span>
    {{ $label }}
</span>
