<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'kode_pesanan',
        'customer_id',
        'worker_id',
        'layanan_id',
        'no_wa_customer',
        'id_akun_game',
        'login_via',
        'username_akun_game',
        'password_akun_game',
        'nickname_game',
        'server_game',
        'data_tambahan',
        'catatan_customer',
        'total_harga',
        'status',
        'data_disetujui',
        'batas_waktu_bayar',
        'selesai_at',
        'kredensial_dihapus_at',
    ];

    protected function casts(): array
    {
        return [
            'password_akun_game' => 'encrypted', // Enkripsi simetris AES-256 untuk kredensial game
            'data_tambahan' => 'array',         // otomatis encode/decode JSON
            'total_harga' => 'decimal:2',
            'status' => StatusPesanan::class,    // cast ke enum StatusPesanan
            'data_disetujui' => 'boolean',
            'batas_waktu_bayar' => 'datetime',
            'selesai_at' => 'datetime',
            'kredensial_dihapus_at' => 'datetime',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /** Customer pemilik pesanan */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /** Worker yang ditugaskan */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /** Layanan yang dipesan */
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    /** Item-item dalam pesanan (salinan harga) */
    public function pesananItems(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id');
    }

    /** Alias untuk pesananItems */
    public function items(): HasMany
    {
        return $this->pesananItems();
    }

    /** Data pembayaran (satu per pesanan) */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'pesanan_id');
    }

    /** Laporan penyelesaian dari Worker (bisa lebih dari satu jika ada revisi) */
    public function laporanPenyelesaian(): HasMany
    {
        return $this->hasMany(LaporanPenyelesaian::class, 'pesanan_id');
    }

    /** Laporan penyelesaian paling baru */
    public function laporanTerbaru(): HasOne
    {
        return $this->hasOne(LaporanPenyelesaian::class, 'pesanan_id')->latestOfMany();
    }

    /** Riwayat perubahan status (timeline) */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusPesanan::class, 'pesanan_id');
    }

    /** Ulasan dari customer (satu per pesanan) */
    public function ulasan(): HasOne
    {
        return $this->hasOne(Ulasan::class, 'pesanan_id');
    }

    // ── Helper ──────────────────────────────────────────

    /**
     * Hapus password akun game dan catat waktu penghapusan.
     * Dipanggil saat: dibatalkan, kedaluwarsa, hasil disetujui, selesai.
     */
    public function hapusPasswordAkunGame(): void
    {
        if ($this->password_akun_game !== null) {
            $this->update([
                'password_akun_game' => null,
                'kredensial_dihapus_at' => now(),
            ]);
        }
    }

    /** Cek apakah batas waktu bayar sudah lewat */
    public function sudahKedaluwarsa(): bool
    {
        return $this->batas_waktu_bayar ? $this->batas_waktu_bayar->isPast() : false;
    }

    /** Cek apakah status pesanan bersifat terminal */
    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * Catat perubahan status ke tabel riwayat_status_pesanan.
     * Dipanggil setiap kali status pesanan berubah. Menerima string maupun enum StatusPesanan.
     */
    public function catatRiwayat(string|StatusPesanan $statusLama, string|StatusPesanan $statusBaru, ?int $diubahOleh = null, ?string $catatan = null): void
    {
        $this->riwayatStatus()->create([
            'status_lama' => $statusLama instanceof StatusPesanan ? $statusLama->value : $statusLama,
            'status_baru' => $statusBaru instanceof StatusPesanan ? $statusBaru->value : $statusBaru,
            'catatan' => $catatan,
            'diubah_oleh' => $diubahOleh,
        ]);
    }

    /**
     * Generate kode pesanan unik: ES-YYYYMMDD-XXX
     */
    public static function generateKodePesanan(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix = "ES-{$tanggal}-";

        $lastOrder = static::where('kode_pesanan', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastOrder) {
            $lastSuffix = substr($lastOrder->kode_pesanan, strlen($prefix));
            if (is_numeric($lastSuffix)) {
                $nextNumber = (int) $lastSuffix + 1;
            }
        }

        $kode = $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        while (static::where('kode_pesanan', $kode)->exists()) {
            $nextNumber++;
            $kode = $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return $kode;
    }
}
