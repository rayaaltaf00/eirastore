# EiraStore — Lampiran Teknis dan Panduan Pengerjaan

Dokumen pendamping `01-Master-Context-PRD.md`. Dokumen utama menjelaskan **apa** yang dibangun dan **aturannya**; lampiran ini menjelaskan **bagaimana** membangunnya: instalasi, kode dasar, urutan pengerjaan, template prompt untuk AI, dan panduan debugging untuk UKOM.

> **Aturan emas:** jika lampiran ini berbeda dengan dokumen utama, **dokumen utama yang benar**. Kode di lampiran adalah titik awal yang sudah disusun mengikuti dokumen utama; sesuaikan bila ada hal yang belum cocok, lalu laporkan ke pemilik proyek.

**Daftar isi**

- A. Panduan instalasi rinci (Windows + Laragon)
- B. Migration (10 tabel)
- C. Enum
- D. Model
- E. Seeder
- F. Kode inti (middleware, service, command, controller kunci, penyesuaian Breeze)
- G. Rute lengkap
- H. Urutan pengerjaan dan uji per modul
- I. Template prompt untuk AI
- J. Panduan debugging untuk UKOM
- K. Daftar periksa persiapan demo

---

## A. Panduan Instalasi Rinci (Windows + Laragon)

### A.1 Perangkat lunak yang dibutuhkan

| Perangkat lunak | Fungsi | Cara memastikan terpasang |
|---|---|---|
| **Laragon (Full)** | Server lokal (Apache atau Nginx), MySQL, PHP, Composer, Node.js, Git dalam satu paket | Buka Laragon, tekan **Start All** |
| **PHP 8.3 atau lebih baru** | Bahasa pemrograman (syarat Laravel 13) | `php -v` |
| **Composer** | Pengelola paket PHP | `composer -V` |
| **Node.js (LTS) dan npm** | Menjalankan Vite untuk membangun CSS dan JS | `node -v` dan `npm -v` |
| **Git** | Riwayat dan pemulihan kode | `git --version` |
| **Visual Studio Code** | Editor kode | Buka VS Code |
| **HeidiSQL atau phpMyAdmin** | Mengelola database (bawaan Laragon) | Menu Laragon: Database |
| **Browser (Chrome atau Edge)** | Menjalankan aplikasi | |

Ekstensi VS Code yang disarankan: *PHP Intelephense*, *Laravel Blade Snippets* (atau *Laravel Blade formatter*), *Tailwind CSS IntelliSense*, *GitHub Copilot*.

### A.2 Langkah instalasi

**Langkah 1. Pasang Laragon.** Unduh Laragon edisi **Full** dari situs resminya, pasang di `C:\laragon`, lalu jalankan dan tekan **Start All** (Apache dan MySQL berstatus berjalan).

**Langkah 2. Pilih PHP 8.3.** Di Laragon: klik kanan ikon, **PHP** → **Version** → pilih versi **8.3.x**. Buka **Terminal** Laragon dan jalankan:

```bash
php -v
```

Pastikan keluarannya menunjukkan versi **8.3 atau lebih baru**. Catat hasilnya untuk bahan presentasi.

**Langkah 3. Periksa alat lain.** Di Terminal Laragon:

```bash
composer -V
node -v
npm -v
git --version
```

Jika `node` atau `git` belum ada, pasang **Node.js LTS** (nodejs.org) dan **Git** (git-scm.com), lalu buka terminal baru dan periksa lagi.

**Langkah 4. Buat project Laravel.**

```bash
cd C:\laragon\www
composer create-project laravel/laravel eirastore
cd eirastore
php artisan --version
```

Perintah terakhir harus menampilkan **Laravel Framework 13.x**. Catat hasilnya.

**Langkah 5. Buat database.** Buka HeidiSQL (menu Laragon: Database), buat database baru bernama `eirastore` dengan collation `utf8mb4_unicode_ci`. Alternatif lewat terminal:

```bash
mysql -u root -e "CREATE DATABASE eirastore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Langkah 6. Atur `.env`.** Buka `.env` di VS Code. **Laravel baru memakai SQLite secara bawaan, ubah ke MySQL:**

```env
APP_NAME=EiraStore
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eirastore
DB_USERNAME=root
DB_PASSWORD=
```

Lalu buka `config/app.php`, ubah `'timezone'` menjadi `'Asia/Jakarta'`.

**Langkah 7. Pasang Laravel Breeze (stack Blade).**

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Jawab pertanyaan: *dark mode* → **No** (tema gelap dibuat sendiri), *testing framework* → **PHPUnit**. **Jangan jalankan `php artisan migrate` dulu.** Migration `users` bawaan harus diganti terlebih dahulu (bagian B).

**Langkah 8. Pasang dependensi front-end.**

```bash
npm install
```

Setelah itu cek `package.json` untuk mengetahui versi `tailwindcss` (3.x atau 4.x), karena penulisan konfigurasi warna berbeda (dokumen utama, bagian 6.8.4).

**Langkah 9. Inisialisasi Git.**

```bash
git init
git add .
git commit -m "awal: Laravel 13 + Breeze (belum dimodifikasi)"
git tag modul-00
```

**Langkah 10. Siapkan struktur proyek, migration, model, dan seeder** mengikuti bagian B sampai E, lalu:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

**Langkah 11. Jalankan aplikasi.** Buka tiga terminal di folder project:

| Terminal | Perintah | Fungsi |
|---|---|---|
| 1 | `php artisan serve` | Menjalankan aplikasi di `http://localhost:8000` |
| 2 | `npm run dev` | Membangun CSS dan JS (Vite) selama pengembangan |
| 3 | `php artisan schedule:work` | Menjalankan scheduler kedaluwarsa pesanan (cukup saat menguji fitur itu dan saat demo) |

Buka `http://localhost:8000`. Halaman muncul tanpa error berarti instalasi berhasil.

### A.3 Menjalankan dari nol di komputer lain (untuk UKOM)

```bash
git clone <alamat-repository> eirastore
cd eirastore
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Ingat: atur lagi `DB_*` di `.env` dan buat database `eirastore` terlebih dahulu. **`APP_KEY` yang berbeda membuat password akun game yang sudah terenkripsi tidak bisa dibaca**, jadi untuk data baru tidak masalah, tetapi jangan memindahkan data lama tanpa `APP_KEY` yang sama.

### A.4 Masalah umum saat instalasi

| Gejala | Penyebab dan solusi |
|---|---|
| `Class "..." not found` | Jalankan `composer dump-autoload` |
| `Vite manifest not found` | Jalankan `npm run dev` (selama pengembangan) atau `npm run build` |
| `could not find driver` atau koneksi database gagal | Pastikan MySQL di Laragon berjalan, `DB_CONNECTION=mysql`, dan nama database benar |
| Gambar game tidak muncul (404) | Jalankan `php artisan storage:link` |
| Perubahan `.env` atau config tidak terbaca | Jalankan `php artisan optimize:clear` |
| Port 8000 terpakai | `php artisan serve --port=8001` |
| `php` tidak dikenali | Buka Terminal dari Laragon (bukan CMD biasa), atau periksa PATH |

---

## B. Migration (10 tabel)

Urutan berkas penting karena foreign key. **Ganti isi** berkas migration `users` bawaan (`database/migrations/0001_01_01_000000_create_users_table.php`) dengan versi di bawah, lalu buat sembilan migration lain dengan `php artisan make:migration create_xxx_table` dan isi seperti berikut. Pastikan **nama berkas berurutan** (games, layanan, layanan_item, pesanan, pesanan_item, pembayaran, laporan_penyelesaian, riwayat_status_pesanan, ulasan).

> Kolom `rating` pada `ulasan` dibatasi 1 sampai 5 oleh validasi aplikasi (constraint CHECK pada skrip SQL referensi bersifat opsional di migration).

### B.1 `users` (mengganti berkas bawaan)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('no_hp', 20);
            $table->string('password');
            $table->string('role', 20);                          // customer, admin, worker
            $table->string('status_akun', 20)->default('aktif'); // aktif, nonaktif
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
```

### B.2 `games`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('nama_game', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('gambar');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
```

### B.3 `layanan`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->restrictOnDelete();
            $table->string('nama_layanan', 100);
            $table->text('deskripsi')->nullable();
            $table->string('tipe_harga', 20);                    // tangga, progres, pilihan
            $table->string('estimasi_waktu', 50)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['game_id', 'nama_layanan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
```

### B.4 `layanan_item`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('layanan_item')->cascadeOnDelete();
            $table->string('jenis', 20)->default('utama');       // utama, tambahan
            $table->string('kategori', 50)->nullable();
            $table->string('nama', 150);
            $table->integer('urutan')->default(0);
            $table->decimal('harga', 12, 2)->default(0);
            $table->unsignedInteger('nilai_maks')->nullable();
            $table->string('satuan', 20)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['layanan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan_item');
    }
};
```

### B.5 `pesanan`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan', 20)->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();

            $table->string('id_akun_game', 100);
            $table->string('login_via', 30)->nullable();
            $table->string('username_akun_game', 100);
            $table->text('password_akun_game')->nullable();      // terenkripsi, dihapus (NULL) pada kondisi tertentu
            $table->string('nickname_game', 100)->nullable();
            $table->string('server_game', 50)->nullable();
            $table->json('data_tambahan')->nullable();           // zone_id, catatan_login (tidak sensitif)
            $table->text('catatan_customer')->nullable();

            $table->decimal('total_harga', 12, 2);
            $table->string('status', 30)->default('menunggu_pembayaran');
            $table->boolean('data_disetujui')->default(false);
            $table->dateTime('batas_waktu_bayar');
            $table->dateTime('selesai_at')->nullable();
            $table->dateTime('kredensial_dihapus_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'batas_waktu_bayar']);
            $table->index(['worker_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
```

### B.6 `pesanan_item`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->foreignId('layanan_item_id')->nullable()->constrained('layanan_item')->nullOnDelete();
            $table->string('peran', 20);                         // awal, tujuan, dipilih, tambahan
            $table->decimal('nilai', 10, 2)->nullable();
            $table->string('nama_item', 150);                    // salinan nama saat dipesan
            $table->decimal('harga_satuan', 12, 2);              // salinan harga saat dipesan
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_item');
    }
};
```

### B.7 `pembayaran`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->string('metode_pembayaran', 50);
            $table->string('bukti_transfer');
            $table->string('status', 30)->default('menunggu_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('diverifikasi_at')->nullable();
            $table->timestamps();

            $table->unique('pesanan_id');                        // satu pembayaran per pesanan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
```

### B.8 `laporan_penyelesaian`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_penyelesaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->foreignId('worker_id')->constrained('users')->restrictOnDelete();
            $table->string('foto_bukti');
            $table->string('status', 30)->default('menunggu_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('diverifikasi_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_penyelesaian');
    }
};
```

### B.9 `riwayat_status_pesanan`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->string('status_lama', 30)->nullable();
            $table->string('status_baru', 30);
            $table->text('catatan')->nullable();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();         // hanya created_at

            $table->index(['pesanan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_pesanan');
    }
};
```

### B.10 `ulasan`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->unsignedTinyInteger('rating');               // 1 sampai 5 (divalidasi aplikasi)
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->unique('pesanan_id');                        // satu ulasan per pesanan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};
```
