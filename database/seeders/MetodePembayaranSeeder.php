<?php

namespace Database\Seeders;

use App\Models\MetodePembayaranTujuan;
use Illuminate\Database\Seeder;

class MetodePembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metodes = [
            [
                'nama_metode' => 'Bank Central Asia (BCA)',
                'kategori' => 'bank',
                'nomor_rekening' => '8040192831',
                'atas_nama' => 'Eira Store Official',
                'logo' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=120&auto=format&fit=crop&q=80',
                'gambar_qris' => null,
                'petunjuk_transfer' => 'Transfer via m-BCA, klikBCA, atau ATM BCA. Simpan bukti transfer untuk diunggah.',
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'nama_metode' => 'Bank Mandiri',
                'kategori' => 'bank',
                'nomor_rekening' => '1370019283471',
                'atas_nama' => 'Eira Store Official',
                'logo' => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?w=120&auto=format&fit=crop&q=80',
                'gambar_qris' => null,
                'petunjuk_transfer' => 'Transfer via Livin by Mandiri atau ATM Mandiri.',
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'nama_metode' => 'DANA E-Wallet',
                'kategori' => 'ewallet',
                'nomor_rekening' => '081234567890',
                'atas_nama' => 'Eira Store Storefront',
                'logo' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=120&auto=format&fit=crop&q=80',
                'gambar_qris' => null,
                'petunjuk_transfer' => 'Kirim saldo DANA ke nomor terdaftar. Pastikan nama penerima sesuai.',
                'urutan' => 3,
                'aktif' => true,
            ],
            [
                'nama_metode' => 'GoPay E-Wallet',
                'kategori' => 'ewallet',
                'nomor_rekening' => '081234567890',
                'atas_nama' => 'Eira Store Official',
                'logo' => 'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?w=120&auto=format&fit=crop&q=80',
                'gambar_qris' => null,
                'petunjuk_transfer' => 'Transfer GoPay ke nomor terdaftar.',
                'urutan' => 4,
                'aktif' => true,
            ],
            [
                'nama_metode' => 'QRIS Semua Pembayaran',
                'kategori' => 'qris',
                'nomor_rekening' => null,
                'atas_nama' => 'EiraStore Merchant (NMID: ID1020304050)',
                'logo' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=120&auto=format&fit=crop&q=80',
                'gambar_qris' => 'https://images.unsplash.com/photo-1595079678712-426d03cf443d?w=400&auto=format&fit=crop&q=80',
                'petunjuk_transfer' => 'Scan QRIS menggunakan aplikasi m-Banking atau E-Wallet apa pun (BCA, Mandiri, BRI, BNI, Dana, GoPay, OVO, ShopeePay, dll). Masukkan nominal sesuai total tagihan.',
                'urutan' => 5,
                'aktif' => true,
            ],
        ];

        foreach ($metodes as $data) {
            MetodePembayaranTujuan::updateOrCreate(
                ['nama_metode' => $data['nama_metode']],
                $data
            );
        }
    }
}
