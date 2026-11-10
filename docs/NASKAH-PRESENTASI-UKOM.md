# 🎤 Naskah Presentasi UKOM — EiraStore

**Catatan:** Naskah ini adalah panduan alur bicara. Tidak perlu dihafal kata per kata — pahami alurnya, lalu jelaskan dengan bahasa kamu sendiri. Yang penting **urutan** dan **poin-poin kunci** tidak terlewat.

---

## BAGIAN 1: PEMBUKAAN (± 2 menit)

### Yang kamu ucapkan:

> "Assalamualaikum warahmatullahi wabarakatuh. Perkenalkan nama saya **Raya**, dari kelas ... jurusan **Rekayasa Perangkat Lunak**."
>
> "Project UKOM yang saya buat berjudul: **Rancang Bangun Sistem Informasi Layanan Joki Game Berbasis Web**, dengan studi kasus **EiraStore**."

### Latar Belakang Masalah:

> "Jadi latar belakang project ini adalah, saat ini banyak jasa joki game yang transaksinya masih dilakukan lewat chat biasa seperti WhatsApp atau DM Instagram. Cara ini punya banyak masalah:"
>
> 1. "**Harga dihitung manual** dan sering salah — apalagi kalau jenis joki-nya bervariasi seperti push rank, explore wilayah, atau quest."
> 2. "**Data akun game customer tersebar** di chat — password akun game dikirim lewat pesan biasa, sangat tidak aman."
> 3. "**Status pesanan tidak jelas** — customer tidak tahu pesanannya sudah dikerjakan atau belum."
> 4. "**Bukti pembayaran tercampur** di chat dan sulit diverifikasi."
> 5. "**Sulit membuktikan pekerjaan sudah selesai** — sering terjadi sengketa antara customer dan pekerja."
>
> "Dari masalah-masalah itu, saya membangun **EiraStore** — sebuah website yang mengotomasi seluruh proses pemesanan joki game, dari pemesanan, pembayaran, pengerjaan, sampai penyelesaian."

---

## BAGIAN 2: TUJUAN PROJECT (± 1 menit)

> "Tujuan utama project ini adalah menyediakan **sistem pemesanan joki game yang aman, transparan, dan terstruktur**."
>
> "Secara spesifik:"
> 1. "**Mengotomasi perhitungan harga** — harga dihitung otomatis berdasarkan pilihan customer, tidak perlu hitung manual lagi."
> 2. "**Mengamankan data akun game** — password akun game customer dienkripsi di database dan otomatis dihapus setelah pesanan selesai."
> 3. "**Memberikan transparansi status** — setiap perubahan status pesanan tercatat dan bisa dilihat oleh customer."
> 4. "**Mengatur alur kerja lengkap** — dari pemesanan sampai selesai, termasuk jalur revisi dan komplain."

---

## BAGIAN 3: TEKNOLOGI YANG DIPAKAI (± 2 menit)

> "Untuk teknologi yang saya gunakan:"

| Komponen | Teknologi | Penjelasan singkat |
|----------|-----------|-------------------|
| Backend | **Laravel (PHP 8.3)** | "Framework PHP untuk membangun logika server dan API" |
| Frontend | **Blade + Tailwind CSS + Alpine.js** | "Blade itu template engine bawaan Laravel, Tailwind untuk styling, Alpine.js untuk interaksi ringan di browser" |
| Autentikasi | **Laravel Breeze** | "Paket starter dari Laravel untuk sistem login dan registrasi" |
| Database | **MySQL** | "Database relasional untuk menyimpan seluruh data" |
| Build Tool | **Vite** | "Untuk mengompilasi CSS dan JavaScript" |
| Version Control | **Git + GitHub** | "Untuk mencatat perubahan kode dan menyimpan di cloud" |
| Testing | **PHPUnit** | "Framework testing untuk memastikan fitur berjalan benar" |
| Code Style | **Laravel Pint** | "Untuk memformat kode agar konsisten sesuai standar PSR-12" |

> "Arsitektur yang saya pakai adalah **MVC — Model, View, Controller**:"
> - "**Model** di folder `app/Models/` — mengelola data dan relasi database. Contoh: `Pesanan.php`, `Game.php`, `User.php`."
> - "**View** di folder `resources/views/` — menampilkan halaman ke user. Menggunakan file `.blade.php`. Contoh: `beranda.blade.php`, `pesanan/create.blade.php`."
> - "**Controller** di folder `app/Http/Controllers/` — menerima request, memproses logika, dan mengembalikan response. Contoh: `PesananController.php`, `Admin/PesananController.php`."
>
> "Alur kerjanya: **User akses URL → Route arahkan ke Controller → Controller ambil data lewat Model → Controller kirim data ke View → View tampil ke user**."

---

## BAGIAN 4: ROLE DAN HAK AKSES (± 3 menit)

> "Di EiraStore ada **3 role utama**:"

### 1. Customer (14 fitur)
> "Customer adalah pemilik akun game yang memesan joki. Customer bisa:"
> - "Melihat katalog game dan layanan joki tanpa login"
> - "Membuat pesanan dan melihat estimasi harga otomatis"
> - "Mengunggah bukti pembayaran"
> - "Merevisi data akun game kalau diminta admin"
> - "Menyetujui atau mengomplain hasil pengerjaan"
> - "Memberi rating dan ulasan setelah pesanan selesai"

### 2. Admin (30 fitur)
> "Admin adalah pengelola toko. Admin bisa:"
> - "Mengelola data game, layanan joki, dan item layanan (CRUD)"
> - "Mengelola data worker (tambah, ubah, nonaktifkan)"
> - "Memverifikasi data pesanan dan bukti pembayaran"
> - "Menugaskan pesanan ke worker"
> - "Memverifikasi laporan penyelesaian dari worker"
> - "Melihat dashboard dan laporan transaksi"
>
> "Yang penting: **Admin tidak bisa melihat password akun game customer** — ini untuk keamanan."

### 3. Worker (6 fitur)
> "Worker adalah orang yang mengerjakan joki. Worker bisa:"
> - "Melihat pesanan yang ditugaskan kepadanya saja"
> - "Melihat data akun game termasuk password (untuk login ke game customer)"
> - "Memulai pengerjaan dan melaporkan penyelesaian dengan foto bukti"
>
> "Worker **tidak bisa** melihat pesanan worker lain atau mengakses halaman admin."

### Keamanan Akses
> "Setiap role dilindungi oleh **middleware**. Kalau customer coba buka halaman admin, dia akan mendapat error **403 Forbidden**. Begitu juga sebaliknya."

---

## BAGIAN 5: DATABASE (± 3 menit)

> "Database saya terdiri dari **10 tabel utama**:"

| No | Tabel | Fungsi |
|----|-------|--------|
| 1 | `users` | Data akun (customer, admin, worker) |
| 2 | `games` | Data game yang dilayani (Mobile Legends, Genshin Impact) |
| 3 | `layanan` | Jenis joki per game (Push Rank, Explore, Quest) |
| 4 | `layanan_item` | Detail item dan harga per layanan |
| 5 | `pesanan` | Data pesanan customer |
| 6 | `pesanan_item` | Item yang dipilih customer dalam pesanan (snapshot harga) |
| 7 | `pembayaran` | Bukti dan status pembayaran |
| 8 | `laporan_penyelesaian` | Bukti foto penyelesaian dari worker |
| 9 | `riwayat_status_pesanan` | Jejak audit setiap perubahan status |
| 10 | `ulasan` | Rating dan komentar customer |

> "Ada juga tabel pendukung: `banners` untuk promosi, `metode_pembayaran_tujuan` untuk rekening tujuan, dan tabel bawaan Laravel untuk cache, session, dan jobs."

### Konsep Penting: Snapshot Harga
> "Satu hal penting di database saya: ketika customer membuat pesanan, **nama dan harga item disalin** dari `layanan_item` ke `pesanan_item`. Jadi kalau admin mengubah harga layanan besok, pesanan yang sudah dibuat hari ini **tidak ikut berubah**. Ini penting untuk integritas data transaksi."

### 3 Tipe Harga
> "Sistem saya mendukung **3 tipe perhitungan harga** yang berbeda:"
> 1. "**Tangga** — untuk push rank. Harga dihitung dari selisih tingkat awal ke tujuan. Contoh: dari Grandmaster ke Epic."
> 2. "**Progres** — untuk explore wilayah. Harga dasar dikurangi progres yang sudah dimiliki customer. Contoh: explore Mondstadt sudah 60%, tinggal bayar 40%."
> 3. "**Pilihan** — untuk quest atau achievement. Customer pilih item, harga dijumlahkan."

---

## BAGIAN 6: DEMO WORKFLOW (± 10 menit)

**⚠️ INI BAGIAN TERPENTING — Demonstrasikan langsung di browser!**

> "Sekarang saya akan mendemonstrasikan alur pesanan dari awal sampai selesai."

### Langkah Demo:

**1. Landing Page (sebagai Pengunjung)**
> "Ini halaman utama EiraStore. Di sini ada banner promosi, katalog game, dan ulasan pelanggan."
> → Tunjukkan katalog game, klik salah satu game

**2. Pilih Layanan & Isi Form Pesanan**
> "Setelah memilih game, customer bisa lihat layanan yang tersedia. Saya pilih layanan ini."
> → Isi form pesanan, tunjukkan **harga yang terhitung otomatis** di panel estimasi
> "Perhatikan harga ini dihitung otomatis oleh JavaScript di browser. Tapi nanti saat disimpan, **server akan menghitung ulang** — karena prinsipnya **never trust the client**, data dari browser bisa dimanipulasi."

**3. Login & Konfirmasi Pesanan**
> "Karena belum login, saya diarahkan ke halaman login dulu. Setelah login, muncul halaman konfirmasi pesanan."
> → Login → Konfirmasi → Pesanan dibuat dengan status **Menunggu Pembayaran**
> "Pesanan ini punya batas waktu bayar **2 jam**. Kalau tidak dibayar, otomatis kedaluwarsa."

**4. Upload Bukti Pembayaran**
> → Upload bukti → Status berubah ke **Menunggu Verifikasi**

**5. Admin: Verifikasi & Tugaskan Worker**
> → Login sebagai Admin
> "Admin memeriksa data pesanan dan bukti pembayaran."
> → Setujui data → Setujui pembayaran → Status: **Siap Ditugaskan**
> → Pilih worker → Tugaskan → Status: **Diproses**

**6. Worker: Kerjakan & Lapor**
> → Login sebagai Worker
> "Worker bisa melihat data akun game termasuk password untuk login ke game customer."
> → Mulai pengerjaan → Status: **Sedang Dikerjakan**
> → Upload bukti selesai → Status: **Laporan Masuk**

**7. Admin: Verifikasi Laporan**
> → Login Admin → Verifikasi laporan → Status: **Worker Selesai**
> → Kirim notifikasi ke customer → Status: **Menunggu Konfirmasi**

**8. Customer: Konfirmasi Hasil**
> → Login Customer
> "Customer bisa melihat foto bukti pengerjaan."
> → Setujui hasil → Status: **Hasil Disetujui** (password akun game **otomatis dihapus**)
> → Konfirmasi selesai → Status: **Selesai**
> → Beri rating & ulasan

> "Itulah alur lengkap satu pesanan dari awal sampai selesai."

---

## BAGIAN 7: STATUS PESANAN (± 2 menit)

> "Total ada **15 status pesanan** yang membentuk state machine:"

```
Menunggu Pembayaran → Menunggu Verifikasi → Siap Ditugaskan → Diproses
    ↓ (2 jam)              ↓                                      ↓
  Kedaluwarsa        Perlu Revisi Data                    Sedang Dikerjakan
                           ↓                                      ↓
                     (Customer revisi)                       Laporan Masuk
                           ↓                                 ↙         ↘
                     Menunggu Verifikasi            Perlu Revisi    Worker Selesai
                                                    Hasil              ↓
                                                                 Menunggu Konfirmasi
                                                                  ↙            ↘
                                                           Dikomplain    Hasil Disetujui
                                                                              ↓
                                                                           Selesai
```

> "Ada juga 2 status terminal yang tidak bisa berubah lagi: **Dibatalkan** (oleh admin, misal pembayaran palsu) dan **Kedaluwarsa** (tidak bayar dalam 2 jam)."

---

## BAGIAN 8: FITUR KEAMANAN (± 2 menit)

> "Untuk keamanan, ada beberapa hal yang saya terapkan:"

1. **Enkripsi Password Akun Game**
> "Password akun game customer disimpan dengan **enkripsi AES-256**. Di database terlihat seperti sandi acak. Ini berbeda dengan password login yang di-**hash** pakai bcrypt — hash tidak bisa dibaca balik, sedangkan enkripsi bisa didekripsi supaya worker bisa membacanya."

2. **Auto-Purge Password**
> "Password akun game **otomatis dihapus** pada 4 kondisi: pesanan selesai, dibatalkan, kedaluwarsa, atau hasil disetujui customer. Jadi tidak ada sisa data password di database setelah pesanan berakhir."

3. **Database Transaction & Row Locking**
> "Untuk mencegah race condition — misalnya dua admin menugaskan worker ke pesanan yang sama secara bersamaan — saya menggunakan **database transaction** dengan **row locking**."

4. **Validasi Server (Never Trust the Client)**
> "Semua data dari browser divalidasi ulang di server. Harga yang dihitung JavaScript di browser **tidak dipercaya** — server menghitung ulang dari database."

5. **Middleware Otorisasi**
> "Setiap route dilindungi middleware `role` yang memastikan hanya role yang berhak yang bisa mengakses."

---

## BAGIAN 9: SCHEDULER / PROSES OTOMATIS (± 1 menit)

> "Ada 2 proses otomatis yang berjalan di background:"

1. **Cek Pesanan Kedaluwarsa** (setiap 5 menit)
> "Memeriksa pesanan yang belum dibayar lebih dari 2 jam, lalu otomatis mengubah statusnya menjadi kedaluwarsa dan menghapus password akun game."

2. **Auto-Complete Pesanan** (setiap 1 jam)
> "Kalau customer sudah dikirim notifikasi hasil tapi tidak konfirmasi dalam 24 jam, pesanan otomatis dianggap selesai."

> "Secara teknis, ini menggunakan **Laravel Scheduler** yang menjalankan **Artisan Command** secara berkala. Konfigurasinya ada di file `routes/console.php`."

---

## BAGIAN 10: TESTING (± 1 menit)

> "Untuk memastikan semua fitur berjalan dengan benar, saya membuat **163 automated test** dengan **570 assertions** menggunakan PHPUnit. Semua test **100% lulus**."
>
> "Test ini mencakup: autentikasi, otorisasi per role, alur pemesanan, verifikasi pembayaran, workflow worker, profil user, dan berbagai edge case."

**→ Jalankan `php artisan test` di depan asesor kalau diminta bukti!**

---

## BAGIAN 11: PENUTUP (± 1 menit)

> "Jadi kesimpulannya, EiraStore adalah sistem informasi layanan joki game yang memiliki:"
> - "**46 fitur** untuk 3 role (Customer, Admin, Worker)"
> - "**10 tabel database** dengan relasi yang terstruktur"
> - "**3 tipe perhitungan harga** otomatis (tangga, progres, pilihan)"
> - "**Keamanan berlapis**: enkripsi, auto-purge password, validasi server, middleware otorisasi"
> - "**163 automated test** yang 100% lulus"
> - "**Kode bersih** dengan dokumentasi bahasa Indonesia dan standar PSR-12"
>
> "Untuk pengembangan lanjutan, sistem ini bisa ditambahkan payment gateway otomatis, notifikasi email atau WhatsApp, dan dukungan multi bahasa."
>
> "Demikian presentasi dari saya. Terima kasih."

---

## 📋 CHEAT SHEET — Istilah Penting yang Harus Dihafal

| Istilah | Artinya | Kapan disebut |
|---------|---------|---------------|
| **MVC** | Model-View-Controller, pola arsitektur | Saat jelaskan struktur project |
| **Migration** | File PHP untuk membuat/mengubah tabel database | Saat jelaskan database |
| **Middleware** | Penyaring request sebelum masuk controller | Saat jelaskan keamanan akses |
| **Autentikasi** | Memastikan **siapa** kamu (login) | Saat jelaskan login |
| **Otorisasi** | Memastikan **boleh atau tidak** kamu akses | Saat jelaskan role/hak akses |
| **Enkripsi** | Mengacak data agar tidak bisa dibaca, tapi bisa dikembalikan | Saat jelaskan password akun game |
| **Hashing** | Mengacak data secara satu arah, tidak bisa dikembalikan | Saat jelaskan password login |
| **Scheduler** | Proses otomatis yang berjalan berkala | Saat jelaskan kedaluwarsa/auto-complete |
| **Artisan Command** | Perintah CLI buatan sendiri di Laravel | Saat jelaskan scheduler |
| **Snapshot harga** | Menyalin harga saat pesanan dibuat | Saat jelaskan pesanan_item |
| **Race condition** | Dua proses mengubah data yang sama bersamaan | Saat jelaskan transaction/locking |
| **Never trust the client** | Jangan percaya data dari browser | Saat jelaskan validasi server |
| **State machine** | Aturan perpindahan status yang terstruktur | Saat jelaskan status pesanan |
| **CRUD** | Create, Read, Update, Delete | Saat jelaskan fitur admin |
| **Eloquent** | ORM bawaan Laravel untuk query database | Saat jelaskan model |
| **Blade** | Template engine bawaan Laravel | Saat jelaskan view |
| **PSR-12** | Standar penulisan kode PHP | Saat jelaskan code style |

---

## 🔧 Command Penting yang Harus Dihafal

```bash
php artisan serve              # Jalankan server development
php artisan migrate            # Jalankan semua migration
php artisan migrate:rollback   # Batalkan migration terakhir
php artisan migrate:status     # Lihat status migration
php artisan route:list         # Lihat semua route yang terdaftar
php artisan test               # Jalankan semua automated test
php artisan tinker             # Console interaktif Laravel
vendor/bin/pint                # Format kode otomatis (PSR-12)
npm run build                  # Compile asset CSS/JS untuk produksi
npm run dev                    # Jalankan Vite dev server
```

---

## ⚡ Tips Terakhir

1. **Jangan bilang "kayaknya" atau "mungkin"** — bicara dengan yakin
2. **Kalau tidak tahu jawaban**, bilang: "Untuk detail teknis bagian itu, saya perlu cek kodenya langsung" — lalu buka VS Code dan tunjukkan
3. **Buka VS Code** saat presentasi — tunjukkan struktur folder yang rapi
4. **Siapkan 3 akun** untuk demo: 1 customer, 1 admin, 1 worker
5. **Siapkan data dummy** yang sudah lengkap agar demo lancar
6. **Kalau asesor buat error**, baca pesan error-nya dengan tenang — Laravel memberikan pesan yang jelas (file, baris, jenis error)
