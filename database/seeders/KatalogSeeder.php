<?php

namespace Database\Seeders;

use App\Enums\TipeHarga;
use App\Models\Game;
use App\Models\Layanan;
use App\Models\LayananItem;
use Illuminate\Database\Seeder;

class KatalogSeeder extends Seeder
{
    /**
     * Run the database seeds for Games, Layanan, and LayananItems.
     */
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1. GAME: MOBILE LEGENDS: BANG BANG
        // ─────────────────────────────────────────────────────────────
        $mlbb = Game::updateOrCreate(
            ['nama_game' => 'Mobile Legends: Bang Bang'],
            [
                'kategori'  => 'MOBA',
                'deskripsi' => 'Layanan joki rank bintang dan paket winrate hero Mobile Legends terlengkap dengan worker Mythical Immortal terpercaya.',
                'gambar'    => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&auto=format&fit=crop&q=80',
                'aktif'     => true,
            ]
        );

        // Layanan MLBB 1: Push Rank (Tipe: Tangga)
        $mlbbRank = Layanan::updateOrCreate(
            ['game_id' => $mlbb->id, 'nama_layanan' => 'Joki Rank Bintang'],
            [
                'gambar'         => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=800&auto=format&fit=crop&q=80',
                'tipe_harga'     => TipeHarga::Tangga,
                'deskripsi'      => 'Joki push rank per bintang/tier dari rank awal hingga rank tujuan yang Anda inginkan.',
                'estimasi_waktu' => '1 - 3 Hari',
                'aktif'          => true,
            ]
        );

        $mlbbRanks = [
            ['nama' => 'Warrior', 'urutan' => 1, 'harga' => 3000, 'nilai_maks' => 3],
            ['nama' => 'Elite', 'urutan' => 2, 'harga' => 4000, 'nilai_maks' => 4],
            ['nama' => 'Master', 'urutan' => 3, 'harga' => 5000, 'nilai_maks' => 4],
            ['nama' => 'Grandmaster', 'urutan' => 4, 'harga' => 7000, 'nilai_maks' => 5],
            ['nama' => 'Epic', 'urutan' => 5, 'harga' => 10000, 'nilai_maks' => 5],
            ['nama' => 'Legend', 'urutan' => 6, 'harga' => 15000, 'nilai_maks' => 5],
            ['nama' => 'Mythic', 'urutan' => 7, 'harga' => 25000, 'nilai_maks' => 25],
            ['nama' => 'Mythical Honor', 'urutan' => 8, 'harga' => 35000, 'nilai_maks' => 25],
            ['nama' => 'Mythical Glory', 'urutan' => 9, 'harga' => 45000, 'nilai_maks' => 50],
        ];

        foreach ($mlbbRanks as $rank) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $mlbbRank->id, 'nama' => $rank['nama']],
                [
                    'parent_id'  => null,
                    'jenis'      => 'utama',
                    'kategori'   => null,
                    'harga'      => $rank['harga'],
                    'satuan'     => 'bintang',
                    'nilai_maks' => $rank['nilai_maks'],
                    'urutan'     => $rank['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // Layanan MLBB 2: Paket Winrate Classic (Tipe: Pilihan)
        $mlbbClassic = Layanan::updateOrCreate(
            ['game_id' => $mlbb->id, 'nama_layanan' => 'Joki Winrate Classic'],
            [
                'gambar'         => 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?w=800&auto=format&fit=crop&q=80',
                'tipe_harga'     => TipeHarga::Pilihan,
                'deskripsi'      => 'Tingkatkan winrate hero favorit Anda di mode Classic bersama worker eks-top global.',
                'estimasi_waktu' => '3 - 8 Jam',
                'aktif'          => true,
            ]
        );

        $mlbbPilihan = [
            ['nama' => 'Paket 5 Match Win', 'harga' => 35000, 'satuan' => 'paket', 'nilai_maks' => 5, 'urutan' => 1],
            ['nama' => 'Paket 10 Match Win', 'harga' => 65000, 'satuan' => 'paket', 'nilai_maks' => 10, 'urutan' => 2],
            ['nama' => 'Paket 20 Match Win', 'harga' => 120000, 'satuan' => 'paket', 'nilai_maks' => 20, 'urutan' => 3],
        ];

        foreach ($mlbbPilihan as $pilih) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $mlbbClassic->id, 'nama' => $pilih['nama']],
                [
                    'parent_id'  => null,
                    'jenis'      => 'utama',
                    'kategori'   => 'Paket Match',
                    'harga'      => $pilih['harga'],
                    'satuan'     => $pilih['satuan'],
                    'nilai_maks' => $pilih['nilai_maks'],
                    'urutan'     => $pilih['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // Layanan MLBB 3: Joki Gendong (Tipe: Pilihan)
        $mlbbGendong = Layanan::updateOrCreate(
            ['game_id' => $mlbb->id, 'nama_layanan' => 'Joki Gendong (Mabar Pro)'],
            [
                'gambar'         => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=800&auto=format&fit=crop&q=80',
                'tipe_harga'     => TipeHarga::Pilihan,
                'deskripsi'      => 'Main bareng (party) dengan pro player kami, akun tetap Anda mainkan sendiri dengan aman.',
                'estimasi_waktu' => '2 - 4 Jam',
                'aktif'          => true,
            ]
        );

        $gendongItems = [
            ['nama' => 'Mabar 3 Bintang (Epic/Legend)', 'harga' => 40000, 'satuan' => 'paket', 'nilai_maks' => 1, 'urutan' => 1],
            ['nama' => 'Mabar 5 Bintang (Epic/Legend)', 'harga' => 65000, 'satuan' => 'paket', 'nilai_maks' => 1, 'urutan' => 2],
            ['nama' => 'Mabar 5 Bintang (Mythic)', 'harga' => 95000, 'satuan' => 'paket', 'nilai_maks' => 1, 'urutan' => 3],
        ];

        foreach ($gendongItems as $item) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $mlbbGendong->id, 'nama' => $item['nama']],
                [
                    'parent_id'  => null,
                    'jenis'      => 'utama',
                    'kategori'   => 'Paket Mabar',
                    'harga'      => $item['harga'],
                    'satuan'     => $item['satuan'],
                    'nilai_maks' => $item['nilai_maks'],
                    'urutan'     => $item['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 2. GAME: GENSHIN IMPACT
        // ─────────────────────────────────────────────────────────────
        $genshin = Game::updateOrCreate(
            ['nama_game' => 'Genshin Impact'],
            [
                'kategori'  => 'RPG',
                'deskripsi' => 'Jasa eksplorasi region 100%, Spiral Abyss 36 Star, Oculus hunting, dan Archon Quest terpercaya tanpa menggunakan bot atau program ilegal.',
                'gambar'    => 'https://images.unsplash.com/photo-1579373903781-fd5c0c30c4cd?w=800&auto=format&fit=crop&q=80',
                'aktif'     => true,
            ]
        );

        // Layanan Genshin 1: Eksplorasi Region (Tipe: Progres)
        $genshinExplore = Layanan::updateOrCreate(
            ['game_id' => $genshin->id, 'nama_layanan' => 'Eksplorasi Map 100%'],
            [
                'gambar'         => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=800&auto=format&fit=crop&q=80',
                'tipe_harga'     => TipeHarga::Progres,
                'deskripsi'      => 'Joki eksplorasi sub-wilayah hingga 100% tuntas semua peti dan tantangan teka-teki.',
                'estimasi_waktu' => '2 - 5 Hari',
                'aktif'          => true,
            ]
        );

        // Parent 1: Mondstadt
        $mondstadt = LayananItem::updateOrCreate(
            ['layanan_id' => $genshinExplore->id, 'nama' => 'Wilayah Mondstadt'],
            [
                'parent_id'  => null,
                'jenis'      => 'utama',
                'kategori'   => 'Region',
                'harga'      => 0,
                'satuan'     => null,
                'nilai_maks' => null,
                'urutan'     => 1,
                'aktif'      => true,
            ]
        );

        $mondstadtSubs = [
            ['nama' => 'Starfell Valley', 'harga' => 35000, 'urutan' => 1],
            ['nama' => 'Galesong Hill', 'harga' => 35000, 'urutan' => 2],
            ['nama' => 'Windwail Highland', 'harga' => 35000, 'urutan' => 3],
            ['nama' => 'Brightcrown Mountains', 'harga' => 40000, 'urutan' => 4],
            ['nama' => 'Dragonspine', 'harga' => 60000, 'urutan' => 5],
        ];

        foreach ($mondstadtSubs as $sub) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $genshinExplore->id, 'nama' => $sub['nama']],
                [
                    'parent_id'  => $mondstadt->id,
                    'jenis'      => 'utama',
                    'kategori'   => 'Sub-Wilayah',
                    'harga'      => $sub['harga'],
                    'satuan'     => 'persen',
                    'nilai_maks' => null,
                    'urutan'     => $sub['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // Parent 2: Liyue
        $liyue = LayananItem::updateOrCreate(
            ['layanan_id' => $genshinExplore->id, 'nama' => 'Wilayah Liyue'],
            [
                'parent_id'  => null,
                'jenis'      => 'utama',
                'kategori'   => 'Region',
                'harga'      => 0,
                'satuan'     => null,
                'nilai_maks' => null,
                'urutan'     => 2,
                'aktif'      => true,
            ]
        );

        $liyueSubs = [
            ['nama' => 'Bishui Plain', 'harga' => 45000, 'urutan' => 1],
            ['nama' => 'Qiongji Estuary', 'harga' => 45000, 'urutan' => 2],
            ['nama' => 'Minlin', 'harga' => 55000, 'urutan' => 3],
            ['nama' => 'Sea of Clouds', 'harga' => 45000, 'urutan' => 4],
            ['nama' => 'The Chasm (Overground + Underground)', 'harga' => 65000, 'urutan' => 5],
        ];

        foreach ($liyueSubs as $sub) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $genshinExplore->id, 'nama' => $sub['nama']],
                [
                    'parent_id'  => $liyue->id,
                    'jenis'      => 'utama',
                    'kategori'   => 'Sub-Wilayah',
                    'harga'      => $sub['harga'],
                    'satuan'     => 'persen',
                    'nilai_maks' => null,
                    'urutan'     => $sub['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // Layanan Genshin 2: Spiral Abyss (Tipe: Pilihan)
        $genshinAbyss = Layanan::updateOrCreate(
            ['game_id' => $genshin->id, 'nama_layanan' => 'Spiral Abyss & Paket Story'],
            [
                'gambar'         => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=800&auto=format&fit=crop&q=80',
                'tipe_harga'     => TipeHarga::Pilihan,
                'deskripsi'      => 'Jasa clear Spiral Abyss Full 36 Star dan pengerjaan Chapter Archon Quest.',
                'estimasi_waktu' => '1 - 3 Jam',
                'aktif'          => true,
            ]
        );

        $abyssPackages = [
            ['nama' => 'Spiral Abyss Full Floor 9-12 (36 Star)', 'harga' => 75000, 'satuan' => 'paket', 'nilai_maks' => 1, 'urutan' => 1],
            ['nama' => 'Spiral Abyss Floor 11-12 (18 Star)', 'harga' => 50000, 'satuan' => 'paket', 'nilai_maks' => 1, 'urutan' => 2],
            ['nama' => 'Spiral Abyss Floor 12 Only (9 Star)', 'harga' => 35000, 'satuan' => 'paket', 'nilai_maks' => 1, 'urutan' => 3],
            ['nama' => 'Archon Quest (Per Chapter/Act)', 'harga' => 45000, 'satuan' => 'chapter', 'nilai_maks' => 10, 'urutan' => 4],
        ];

        foreach ($abyssPackages as $pkg) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $genshinAbyss->id, 'nama' => $pkg['nama']],
                [
                    'parent_id'  => null,
                    'jenis'      => 'utama',
                    'kategori'   => 'Tantangan & Quest',
                    'harga'      => $pkg['harga'],
                    'satuan'     => $pkg['satuan'],
                    'nilai_maks' => $pkg['nilai_maks'],
                    'urutan'     => $pkg['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 3. GAME: VALORANT
        // ─────────────────────────────────────────────────────────────
        $valorant = Game::updateOrCreate(
            ['nama_game' => 'Valorant'],
            [
                'kategori'  => 'FPS',
                'deskripsi' => 'Joki Competitive Tier Rank Valorant dikerjakan oleh Immortal/Radiant worker profesional tanpa third party.',
                'gambar'    => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&auto=format&fit=crop&q=80',
                'aktif'     => true,
            ]
        );

        $valRank = Layanan::updateOrCreate(
            ['game_id' => $valorant->id, 'nama_layanan' => 'Joki Tier Competitive'],
            [
                'gambar'         => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800&auto=format&fit=crop&q=80',
                'tipe_harga'     => TipeHarga::Tangga,
                'deskripsi'      => 'Push rank tier dari Iron hingga Radiant dengan perhitungan harga per divisi.',
                'estimasi_waktu' => '1 - 4 Hari',
                'aktif'          => true,
            ]
        );

        $valTiers = [
            ['nama' => 'Iron', 'urutan' => 1, 'harga' => 20000, 'nilai_maks' => 3],
            ['nama' => 'Bronze', 'urutan' => 2, 'harga' => 25000, 'nilai_maks' => 3],
            ['nama' => 'Silver', 'urutan' => 3, 'harga' => 35000, 'nilai_maks' => 3],
            ['nama' => 'Gold', 'urutan' => 4, 'harga' => 45000, 'nilai_maks' => 3],
            ['nama' => 'Platinum', 'urutan' => 5, 'harga' => 60000, 'nilai_maks' => 3],
            ['nama' => 'Diamond', 'urutan' => 6, 'harga' => 85000, 'nilai_maks' => 3],
            ['nama' => 'Ascendant', 'urutan' => 7, 'harga' => 120000, 'nilai_maks' => 3],
            ['nama' => 'Immortal', 'urutan' => 8, 'harga' => 180000, 'nilai_maks' => 3],
        ];

        foreach ($valTiers as $tier) {
            LayananItem::updateOrCreate(
                ['layanan_id' => $valRank->id, 'nama' => $tier['nama']],
                [
                    'parent_id'  => null,
                    'jenis'      => 'utama',
                    'kategori'   => 'Competitive Tier',
                    'harga'      => $tier['harga'],
                    'satuan'     => 'tier',
                    'nilai_maks' => $tier['nilai_maks'],
                    'urutan'     => $tier['urutan'],
                    'aktif'      => true,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 4. GAME: FREE FIRE & HONOR OF KINGS (Additional Catalog)
        // ─────────────────────────────────────────────────────────────
        Game::updateOrCreate(
            ['nama_game' => 'Free Fire'],
            [
                'kategori'  => 'Battle Royale',
                'deskripsi' => 'Joki Push Rank Battle Royale & Clash Squad Free Fire sampai Master & Grandmaster.',
                'gambar'    => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800&auto=format&fit=crop&q=80',
                'aktif'     => true,
            ]
        );

        Game::updateOrCreate(
            ['nama_game' => 'Honor of Kings'],
            [
                'kategori'  => 'MOBA',
                'deskripsi' => 'Joki rank game Honor of Kings (HOK) Global server terpercaya dan pengerjaan kilat.',
                'gambar'    => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800&auto=format&fit=crop&q=80',
                'aktif'     => true,
            ]
        );
    }
}
