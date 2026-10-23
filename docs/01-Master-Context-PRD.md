# EiraStore — Master Context \& PRD

**Sistem Informasi Layanan Joki Game Berbasis Web (Proyek UKOM)**

|Keterangan|Isi|
|-|-|
|Versi dokumen|1.0|
|Tanggal dibuat|21 Oktober 2026|
|Pemilik proyek|Raya (siswa SMK jurusan RPL, mengerjakan sendiri)|
|Penyusun|Hasil diskusi Raya dan Claude (Anthropic)|
|Bahasa dokumen|Indonesia (nama tabel, kolom, dan istilah teknis ditulis apa adanya)|
|Dokumen pendamping|`02-Lampiran-Teknis-dan-Panduan-Pengerjaan.md` (instalasi, migration, kode inti, urutan pengerjaan, template prompt, panduan debugging)|
|File pendukung|`Daftar-Fitur-v2.docx`, `UseCase-Diagram-v2.drawio`, `Activity-Diagram-Customer-v2.drawio`, `Activity-Diagram-Admin-v2.drawio`, `Activity-Diagram-Worker-v2.drawio`, `Activity-Diagram-Sistem-v2.drawio`, `ERD-PDM-Joki-Game-v2.drawio`|

\---

## 0\. Cara Memakai Dokumen Ini

### 0.1 Untuk AI atau developer yang menerima dokumen ini

1. Baca seluruh dokumen ini sebelum menulis kode. Dokumen ini adalah **sumber kebenaran tunggal**. Jika ada pertanyaan yang jawabannya tidak ada di sini, tanyakan ke pemilik proyek, jangan berasumsi.
2. Pemilik proyek adalah pemula di Laravel (paham dasar migration, relasi model, dan controller; belum mahir validasi, middleware, JavaScript, dan Git). Pada saat UKOM, penguji akan **menjelaskan dan mengotak-atik kode untuk memunculkan error**, lalu pemilik harus menemukan dan memperbaikinya sendiri. Karena itu:

   * Tulis kode yang **sederhana, konvensional, dan mudah dibaca**. Hindari trik, pola lanjutan, dan abstraksi yang tidak perlu.
   * Beri **komentar bahasa Indonesia** pada logika yang tidak langsung jelas.
   * Ikuti konvensi penamaan Laravel dan penamaan yang tertulis di dokumen ini.
   * Kerjakan **per modul kecil**, lalu minta pemilik menjalankan dan memeriksanya sebelum lanjut.
3. Jangan mengubah struktur database tanpa persetujuan pemilik. Jika menurutmu ada yang perlu diubah, ajukan sebagai usulan beserta alasannya.

### 0.2 Penanda status keputusan

|Penanda|Arti|
|-|-|
|**\[FINAL]**|Sudah disepakati pemilik proyek. Jangan diubah tanpa persetujuan.|
|**\[KEPUTUSAN CLAUDE]**|Pemilik ragu atau menyerahkan keputusan. Diputuskan Claude sebagai tindakan terbaik. Boleh diubah jika pemilik meminta.|
|**\[OPSIONAL]**|Dikerjakan hanya jika waktu cukup. Tidak boleh mengganggu fitur wajib.|

### 0.3 Ringkasan keputusan terpenting (TL;DR)

1. Nama sistem **EiraStore**. Aplikasi web pemesanan jasa joki game dengan 3 role: Customer, Admin, Worker. **\[FINAL]**
2. Satu pesanan berisi **satu layanan joki**. Pengerjaan dilakukan oleh **satu Worker** yang ditugaskan Admin. **\[FINAL]**
3. Pembayaran **manual** lewat transfer bank atau e-wallet, dengan unggah bukti dan verifikasi oleh Admin. Tidak memakai payment gateway atau QRIS API. **\[FINAL]**
4. Pesanan yang tidak dibayar dalam **1x24 jam otomatis kedaluwarsa** dan tidak bisa dibayar lagi. Customer harus membuat pesanan baru. **\[FINAL]**
5. Database memakai **10 tabel**. Jenis joki yang beragam (push rank, explore, quest, achievement, hero) ditampung oleh struktur **layanan → layanan\_item → pesanan\_item** dengan 3 tipe harga: `tangga`, `progres`, `pilihan`. **\[FINAL]**
6. Harga dihitung otomatis dari pilihan customer, **dihitung ulang di server**, dan disalin ke `pesanan\_item` supaya perubahan harga tidak mengubah pesanan lama. **\[FINAL]**
7. Password akun game customer disimpan **terenkripsi (bisa dibaca Worker)** dan **dihapus total** saat pesanan dibatalkan, kedaluwarsa, hasil disetujui, atau selesai. **\[FINAL]**
8. Landing page langsung menampilkan promosi, lalu katalog game, dengan ulasan di samping (atau di bawah pada layar kecil). Customer **boleh melihat katalog dan mengisi form tanpa login**; login baru diminta saat menekan tombol **Pesan**. **\[FINAL]**
9. Tidak ada inbox notifikasi. Pemberitahuan berupa **perubahan status dan catatan** yang tampil di halaman detail pesanan masing-masing role. **\[FINAL]**
10. Teknologi: **Laravel 13, PHP 8.3, MySQL (Laragon), Blade + Tailwind CSS lewat Laravel Breeze (stack Blade dengan Alpine.js), Vite, Git**. Demo berjalan di **localhost**. **\[FINAL]**
11. Tampilan tema gelap dengan warna dasar biru, **tanpa gradien neon ungu-pink ala template AI**. **\[FINAL]**
12. UKOM tanggal **9 sampai 11 November 2026**. Kode harus selesai dan bebas bug paling lambat **4 November 2026**. **\[FINAL]**

### 0.4 Glosarium

|Istilah|Arti dalam proyek ini|
|-|-|
|Joki|Jasa mengerjakan sesuatu di akun game milik orang lain atas permintaan pemilik akun.|
|Customer|Pemilik akun game yang memesan joki.|
|Worker|Orang yang mengerjakan joki di akun game customer.|
|Admin|Pengelola toko yang memverifikasi, menugaskan, dan mengawasi seluruh pesanan.|
|Layanan|Satu jenis joki milik sebuah game, misalnya "Joki Push Rank" atau "Joki Achievement".|
|Item layanan|Isi dari sebuah layanan: tingkat rank, wilayah, quest, achievement, hero, atau paket tetap, lengkap dengan harga.|
|Tipe harga|Cara menghitung harga sebuah layanan: `tangga`, `progres`, atau `pilihan`.|
|Tangga|Harga dihitung dari selisih posisi awal ke posisi tujuan pada tingkatan berurutan (contoh: rank Mobile Legends).|
|Progres|Harga dasar dikurangi progres yang sudah dimiliki customer (contoh: explore wilayah Genshin Impact).|
|Pilihan|Customer memilih satu atau banyak item, harga dijumlahkan (contoh: quest, achievement, paket tetap).|
|Item tambahan|Item opsional yang menambah harga atau fasilitas (contoh: memilih hero).|
|Draf pesanan|Isian form pesanan milik customer yang belum login, disimpan sementara di sesi sampai ia login.|
|Kedaluwarsa|Status pesanan yang tidak dibayar dalam 1x24 jam.|
|UKOM|Uji Kompetensi yang menjadi tujuan proyek ini (sebelumnya disebut UKK).|

\---

## 1\. Project Overview

### 1.1 Gambaran sistem

**EiraStore** adalah aplikasi web untuk memesan dan mengelola **jasa joki game**. Customer memilih game dan layanan joki, mengisi data akun game, melihat estimasi harga yang dihitung otomatis, lalu membayar secara manual. Admin memverifikasi data dan pembayaran, kemudian menugaskan pesanan ke Worker. Worker mengerjakan joki, melaporkan hasilnya dengan foto bukti, dan Admin menyampaikan hasilnya ke customer untuk dikonfirmasi. Customer yang puas menyelesaikan pesanan dan dapat memberi rating serta ulasan.

Sistem ini dirancang agar **universal untuk jangka panjang**: saat ini hanya dua game (Mobile Legends dan Genshin Impact) yang dilayani untuk keperluan UKOM, tetapi struktur database memungkinkan game baru dengan jenis joki yang berbeda ditambahkan hanya dengan memasukkan data lewat halaman Admin, tanpa membuat tabel baru.

### 1.2 Masalah yang diselesaikan

|Masalah pada pemesanan joki manual (lewat chat)|Solusi di EiraStore|
|-|-|
|Harga tiap pesanan dihitung manual dan rawan salah.|Harga dihitung otomatis dari pilihan customer dengan aturan yang sama untuk semua pesanan.|
|Data akun game dikirim lewat chat dan tersebar.|Data akun disimpan terstruktur, password dienkripsi dan dihapus setelah pekerjaan selesai.|
|Status pesanan tidak jelas bagi customer.|Setiap pesanan punya status dan riwayat perubahan yang terlihat di halaman detail.|
|Bukti pembayaran tercampur di chat.|Bukti diunggah per pesanan dan diverifikasi Admin dengan jejak pencatatan.|
|Sulit membuktikan pekerjaan sudah selesai.|Worker wajib mengunggah foto bukti dan customer mengonfirmasi hasil.|
|Revisi dan komplain tidak tercatat.|Seluruh catatan revisi, penolakan, dan komplain tersimpan di riwayat status pesanan.|
|Toko tampak tidak meyakinkan.|Landing page profesional dengan katalog, ulasan, dan akun customer yang jelas.|

### 1.3 Tujuan

**Tujuan utama:** menyediakan sistem pemesanan joki game yang aman, transparan, dan dapat dikembangkan, sekaligus menjadi proyek UKOM yang dapat dipertanggungjawabkan oleh pembuatnya.

**Tujuan turunan:**

1. Mengotomasi perhitungan harga untuk beragam jenis joki.
2. Mengatur alur kerja lengkap dari pemesanan sampai pesanan selesai, termasuk jalur revisi, penolakan, dan komplain.
3. Melindungi data akun game customer.
4. Menghasilkan dokumentasi perancangan yang konsisten (fitur, use case, activity diagram, ERD, PDM) dengan aplikasi yang dibangun.

### 1.4 Ruang lingkup

**Dalam lingkup (in scope):**

* 46 fitur unik untuk 3 role (Customer 14, Admin 30, Worker 6), lihat bagian 3.
* Katalog game dan layanan yang bisa dilihat publik.
* Pemesanan dengan tiga tipe harga dan perhitungan otomatis.
* Pembayaran manual (bank dan e-wallet) dengan verifikasi Admin.
* Penugasan, pengerjaan, pelaporan, revisi, komplain, dan konfirmasi penyelesaian.
* Rating dan ulasan, dashboard Admin, laporan transaksi.
* Proses otomatis kedaluwarsa pesanan (scheduler).
* Tampilan responsif untuk halaman Customer.

**Di luar lingkup (out of scope) untuk versi UKOM ini:**

* Payment gateway, QRIS API, atau verifikasi pembayaran otomatis.
* Notifikasi email, WhatsApp, atau push notification, dan inbox notifikasi.
* Verifikasi email, lupa password, dan ubah profil pengguna.
* Tugaskan ulang pesanan ke Worker lain (dicatat sebagai pengembangan lanjutan).
* Customer membatalkan pesanan sendiri (pesanan tidak dibayar kedaluwarsa otomatis).
* Satu pesanan berisi beberapa layanan (keranjang).
* Biaya persentase (misalnya ekspres +20%), harga hasil negosiasi, kupon dan diskon.
* Pengembalian dana otomatis (refund ditangani Admin di luar aplikasi).
* Ekspor PDF atau Excel (laporan hanya tampil di layar dan dapat dicetak).
* Multi bahasa (seluruh antarmuka berbahasa Indonesia).

### 1.5 Konteks UKOM dan batasan pengerjaan

|Aspek|Keterangan|
|-|-|
|Jadwal UKOM|**9 sampai 11 November 2026**|
|Batas selesai kode|**4 November 2026** (5 hari sebelum UKOM), sudah bebas bug|
|Sisa waktu saat dokumen ini dibuat|Sekitar 14 hari (21 Oktober sampai 4 November 2026)|
|Pengerjaan|**Individu** (hanya Raya)|
|Yang dinilai|Penguasaan terhadap sistem sendiri: penjelasan fitur, use case, dan activity diagram; perancangan database; mockup atau prototype; software dan tools yang dipakai beserta cara instalasi yang rinci; bahasa, framework, dan library; implementasi database dan query; struktur project (MVC, route, source code); hasil eksekusi (aplikasi berjalan)|
|Skenario penguji|Penguji akan **mengotak-atik kode untuk membuat error**, lalu pemilik harus menemukan penyebab dan memperbaikinya|
|Konsekuensi untuk kode|Kode harus **mudah dibaca dan dijelaskan** oleh pemula. Pemilik harus memahami setiap berkas yang dihasilkan AI.|
|Prioritas|Fitur **CRUD wajib** berjalan. Sebisa mungkin seluruh fitur dan alur kerja selesai tanpa bug.|
|Dokumen yang akan dipresentasikan|Daftar fitur, use case, activity diagram, ERD dan PDM, flowchart alur kerja, mockup (dibuat setelah aplikasi jadi), panduan instalasi|

### 1.6 Nama sistem dan judul **\[KEPUTUSAN CLAUDE]**

* **Nama sistem (brand): EiraStore.** Singkat, bergaya toko, dan netral untuk banyak game. Nama disimpan di satu pengaturan (`APP\_NAME` di `.env`) sehingga mudah diganti. Alternatif jika ingin diganti: *EiraBoost*, *Arena Joki*, *RankUp Store*.
* **Judul UKOM (sederhana):** *Rancang Bangun Sistem Informasi Layanan Joki Game Berbasis Web (Studi Kasus: EiraStore)*.

### 1.7 Kriteria keberhasilan

1. Seluruh 46 fitur berjalan sesuai alur pada bagian 3 dan aturan pada bagian 5.
2. Satu pesanan dapat berjalan dari pembuatan sampai berstatus `selesai` melalui semua jalur: normal, revisi data, bukti ditolak, kedaluwarsa, laporan ditolak, dan komplain.
3. Tiga tipe harga menghasilkan perhitungan yang benar sesuai contoh pada bagian 4.7 dan 5.2.
4. Password akun game terhapus pada seluruh kondisi yang ditetapkan.
5. Kode dapat dijelaskan oleh pemilik, dan pemilik dapat menemukan serta memperbaiki error yang sengaja dibuat penguji.
6. Seluruh dokumen perancangan sesuai dengan aplikasi yang berjalan.

\---

## 2\. User Roles / Aktor

### 2.1 Ringkasan aktor

|Aktor|Jenis|Cara memperoleh akun|Halaman utama|
|-|-|-|-|
|**Pengunjung (Guest)**|Customer yang belum login|Tanpa akun|Landing page, katalog game, halaman layanan beserta form pesan|
|**Customer**|Pengguna eksternal|Registrasi sendiri lewat form publik|Beranda, riwayat pesanan, detail pesanan|
|**Admin**|Pengelola toko|Dibuat lewat **seeder** (tidak ada form registrasi publik)|Dashboard Admin|
|**Worker**|Pelaksana joki|Dibuat oleh Admin lewat fitur *Tambah Data Worker*|Daftar pesanan yang ditugaskan|
|**Sistem (scheduler)**|Proses latar belakang|Berjalan otomatis|Tidak ada antarmuka|

> \*\*Catatan penting:\*\* Pengunjung dan Customer adalah orang yang sama sebelum dan sesudah login, sehingga di use case diagram hanya ada \*\*tiga aktor\*\*: Customer, Admin, Worker. Scheduler \*\*bukan aktor\*\* use case; ia adalah proses internal sistem (lihat `Activity-Diagram-Sistem-v2.drawio`).

### 2.2 Pengunjung (Guest)

**Tanggung jawab:** menjelajahi toko dan menyiapkan pesanan.

**Boleh:**

* Membuka landing page, melihat promosi, katalog game, dan ulasan terbaru.
* Membuka halaman daftar layanan sebuah game dan halaman layanan beserta daftar harga.
* Mengisi form pesanan dan melihat estimasi harga.
* Registrasi dan login.

**Tidak boleh:** membuat pesanan, melihat riwayat, atau membuka halaman Admin dan Worker. Saat menekan tombol **Pesan**, pengunjung diminta login atau registrasi terlebih dahulu (draf pesanan disimpan sementara di sesi, lihat bagian 5.3).

### 2.3 Customer

**Tanggung jawab:** memesan joki, membayar, memperbaiki data bila diminta, mengonfirmasi hasil, dan memberi ulasan.

**Boleh:**

* Semua yang boleh dilakukan Pengunjung.
* Membuat pesanan, melihat riwayat dan status **pesanannya sendiri**.
* Mengunggah bukti pembayaran untuk pesanannya.
* Merevisi data akun game ketika Admin meminta revisi data.
* Menyetujui hasil pengerjaan atau mengajukan komplain.
* Mengonfirmasi penyelesaian pesanan dan memberi rating serta ulasan.

**Tidak boleh:** melihat pesanan milik customer lain, mengubah pilihan layanan atau harga setelah pesanan dibuat, membatalkan pesanan sendiri, melihat halaman Admin atau Worker.

**Akun:** `role = customer`, `status\_akun = aktif` saat registrasi.

### 2.4 Admin

**Tanggung jawab:** mengelola data master (game, layanan, item layanan, Worker), memverifikasi data dan pembayaran, menugaskan pesanan, memverifikasi laporan Worker, menyampaikan hasil ke customer, menangani revisi dan komplain, serta memantau dashboard dan laporan transaksi.

**Boleh:**

* CRUD data game, layanan joki, dan item layanan.
* CRUD data Worker dan menonaktifkan Worker.
* Melihat dashboard, daftar semua pesanan, dan detail pesanan.
* Menyetujui atau meminta revisi data pemesanan, menyetujui atau menolak bukti pembayaran.
* Menugaskan pesanan ke Worker, melihat laporan Worker, memverifikasi laporan, mengirim pemberitahuan penyelesaian, meminta revisi hasil.
* Melihat laporan transaksi.

**Tidak boleh:** melihat **password akun game** customer (Admin hanya melihat ID akun, username login, nickname, dan server), membuat pesanan sebagai customer.

**Akun:** `role = admin`, dibuat lewat seeder.

### 2.5 Worker

**Tanggung jawab:** mengerjakan pesanan yang ditugaskan dan melaporkan hasilnya dengan foto bukti.

**Boleh:**

* Melihat pesanan yang **ditugaskan kepadanya** saja.
* Melihat data akun game termasuk **password** (selama password belum dihapus) untuk pesanan miliknya.
* Memulai pengerjaan, melaporkan penyelesaian dengan foto bukti, dan mengerjakan revisi.

**Tidak boleh:** melihat pesanan Worker lain, mengubah data pesanan, memverifikasi apa pun, melihat halaman Admin atau Customer.

**Akun:** `role = worker`, dibuat oleh Admin. Akun yang `status\_akun = nonaktif` tidak bisa login.

### 2.6 Sistem (scheduler)

**Tanggung jawab:** mengubah pesanan berstatus `menunggu\_pembayaran` yang melewati `batas\_waktu\_bayar` menjadi `kedaluwarsa`, menghapus password akun game pada pesanan tersebut, dan mencatat perubahan di `riwayat\_status\_pesanan` dengan `diubah\_oleh = NULL` (artinya sistem).

### 2.7 Matriks hak akses data

R = lihat (read), C = buat (create), U = ubah (update), D = hapus (delete). Tanda "—" berarti tidak ada akses.

|Data|Guest|Customer|Admin|Worker|
|-|-|-|-|-|
|users (akun sendiri)|C (registrasi)|— (tidak ada ubah profil)|R sebatas Worker|—|
|users role worker|—|—|C, R, U, nonaktifkan|—|
|games|R (yang aktif)|R (yang aktif)|C, R, U, D|—|
|layanan|R (yang aktif)|R (yang aktif)|C, R, U, D|—|
|layanan\_item|R (yang aktif)|R (yang aktif)|C, R, U, D|—|
|pesanan|—|C, R (miliknya), U terbatas (revisi data akun game)|R semua, U lewat aksi status|R (yang ditugaskan), U lewat aksi status|
|pesanan\_item|—|C (saat membuat pesanan), R miliknya|R|R (yang ditugaskan)|
|pembayaran|—|C, R miliknya|R, U (verifikasi)|—|
|laporan\_penyelesaian|—|R foto bukti pada pesanannya setelah status `menunggu\_konfirmasi`|R, U (verifikasi)|C, R miliknya|
|riwayat\_status\_pesanan|—|R miliknya|R|R (yang ditugaskan)|
|ulasan|R (ulasan terbaru di landing page)|C, R miliknya|R|—|
|password\_akun\_game|—|Mengisi saat memesan dan merevisi data|**Tidak dapat melihat**|R hanya bila pesanan ditugaskan kepadanya dan password belum dihapus|

### 2.8 Aturan autentikasi dan otorisasi

1. Satu halaman login dipakai untuk ketiga role. Setelah login, pengguna diarahkan sesuai role: Admin ke dashboard Admin, Worker ke daftar pesanan Worker, Customer ke halaman tujuan sebelumnya (jika ada) atau beranda. **\[KEPUTUSAN CLAUDE]**
2. Login ditolak jika email atau password salah, atau `status\_akun = nonaktif`.
3. Setiap rute Admin dan Worker dilindungi middleware `role` (lihat bagian 6). Customer yang membuka rute Admin atau Worker mendapat respons 403.
4. Setiap aksi pada pesanan wajib memeriksa **kepemilikan**: Customer hanya pada pesanannya (`customer\_id`), Worker hanya pada pesanan yang ditugaskan (`worker\_id`).
5. Akun Admin dan Worker yang membuka form pemesanan boleh melihat, tetapi tombol **Pesan** hanya berfungsi untuk akun dengan role `customer` (selain itu respons 403 dengan pesan yang jelas).

\---

## 3\. Core Features \& User Flow

### 3.1 Peta fitur (46 fitur unik)

ID fitur dibuat per role: **C** = Customer, **A** = Admin, **W** = Worker. Nomor ID sama dengan nomor halaman pada file `Activity-Diagram-\*-v2.drawio` (contoh: **C-07** = halaman **C07**). Fitur *Login* dan *Logout* dipakai ketiga role sehingga muncul sebagai tiga fitur terpisah (C-02/C-03, A-01/A-02, W-01/W-02) dengan alur yang hampir sama.

**Customer (14 fitur)**

|ID|Fitur|Modul|
|-|-|-|
|C-01|Registrasi Akun|Autentikasi \& Akun|
|C-02|Login|Autentikasi \& Akun|
|C-03|Logout|Autentikasi \& Akun|
|C-04|Lihat Beranda \& Promosi|Beranda \& Katalog|
|C-05|Lihat Katalog Game|Beranda \& Katalog|
|C-06|Lihat Katalog Layanan Joki|Beranda \& Katalog|
|C-07|Buat Pesanan Joki|Pemesanan|
|C-08|Lihat Riwayat \& Status Pesanan|Pemesanan|
|C-09|Unggah Bukti Pembayaran|Pemesanan|
|C-10|Revisi Data Pesanan|Pemesanan|
|C-11|Setujui Hasil Pengerjaan Joki|Penyelesaian Pesanan|
|C-12|Ajukan Komplain Hasil Pengerjaan|Penyelesaian Pesanan|
|C-13|Konfirmasi Penyelesaian Pesanan|Penyelesaian Pesanan|
|C-14|Beri Rating \& Ulasan|Rating \& Laporan|

**Admin (30 fitur)**

|ID|Fitur|Modul|
|-|-|-|
|A-01|Login|Autentikasi \& Akun|
|A-02|Logout|Autentikasi \& Akun|
|A-03|Tambah Data Game|Manajemen Data Game|
|A-04|Lihat Data Game|Manajemen Data Game|
|A-05|Ubah Data Game|Manajemen Data Game|
|A-06|Hapus Data Game|Manajemen Data Game|
|A-07|Tambah Data Layanan Joki|Manajemen Data Layanan Joki|
|A-08|Lihat Data Layanan Joki|Manajemen Data Layanan Joki|
|A-09|Ubah Data Layanan Joki|Manajemen Data Layanan Joki|
|A-10|Hapus Data Layanan Joki|Manajemen Data Layanan Joki|
|A-11|Tambah Data Item Layanan|Manajemen Data Item Layanan|
|A-12|Lihat Data Item Layanan|Manajemen Data Item Layanan|
|A-13|Ubah Data Item Layanan|Manajemen Data Item Layanan|
|A-14|Hapus Data Item Layanan|Manajemen Data Item Layanan|
|A-15|Tambah Data Worker|Manajemen Data Worker|
|A-16|Lihat Data Worker|Manajemen Data Worker|
|A-17|Ubah Data Worker|Manajemen Data Worker|
|A-18|Nonaktifkan Data Worker|Manajemen Data Worker|
|A-19|Lihat Dashboard Pesanan|Verifikasi Pemesanan|
|A-20|Lihat Daftar Pesanan Masuk|Verifikasi Pemesanan|
|A-21|Setujui Data Pemesanan|Verifikasi Pemesanan|
|A-22|Minta Revisi Data Pemesanan|Verifikasi Pemesanan|
|A-23|Setujui Bukti Pembayaran|Verifikasi Pemesanan|
|A-24|Tolak Bukti Pembayaran|Verifikasi Pemesanan|
|A-25|Tugaskan Pesanan ke Worker|Pengerjaan Jokian|
|A-26|Lihat Laporan Penyelesaian Worker|Pengerjaan Jokian|
|A-27|Verifikasi Laporan Penyelesaian Worker|Penyelesaian Pesanan|
|A-28|Kirim Notifikasi Penyelesaian ke Pelanggan|Penyelesaian Pesanan|
|A-29|Minta Revisi Hasil Pengerjaan|Penyelesaian Pesanan|
|A-30|Lihat Laporan Transaksi|Rating \& Laporan|

**Worker (6 fitur)**

|ID|Fitur|Modul|
|-|-|-|
|W-01|Login|Autentikasi \& Akun|
|W-02|Logout|Autentikasi \& Akun|
|W-03|Lihat Pesanan Ditugaskan|Pengerjaan Jokian|
|W-04|Mulai Pengerjaan Pesanan|Pengerjaan Jokian|
|W-05|Laporkan Penyelesaian Pesanan|Pengerjaan Jokian|
|W-06|Revisi Hasil Pengerjaan|Penyelesaian Pesanan|

### 3.2 Alur kerja end-to-end

#### 3.2.1 Alur utama (jalur normal)

|No|Aktor|Aksi|Fitur|Status pesanan setelahnya|
|-|-|-|-|-|
|1|Pengunjung|Membuka landing page, melihat promosi, katalog game, dan ulasan|C-04, C-05|(belum ada pesanan)|
|2|Pengunjung|Memilih game, lalu memilih layanan dan melihat daftar harga|C-06|(belum ada pesanan)|
|3|Pengunjung|Mengisi form: pilihan layanan dan data akun game, melihat estimasi harga|C-07|(belum ada pesanan)|
|4|Pengunjung|Menekan **Pesan**. Jika belum login, draf disimpan di sesi dan ia diarahkan login atau registrasi|C-07, C-01, C-02|(belum ada pesanan)|
|5|Customer|Setelah login, pesanan dibuat (melalui halaman konfirmasi draf). Harga dihitung ulang di server|C-07|`menunggu\_pembayaran` (batas waktu bayar 24 jam)|
|6|Customer|Membayar manual lewat bank atau e-wallet, lalu mengunggah bukti dan memilih metode pembayaran|C-09|`menunggu\_verifikasi`|
|7|Admin|Memeriksa data akun game dan rincian pilihan, lalu menyetujui data|A-20, A-21|`menunggu\_verifikasi` (data disetujui)|
|8|Admin|Memeriksa bukti pembayaran, lalu menyetujuinya. Karena data dan bukti sudah disetujui, status berubah|A-23|`siap\_ditugaskan`|
|9|Admin|Memilih Worker aktif dan menugaskan pesanan|A-25|`diproses`|
|10|Worker|Membuka pesanan, melihat data akun beserta password, menekan **Mulai Pengerjaan**|W-03, W-04|`sedang\_dikerjakan`|
|11|Worker|Selesai mengerjakan, mengunggah foto bukti dan melapor|W-05|`laporan\_masuk`|
|12|Admin|Memeriksa laporan dan foto bukti, lalu memverifikasi|A-26, A-27|`worker\_selesai`|
|13|Admin|Mengirim pemberitahuan penyelesaian ke customer|A-28|`menunggu\_konfirmasi`|
|14|Customer|Mengecek hasil langsung di dalam game, lalu menyetujui hasil. Password akun game dihapus|C-11|`hasil\_disetujui`|
|15|Customer|Menekan **Konfirmasi Selesai**|C-13|`selesai`|
|16|Customer|Memberi rating dan ulasan|C-14|`selesai` (ulasan tersimpan)|

#### 3.2.2 Jalur alternatif

|Kode|Kondisi|Alur|Hasil akhir|
|-|-|-|-|
|**V1 Data salah**|Admin menemukan data akun game tidak sesuai|Admin menekan **Minta Revisi Data** dan menulis catatan (A-22) → status `perlu\_revisi\_data` → Customer memperbaiki **data akun game saja**, tanpa bayar ulang (C-10) → status kembali `menunggu\_verifikasi` → Admin memeriksa ulang|Kembali ke jalur utama langkah 7|
|**V2 Bukti palsu**|Admin menilai bukti pembayaran tidak sah|Admin menekan **Tolak Bukti** dan menulis alasan (A-24)|Status `dibatalkan` (terminal), password akun game dihapus|
|**V3 Kedaluwarsa**|Customer tidak mengunggah bukti dalam 1x24 jam|Scheduler mengubah status menjadi `kedaluwarsa` dan menghapus password akun game|Status `kedaluwarsa` (terminal). Customer membuat pesanan baru|
|**V4 Laporan ditolak**|Admin menilai laporan atau foto Worker tidak sesuai|Admin menekan **Minta Revisi Hasil** dan menulis catatan (A-29) → status `perlu\_revisi\_hasil` → Worker memperbaiki dan melapor ulang (W-06) → `laporan\_masuk`|Kembali ke langkah 12|
|**V5 Komplain**|Customer menilai hasil belum sesuai|Customer menekan **Ajukan Komplain** dan menulis alasan (C-12) → status `dikomplain` → Admin menekan **Minta Revisi Hasil** (A-29) → `perlu\_revisi\_hasil` → Worker memperbaiki (W-06) → `laporan\_masuk`|Kembali ke langkah 12|

Jalur V4 dan V5 dapat berulang sampai customer puas. Tidak ada batas jumlah revisi.

### 3.3 Spesifikasi landing page dan halaman publik

#### 3.3.1 Peta halaman publik

|Rute|Halaman|Login|Keterangan|
|-|-|-|-|
|`/`|Landing page|Tidak perlu|Promosi, katalog game, ulasan, info sosial media|
|`/game/{game}`|Daftar layanan sebuah game|Tidak perlu|Menampilkan layanan aktif milik game tersebut|
|`/layanan/{layanan}`|Halaman layanan|Tidak perlu|Daftar harga dan form pesanan|
|`/login`, `/register`|Login dan registrasi|Tamu|Halaman bawaan Breeze yang disesuaikan|

> Rute memakai \*\*ID\*\* (bukan slug) karena tabel `games` dan `layanan` tidak memiliki kolom slug.

#### 3.3.2 Susunan landing page (`/`)

Urutan dari atas ke bawah **\[FINAL]**:

1. **Navbar:** logo dan nama EiraStore, tautan *Beranda*, *Katalog* (anchor ke bagian katalog), *Riwayat Pesanan* (hanya untuk Customer yang login), tombol *Login* dan *Daftar* untuk tamu, atau menu pengguna (Logout) bila sudah login.
2. **Bagian promosi (hero):** judul, subjudul, tombol *Lihat Katalog*, dan beberapa kartu promosi. **Promosi berupa konten statis** yang ditulis langsung di Blade atau berkas config (tidak disimpan di database). Konten promosi awal berupa contoh yang boleh diganti pemilik.
3. **Katalog game:** kartu game aktif (gambar, nama, jumlah layanan aktif). Menekan kartu membuka `/game/{game}`.
4. **Ulasan pelanggan:** rata-rata rating dan lima ulasan terbaru.

   * **Layar lebar (desktop):** dua kolom. Katalog di kolom utama, ulasan sebagai **sidebar di sisi kanan**.
   * **Layar kecil (HP):** sidebar **dipindahkan ke bawah katalog** dan disederhanakan menjadi rata-rata rating dan tiga ulasan terbaru. **\[KEPUTUSAN CLAUDE]** (pemilik ragu soal tata letak responsif bagian ini).
5. **Informasi dan sosial media:** keterangan singkat toko dan tautan sosial media (Instagram, TikTok, WhatsApp). Isi awal berupa contoh yang dapat diganti.
6. **Footer.**
7. **\[OPSIONAL]** bagian *Cara Pesan* (empat langkah) dan *FAQ* singkat, bila waktu cukup.

#### 3.3.3 Halaman daftar layanan (`/game/{game}`)

Menampilkan nama dan gambar game, lalu kartu layanan aktif (nama layanan, deskripsi, perkiraan waktu). Menekan kartu membuka `/layanan/{layanan}`. Game atau layanan yang `aktif = false` tidak tampil dan tidak dapat dibuka publik (respons 404).

#### 3.3.4 Halaman layanan (`/layanan/{layanan}`)

Terdiri dari dua bagian:

1. **Daftar harga**, tampilannya mengikuti `tipe\_harga`:

   * `tangga`: tabel tingkatan berurutan dengan kolom nama tingkat, jumlah langkah (bintang), dan harga per langkah.
   * `progres`: daftar wilayah (atau region beserta wilayahnya) dengan harga dasar.
   * `pilihan`: daftar item dikelompokkan per `kategori` beserta harga.
   * Item tambahan (jenis `tambahan`) ditampilkan di bagian terpisah bila ada.
2. **Form pesanan** (spesifikasi lengkap pada fitur C-07). Form dapat diisi tanpa login.

### 3.4 Spesifikasi fitur Customer

#### C-01 Registrasi Akun

* **Tujuan:** customer baru membuat akun.
* **Prakondisi:** belum login.
* **Alur:**

  1. Pengunjung membuka `/register` (dari navbar atau diarahkan saat menekan **Pesan**).
  2. Mengisi nama, email, nomor HP, password, dan konfirmasi password.
  3. Menekan **Daftar**.
  4. Server memvalidasi input.
  5. Server membuat akun dengan `role = customer` dan `status\_akun = aktif`; password di-hash.
  6. Pengguna login otomatis dan diarahkan ke halaman tujuan sebelumnya (jika ada draf pesanan) atau beranda.
* **Validasi:** nama wajib (maks 100); email wajib, format email, unik di `users`, maks 100; no\_hp wajib, hanya angka 10 sampai 15 digit; password wajib, minimal 8 karakter, sama dengan konfirmasi.
* **Hasil:** satu baris baru di `users`. Tidak ada verifikasi email.
* **Referensi:** halaman C01 pada activity diagram.

#### C-02 Login

* **Tujuan:** customer masuk ke akunnya.
* **Prakondisi:** sudah punya akun.
* **Alur:** membuka `/login` → mengisi email dan password → server memeriksa kecocokan dan `status\_akun = aktif` → sesi dibuat → diarahkan ke halaman tujuan sebelumnya (`intended`) atau beranda.
* **Validasi:** email dan password wajib. Percobaan gagal dibatasi (bawaan Breeze). Pesan kesalahan bersifat umum (tidak membedakan email salah atau password salah).
* **Hasil:** sesi login aktif.
* **Referensi:** C02.

#### C-03 Logout

* **Alur:** menekan **Logout** (permintaan POST dengan token CSRF) → sesi dihapus → diarahkan ke halaman login.
* **Referensi:** C03.

#### C-04 Lihat Beranda \& Promosi

* **Tujuan:** pengunjung melihat promosi dan gambaran toko.
* **Prakondisi:** tidak ada (publik).
* **Alur:** membuka `/` → server mengambil game aktif beserta jumlah layanan aktif dan ulasan terbaru → halaman menampilkan susunan pada bagian 3.3.2.
* **Aturan:** bila data belum ada, tampil pesan *konten belum tersedia* pada bagian terkait, bukan error.
* **Referensi:** C04.

#### C-05 Lihat Katalog Game

* **Tujuan:** melihat game yang dilayani.
* **Alur:** bagian katalog pada landing page memuat kartu game dengan `aktif = true`. Menekan kartu membuka `/game/{game}`.
* **Aturan:** game nonaktif tidak tampil.
* **Referensi:** C05.

#### C-06 Lihat Katalog Layanan Joki

* **Tujuan:** melihat layanan joki sebuah game beserta daftar harga.
* **Alur:** dari kartu game membuka `/game/{game}` → memilih layanan → membuka `/layanan/{layanan}` yang menampilkan daftar harga sesuai tipe (bagian 3.3.4).
* **Aturan:** hanya layanan dan item dengan `aktif = true` yang tampil. Item diurutkan menurut `urutan`.
* **Referensi:** C06.

#### C-07 Buat Pesanan Joki

* **Tujuan:** customer membuat pesanan dengan harga yang dihitung otomatis.
* **Prakondisi:** layanan aktif. **Login tidak diperlukan untuk mengisi form**, tetapi diperlukan untuk menyelesaikan pesanan.
* **Isi form**

  * **Bagian A, pilihan layanan (dinamis menurut `tipe\_harga`):**

    * `tangga`: pilih tingkat awal dan isi nilai awal (bintang), pilih tingkat tujuan dan isi nilai tujuan; item tambahan opsional (misalnya hero).
    * `progres`: pilih satu item tingkat atas (wilayah, atau region). Jika punya wilayah turunan, isi **progres persen (0 sampai 100)** untuk tiap wilayah; jika tidak, isi satu progres.
    * `pilihan`: centang satu atau lebih item (dikelompokkan per kategori). Item yang memiliki satuan (misalnya "hari") meminta isian jumlah. Item tambahan opsional.
  * **Bagian B, data akun game:** ID akun game (wajib), login via (wajib, pilihan: Moonton, Facebook, Google, Apple ID, HoYoverse, Lainnya), username atau email login (wajib), password akun game (wajib), nickname (opsional), server (opsional), Zone ID (opsional), catatan login seperti 2FA (opsional), catatan untuk Worker (opsional).
  * **Panel estimasi harga:** menampilkan rincian dan total yang diperbarui otomatis saat pilihan berubah (dengan Alpine.js).
* **Alur:**

  1. Pengunjung membuka `/layanan/{layanan}` dari katalog dan mengisi form.
  2. Menekan **Pesan**. Browser memvalidasi kelengkapan isian wajib.
  3. **Jika sudah login (role customer):** form dikirim ke server dan pesanan langsung dibuat (langkah 6).
  4. **Jika belum login:** server memvalidasi isian, menyimpan **draf pesanan** di sesi (password akun game disimpan dalam keadaan terenkripsi), lalu mengarahkan ke halaman login atau registrasi.
  5. Setelah login atau registrasi, pengguna dikembalikan ke halaman **Konfirmasi Pesanan** yang menampilkan ringkasan draf (tanpa menampilkan password) dan tombol **Konfirmasi Pesanan**. Menekan tombol ini mengirim draf ke server. *(Di activity diagram C07 langkah ini diringkas dalam satu simpul "Mengirim pesanan ke server".)*
  6. Server **menghitung ulang harga dari data di database** (harga dari browser tidak dipercaya) dan memvalidasi pilihan.
  7. Jika valid, server menyimpan `pesanan` (status `menunggu\_pembayaran`, `batas\_waktu\_bayar` = sekarang + 24 jam, kode pesanan dibuat), menyimpan `pesanan\_item` (nama, harga satuan, dan subtotal disalin), serta mencatat entri pertama di `riwayat\_status\_pesanan`.
  8. Customer diarahkan ke detail pesanan dengan pesan berhasil dan informasi pembayaran.
* **Validasi dan aturan:** semua aturan hitung dan validasi pilihan ada di bagian 5.2. Pesanan hanya dapat dibuat oleh akun `role = customer`. Pilihan yang tidak valid (misalnya item yang sudah dinonaktifkan, tujuan tidak lebih tinggi dari awal, progres di luar 0 sampai 100) menampilkan pesan kesalahan dan form dibuka kembali dengan isian yang sudah dimasukkan.
* **Hasil:** baris baru di `pesanan` dan `pesanan\_item` serta satu entri di `riwayat\_status\_pesanan`.
* **Referensi:** C07.

#### C-08 Lihat Riwayat \& Status Pesanan

* **Tujuan:** customer memantau pesanannya.
* **Prakondisi:** login sebagai customer.
* **Alur:** membuka **Riwayat Pesanan** (`/riwayat`) → daftar pesanan miliknya (kode, game dan layanan, total, status, batas waktu bayar bila masih menunggu pembayaran) → memilih satu pesanan untuk membuka detail.
* **Isi halaman detail (`/pesanan/{pesanan}`):**

  1. Ringkasan: kode, game, layanan, tanggal, total harga.
  2. Rincian pilihan (dari `pesanan\_item`): nama item, nilai, subtotal.
  3. Status dengan penjelasan singkat dan tombol aksi sesuai status (lihat tabel).
  4. Bila `menunggu\_pembayaran`: batas waktu bayar dan daftar bank dan e-wallet tujuan beserta total tagihan.
  5. Data pembayaran (metode dan status) bila sudah ada.
  6. **Timeline** perubahan status beserta catatan (dari `riwayat\_status\_pesanan`), termasuk catatan revisi dari Admin.
  7. Foto bukti pengerjaan (dari laporan terverifikasi) saat status `menunggu\_konfirmasi`, `dikomplain`, `hasil\_disetujui`, atau `selesai`.
* **Tombol aksi menurut status:**

|Status|Tombol yang tampil untuk Customer|
|-|-|
|`menunggu\_pembayaran`|Unggah Bukti Pembayaran (C-09)|
|`perlu\_revisi\_data`|Revisi Data Pesanan (C-10)|
|`menunggu\_konfirmasi`|Setujui Hasil (C-11) dan Ajukan Komplain (C-12)|
|`hasil\_disetujui`|Konfirmasi Selesai (C-13)|
|`selesai` (belum ada ulasan)|Beri Rating \& Ulasan (C-14)|
|status lain|Tidak ada tombol aksi|

* **Aturan:** customer hanya dapat membuka pesanan miliknya (`customer\_id` = id login), selain itu respons 403.
* **Referensi:** C08.

#### C-09 Unggah Bukti Pembayaran

* **Tujuan:** customer membuktikan pembayaran manual.
* **Prakondisi:** pesanan miliknya berstatus `menunggu\_pembayaran` dan `batas\_waktu\_bayar` belum lewat.
* **Alur:**

  1. Membuka detail pesanan; server memeriksa status. Jika bukan `menunggu\_pembayaran` (misalnya sudah `kedaluwarsa`), form **tidak ditampilkan**, hanya status dan arahan membuat pesanan baru.
  2. Form menampilkan total tagihan, daftar bank dan e-wallet tujuan, dan batas waktu.
  3. Customer memilih **metode pembayaran** (nama bank atau e-wallet yang dipakai) dan memilih file bukti, lalu menekan **Kirim**.
  4. Browser dan server memvalidasi metode dan file.
  5. Server memeriksa ulang bahwa status masih `menunggu\_pembayaran` (pengaman bila status berubah antara membuka dan mengirim).
  6. Server menyimpan file di penyimpanan privat, membuat baris `pembayaran` (`status = menunggu\_verifikasi`), mengubah status pesanan menjadi `menunggu\_verifikasi`, dan mencatat riwayat.
* **Validasi:** metode wajib dan harus salah satu dari daftar di konfigurasi; file wajib, format JPG, JPEG, PNG, atau WebP, maksimal 2 MB; satu file per pesanan.
* **Aturan penting:** pesanan yang sudah kedaluwarsa **tidak dapat dibayar**. Customer harus membuat pesanan baru.
* **Hasil:** baris `pembayaran`, status `menunggu\_verifikasi`.
* **Referensi:** C09.

#### C-10 Revisi Data Pesanan

* **Tujuan:** customer memperbaiki data akun game yang diminta Admin tanpa membayar ulang.
* **Prakondisi:** pesanan miliknya berstatus `perlu\_revisi\_data`.
* **Alur:** membuka detail pesanan → membaca catatan revisi Admin → membuka form revisi (terisi data lama) → memperbaiki → menekan **Simpan** → validasi → server memperbarui data → status kembali `menunggu\_verifikasi`, `data\_disetujui = false`, riwayat dicatat.
* **Yang boleh diubah:** ID akun game, login via, username atau email login, password (bila dikosongkan, password lama tetap dipakai), nickname, server, Zone ID, catatan login, catatan untuk Worker.
* **Yang tidak boleh diubah:** layanan, pilihan layanan, dan harga. Jika kesalahan ada pada pilihan layanan sehingga harga berubah, pesanan tidak dapat direvisi; Admin membatalkannya dan customer membuat pesanan baru. **\[KEPUTUSAN CLAUDE]**
* **Hasil:** data akun game diperbarui, status `menunggu\_verifikasi`. Status pembayaran tidak berubah.
* **Referensi:** C10.

#### C-11 Setujui Hasil Pengerjaan Joki

* **Tujuan:** customer menyatakan hasil sudah sesuai.
* **Prakondisi:** pesanan miliknya berstatus `menunggu\_konfirmasi`.
* **Alur:** membuka detail pesanan → melihat foto bukti pengerjaan → mengecek hasil langsung di dalam game → jika sesuai menekan **Setujui Hasil** (jika tidak sesuai, memilih **Ajukan Komplain**, C-12) → server mengubah status menjadi `hasil\_disetujui`, **menghapus password akun game**, dan mencatat riwayat.
* **Hasil:** status `hasil\_disetujui`, `password\_akun\_game = NULL`, `kredensial\_dihapus\_at` terisi.
* **Referensi:** C11.

#### C-12 Ajukan Komplain Hasil Pengerjaan

* **Tujuan:** customer melaporkan hasil yang belum sesuai.
* **Prakondisi:** pesanan miliknya berstatus `menunggu\_konfirmasi`.
* **Alur:** menekan **Ajukan Komplain** → mengisi alasan → **Kirim** → validasi → server mengubah status menjadi `dikomplain` dan menyimpan alasan sebagai catatan di `riwayat\_status\_pesanan`.
* **Validasi:** alasan wajib, minimal 10 dan maksimal 1000 karakter.
* **Hasil:** status `dikomplain`. Password akun game **belum dihapus** karena Worker mungkin harus memperbaiki.
* **Referensi:** C12.

#### C-13 Konfirmasi Penyelesaian Pesanan

* **Tujuan:** customer menutup pesanan secara final.
* **Prakondisi:** pesanan miliknya berstatus `hasil\_disetujui`.
* **Alur:** menekan **Konfirmasi Selesai** → dialog konfirmasi (*Yakin pesanan selesai?*) → jika **Ya**, server mengubah status menjadi `selesai`, mengisi `selesai\_at`, memastikan password akun game sudah terhapus, dan mencatat riwayat; jika **Tidak**, dialog ditutup.
* **Hasil:** status `selesai`. Form rating muncul.
* **Referensi:** C13.

#### C-14 Beri Rating \& Ulasan

* **Tujuan:** customer menilai layanan.
* **Prakondisi:** pesanan miliknya berstatus `selesai` dan belum memiliki ulasan.
* **Alur:** membuka detail pesanan → mengisi rating dan komentar → **Kirim** → validasi → server menyimpan ulasan.
* **Validasi:** rating wajib bilangan bulat 1 sampai 5; komentar opsional, maksimal 500 karakter. Satu ulasan per pesanan.
* **Hasil:** baris `ulasan`. Ulasan tampil di sidebar landing page.
* **Referensi:** C14.

### 3.5 Spesifikasi fitur Admin

Seluruh fitur Admin menuntut login dengan `role = admin`. Rute berawalan `/admin`.

**Tombol aksi Admin pada detail pesanan menurut status**

|Status pesanan|Aksi yang tersedia untuk Admin|
|-|-|
|`menunggu\_verifikasi`|Setujui Data (A-21) bila `data\_disetujui = false`; Minta Revisi Data (A-22); Setujui Bukti (A-23) dan Tolak Bukti (A-24) bila `pembayaran.status = menunggu\_verifikasi`|
|`siap\_ditugaskan`|Tugaskan ke Worker (A-25)|
|`laporan\_masuk`|Verifikasi Laporan (A-27) atau Minta Revisi Hasil (A-29)|
|`worker\_selesai`|Kirim Notifikasi Penyelesaian (A-28)|
|`dikomplain`|Minta Revisi Hasil (A-29)|
|status lain|Hanya melihat|

#### A-01 Login dan A-02 Logout

Sama seperti C-02 dan C-03. Setelah login, Admin diarahkan ke `/admin/dashboard`. Akun Admin dibuat lewat seeder dan tidak ada registrasi publik untuk Admin.

#### Modul Manajemen Data Game

**A-03 Tambah Data Game**

* **Alur:** menu *Data Game* → **Tambah** → form → **Simpan** → validasi → server menyimpan gambar (disk publik, folder `games`) dan baris `games` → pesan berhasil → kembali ke daftar.
* **Field:** `nama\_game`, `deskripsi`, `gambar`, `aktif`.
* **Validasi:** `nama\_game` wajib, maks 100, **unik**; `deskripsi` opsional; `gambar` wajib saat tambah, JPG, PNG, atau WebP, maks 2 MB; `aktif` default aktif.
* **Referensi:** A03.

**A-04 Lihat Data Game**

* **Alur:** menu *Data Game* → server mengambil seluruh game (aktif dan nonaktif) → tabel (gambar, nama, jumlah layanan, status aktif, aksi) dengan pagination 10 baris. Bila kosong tampil pesan *data game kosong*.
* **Referensi:** A04.

**A-05 Ubah Data Game**

* **Alur:** pilih data → **Ubah** → form terisi → ubah → **Simpan** → validasi → server memperbarui.
* **Aturan:** nama tetap harus unik (mengabaikan baris sendiri); gambar opsional (bila tidak diganti, gambar lama dipertahankan; bila diganti, berkas lama dihapus). Mengubah `aktif` menjadi nonaktif **menyembunyikan game beserta seluruh layanannya** dari katalog publik; data layanan tetap tersimpan.
* **Referensi:** A05.

**A-06 Hapus Data Game**

* **Alur:** pilih data → **Hapus** → dialog konfirmasi → **Ya** → server memeriksa relasi.
* **Aturan:** **ditolak** bila game masih memiliki layanan (aktif maupun nonaktif), dengan pesan agar game dinonaktifkan lewat **Ubah**. Bila tidak punya layanan, baris dan berkas gambar dihapus.
* **Referensi:** A06.

#### Modul Manajemen Data Layanan Joki

**A-07 Tambah Data Layanan Joki**

* **Alur:** menu *Data Layanan Joki* → **Tambah** → form → **Simpan** → validasi → simpan.
* **Field:** `game\_id`, `nama\_layanan`, `deskripsi`, `tipe\_harga`, `estimasi\_waktu`, `aktif`.
* **Validasi:** `game\_id` wajib dan ada di `games`; `nama\_layanan` wajib, maks 100, **unik per game**; `tipe\_harga` wajib, salah satu dari `tangga`, `progres`, `pilihan` (form menampilkan penjelasan singkat tiap tipe); `estimasi\_waktu` opsional (teks, maks 50); `aktif` default aktif.
* **Referensi:** A07.

**A-08 Lihat Data Layanan Joki**

* **Alur:** menu → tabel (game, nama layanan, tipe harga, jumlah item, aktif, aksi *Item*, *Ubah*, *Hapus*), dapat difilter per game, pagination.
* **Referensi:** A08.

**A-09 Ubah Data Layanan Joki**

* **Aturan terkunci:** `tipe\_harga` **tidak dapat diubah** bila layanan sudah memiliki item atau pesanan, dan `game\_id` **tidak dapat diubah** bila layanan sudah memiliki pesanan. Pada kondisi itu field dikunci di form dan ditolak oleh server. Nama layanan tetap harus unik per game.
* **Referensi:** A09.

**A-10 Hapus Data Layanan Joki**

* **Aturan:** **ditolak** bila layanan sudah punya pesanan (pesan: nonaktifkan lewat **Ubah**). Bila tidak punya pesanan, layanan beserta seluruh item layanannya dihapus (cascade).
* **Referensi:** A10.

#### Modul Manajemen Data Item Layanan

Item dikelola **di dalam sebuah layanan** (`/admin/layanan/{layanan}/item`). Aturan isian bergantung pada `tipe\_harga` layanan induknya:

|Tipe harga layanan|Jenis `utama`|Jenis `tambahan`|
|-|-|-|
|`tangga`|`nilai\_maks` **wajib** (≥ 1) = jumlah langkah per tingkat; `harga` = harga **per langkah**; `urutan` **unik** di antara item utama; `parent\_id` kosong|Opsional; `parent\_id` kosong|
|`progres`|`harga` = harga dasar 100%; `nilai\_maks` kosong; `parent\_id` boleh diisi **hanya satu tingkat** (wilayah di bawah region); item region (yang punya anak) hanya penampung, `harga` = 0|Opsional; `parent\_id` kosong|
|`pilihan`|`harga` per item; `parent\_id` kosong; `satuan` dan `nilai\_maks` boleh diisi **bersama** bila item bisa dipesan dalam jumlah (misalnya "hari")|Opsional; `satuan` dan `nilai\_maks` boleh untuk jumlah|

**A-11 Tambah Data Item Layanan**

* **Field:** `jenis` (`utama` atau `tambahan`), `kategori` (opsional, misalnya *Archon Quest*, *Basic*, *Hidden*), `nama`, `urutan`, `harga`, `nilai\_maks`, `satuan`, `parent\_id`, `aktif`.
* **Alur:** dari daftar layanan memilih **Item** → **Tambah Item** → form menyesuaikan tipe harga layanan → **Simpan** → validasi dasar (browser dan server) → server memeriksa **aturan tipe harga** pada tabel di atas → menyimpan.
* **Validasi dasar:** `nama` wajib maks 150; `urutan` bilangan bulat ≥ 0; `harga` ≥ 0; `nilai\_maks` bilangan bulat ≥ 1 bila diisi; `parent\_id` harus item `utama` milik layanan yang sama.
* **Referensi:** A11.

**A-12 Lihat Data Item Layanan**

* **Alur:** dari daftar layanan memilih **Item** → tabel item (urutan, jenis, kategori, nama, induk, harga, nilai maks, satuan, aktif), diurutkan menurut jenis lalu `urutan`. Bila kosong tampil pesan.
* **Referensi:** A12.

**A-13 Ubah Data Item Layanan**

* **Aturan:** sama dengan A-11. Perubahan harga atau nilai hanya mempengaruhi pesanan **berikutnya**, tidak mengubah pesanan lama (karena harga sudah disalin ke `pesanan\_item`).
* **Referensi:** A13.

**A-14 Hapus Data Item Layanan**

* **Aturan:** dialog konfirmasi. Item yang dihapus **beserta item anaknya** dihapus. Pesanan lama tetap utuh: `pesanan\_item.layanan\_item\_id` menjadi `NULL`, sedangkan nama dan harga yang sudah disalin tetap tersimpan. Tidak ada pemblokiran; untuk menyembunyikan item tanpa menghapus, gunakan `aktif = false` lewat **Ubah**.
* **Referensi:** A14.

#### Modul Manajemen Data Worker

**A-15 Tambah Data Worker**

* **Field:** `nama`, `email`, `no\_hp`, `password`, konfirmasi password.
* **Validasi:** sama seperti registrasi (email unik di `users`; password minimal 8 karakter).
* **Hasil:** akun baru dengan `role = worker`, `status\_akun = aktif`, password di-hash.
* **Referensi:** A15.

**A-16 Lihat Data Worker**

* **Alur:** tabel Worker (nama, email, no HP, status akun, jumlah pesanan aktif, aksi). Bila kosong tampil pesan.
* **Referensi:** A16.

**A-17 Ubah Data Worker**

* **Aturan:** email tetap unik (mengabaikan baris sendiri); password opsional (kosong berarti tidak diubah); `status\_akun` dapat diubah dan **menonaktifkan memakai aturan yang sama dengan A-18**.
* **Referensi:** A17.

**A-18 Nonaktifkan Data Worker**

* **Alur:** pilih Worker → **Nonaktifkan** → dialog konfirmasi → **Ya** → server memeriksa pesanan aktif.
* **Aturan:** **ditolak** bila Worker masih memiliki pesanan aktif, yaitu pesanan dengan `worker\_id` Worker tersebut dan status salah satu dari `diproses`, `sedang\_dikerjakan`, `laporan\_masuk`, `worker\_selesai`, `menunggu\_konfirmasi`, `dikomplain`, `perlu\_revisi\_hasil`. Bila lolos, `status\_akun = nonaktif`: Worker tidak dapat login, riwayat pekerjaannya tetap tersimpan. Tidak ada fitur *hapus Worker*.
* **Catatan keterbatasan:** karena **tugaskan ulang** belum ada, Worker yang masih punya pesanan aktif baru dapat dinonaktifkan setelah pesanannya selesai.
* **Referensi:** A18.

#### Modul Verifikasi Pemesanan

**A-19 Lihat Dashboard Pesanan**

* **Alur:** membuka `/admin/dashboard` → server menghitung jumlah pesanan per status → halaman menampilkan **kartu jumlah** per status penting: menunggu verifikasi, perlu revisi data, siap ditugaskan, diproses, sedang dikerjakan, laporan masuk, worker selesai, menunggu konfirmasi, dikomplain, perlu revisi hasil, dan selesai. Setiap kartu menautkan ke daftar pesanan terfilter. Grafik tidak dibuat.
* **Referensi:** A19.

**A-20 Lihat Daftar Pesanan Masuk**

* **Alur:** menu *Pesanan* → tabel pesanan (kode, customer, game dan layanan, total, status, tanggal) dengan filter status dan pencarian kode, pagination. **Tampilan awal** menyaring pesanan yang butuh tindakan Admin (`menunggu\_verifikasi`, `siap\_ditugaskan`, `laporan\_masuk`, `worker\_selesai`, `dikomplain`); filter status dapat menampilkan semua status.
* **Detail pesanan (Admin):** data customer, **data akun game** (ID akun, login via, username, nickname, server, Zone ID, catatan; **password tidak ditampilkan**), rincian pilihan (`pesanan\_item`), data pembayaran (metode, status, tautan bukti lewat rute berotorisasi), laporan Worker, timeline riwayat, dan tombol aksi menurut tabel status di atas.
* **Referensi:** A20.

**A-21 Setujui Data Pemesanan**

* **Prakondisi:** status `menunggu\_verifikasi` dan `data\_disetujui = false`.
* **Alur:** memeriksa data akun game dan rincian pilihan → data sesuai → **Setujui Data** → server mengisi `data\_disetujui = true` dan mencatat riwayat. **Jika pembayaran sudah disetujui** (`pembayaran.status = disetujui`), status berubah menjadi `siap\_ditugaskan`; jika belum, status tetap `menunggu\_verifikasi`.
* **Hasil:** `data\_disetujui = true`; riwayat tercatat (status lama dan baru boleh sama, catatan: *Data pemesanan disetujui*).
* **Referensi:** A21.

**A-22 Minta Revisi Data Pemesanan**

* **Prakondisi:** status `menunggu\_verifikasi`.
* **Alur:** **Minta Revisi Data** → menulis catatan revisi → **Kirim** → validasi → status `perlu\_revisi\_data`, `data\_disetujui = false`, catatan disimpan di riwayat.
* **Validasi:** catatan wajib, minimal 5 dan maksimal 1000 karakter.
* **Referensi:** A22.

**A-23 Setujui Bukti Pembayaran**

* **Prakondisi:** status `menunggu\_verifikasi` dan `pembayaran.status = menunggu\_verifikasi`.
* **Alur:** membuka bukti → memeriksa keaslian dan kesesuaian nominal terhadap `total\_harga` dan metode → **Setujui Bukti** → server mengisi `pembayaran.status = disetujui`, `diverifikasi\_oleh`, `diverifikasi\_at`. **Jika data pemesanan sudah disetujui** (`data\_disetujui = true`), status berubah menjadi `siap\_ditugaskan`; jika belum, status tetap `menunggu\_verifikasi`.
* **Referensi:** A23.

**A-24 Tolak Bukti Pembayaran**

* **Prakondisi:** status `menunggu\_verifikasi` dan `pembayaran.status = menunggu\_verifikasi`.
* **Alur:** **Tolak Bukti** → menulis alasan → **Kirim** → server mengisi `pembayaran.status = ditolak`, mengubah status pesanan menjadi `dibatalkan`, **menghapus password akun game**, dan mencatat alasan di riwayat.
* **Validasi:** alasan wajib, minimal 5 dan maksimal 1000 karakter.
* **Hasil:** status `dibatalkan` (terminal).
* **Referensi:** A24.

#### Modul Pengerjaan dan Penyelesaian

**A-25 Tugaskan Pesanan ke Worker**

* **Prakondisi:** status `siap\_ditugaskan`.
* **Alur:** membuka pesanan → server mengambil daftar Worker dengan `status\_akun = aktif` → bila tidak ada, tampil pesan *belum ada Worker aktif* → Admin memilih Worker dan menekan **Tugaskan** → server mengisi `worker\_id`, mengubah status menjadi `diproses`, dan mencatat riwayat (catatan: *Ditugaskan ke \[nama Worker]*).
* **Referensi:** A25.

**A-26 Lihat Laporan Penyelesaian Worker**

* **Alur:** menu *Laporan Worker* → tabel laporan (pesanan, Worker, waktu, status laporan, foto bukti) dengan filter status laporan (tampilan awal: `menunggu\_verifikasi`). Bila kosong tampil pesan.
* **Referensi:** A26.

**A-27 Verifikasi Laporan Penyelesaian Worker**

* **Prakondisi:** pesanan berstatus `laporan\_masuk` dan laporan terbarunya berstatus `menunggu\_verifikasi`.
* **Alur:** membuka laporan dan foto bukti → bila sesuai menekan **Verifikasi Laporan** → server mengisi `laporan\_penyelesaian.status = terverifikasi`, `diverifikasi\_oleh`, `diverifikasi\_at`, dan mengubah status pesanan menjadi `worker\_selesai` (status internal). Bila tidak sesuai, Admin memilih **Minta Revisi Hasil** (A-29).
* **Referensi:** A27.

**A-28 Kirim Notifikasi Penyelesaian ke Pelanggan**

* **Prakondisi:** status `worker\_selesai`.
* **Alur:** membuka pesanan → **Kirim Notifikasi Penyelesaian** (boleh menyertakan pesan singkat) → server mengubah status menjadi `menunggu\_konfirmasi` dan mencatat pesan di riwayat. Customer melihat perubahan ini di detail pesanannya (tidak ada inbox notifikasi terpisah).
* **Referensi:** A28.

**A-29 Minta Revisi Hasil Pengerjaan**

* **Prakondisi:** status `dikomplain` (jalur komplain) **atau** `laporan\_masuk` (jalur laporan Worker ditolak).
* **Alur:** membuka pesanan → membaca komplain atau memeriksa laporan → menulis catatan revisi untuk Worker → **Minta Revisi** → server mengubah status menjadi `perlu\_revisi\_hasil` dan menyimpan catatan di riwayat. Pada jalur `laporan\_masuk`, status laporan Worker menjadi `ditolak`.
* **Validasi:** catatan wajib, minimal 5 dan maksimal 1000 karakter.
* **Referensi:** A29.

**A-30 Lihat Laporan Transaksi**

* **Alur:** menu *Laporan Transaksi* → memilih periode (tanggal mulai dan tanggal akhir) → **Tampilkan** → validasi (tanggal akhir tidak boleh sebelum tanggal mulai) → server mengambil pesanan berstatus `selesai` dengan `selesai\_at` pada periode tersebut → menampilkan tabel (kode, tanggal selesai, customer, game dan layanan, Worker, total) disertai **jumlah pesanan dan total pendapatan**. Tombol **Cetak** memakai fungsi cetak browser dengan tampilan cetak yang rapi. Bila tidak ada data, tampil pesan *tidak ada transaksi*.
* **Referensi:** A30.

### 3.6 Spesifikasi fitur Worker

Seluruh fitur Worker menuntut login dengan `role = worker` dan `status\_akun = aktif`. Rute berawalan `/worker`. Worker hanya dapat membuka pesanan dengan `worker\_id` miliknya.

#### W-01 Login dan W-02 Logout

Sama seperti C-02 dan C-03. Tambahan: akun Worker dengan `status\_akun = nonaktif` ditolak saat login (pesan: akun dinonaktifkan). Setelah login, Worker diarahkan ke `/worker/pesanan`.

#### W-03 Lihat Pesanan Ditugaskan

* **Alur:** menu *Pesanan Saya* → server mengambil pesanan miliknya → daftar (kode, game dan layanan, status, tanggal) dengan dua tampilan: **aktif** (`diproses`, `sedang\_dikerjakan`, `perlu\_revisi\_hasil`, dan status menunggu lain) dan **riwayat** (selesai). Bila kosong tampil pesan.
* **Detail pesanan (Worker):** rincian pilihan layanan (apa yang harus dikerjakan, misalnya rank awal dan tujuan, wilayah dan progres, atau daftar quest), **data akun game termasuk password** selama `password\_akun\_game` belum dihapus, catatan customer, catatan revisi dari Admin bila ada, dan riwayat status.
* **Referensi:** W03.

#### W-04 Mulai Pengerjaan Pesanan

* **Prakondisi:** status `diproses`.
* **Alur:** membuka detail → melihat data akun dan rincian pilihan → **Mulai Pengerjaan** → server mengubah status menjadi `sedang\_dikerjakan` dan mencatat riwayat → Worker mengerjakan joki di akun game customer.
* **Referensi:** W04.

#### W-05 Laporkan Penyelesaian Pesanan

* **Prakondisi:** status `sedang\_dikerjakan`.
* **Alur:** membuka pesanan → form laporan → memilih **foto bukti penyelesaian** → **Kirim Laporan** → validasi → server menyimpan foto di penyimpanan privat, membuat baris `laporan\_penyelesaian` (`status = menunggu\_verifikasi`), mengubah status pesanan menjadi `laporan\_masuk`, dan mencatat riwayat.
* **Validasi:** foto wajib, JPG, JPEG, PNG, atau WebP, maksimal 2 MB, satu foto per laporan.
* **Referensi:** W05.

#### W-06 Revisi Hasil Pengerjaan

* **Prakondisi:** status `perlu\_revisi\_hasil`.
* **Alur:** membuka pesanan → membaca catatan revisi Admin → memperbaiki hasil di akun game → membuka form laporan revisi → memilih foto bukti perbaikan → **Kirim Revisi** → validasi → server membuat **baris laporan baru** (laporan lama tetap sebagai riwayat), mengubah status pesanan menjadi `laporan\_masuk`, dan mencatat riwayat.
* **Referensi:** W06.

### 3.7 Proses otomatis sistem: kedaluwarsa pesanan

Bukan fitur role mana pun. Digambarkan di `Activity-Diagram-Sistem-v2.drawio`.

|Aspek|Ketentuan|
|-|-|
|Nama perintah|`pesanan:kedaluwarsakan` (Artisan command)|
|Jadwal|Setiap 5 menit, didaftarkan di `routes/console.php`|
|Sasaran|Pesanan dengan `status = menunggu\_pembayaran` dan `batas\_waktu\_bayar` lebih kecil dari waktu sekarang|
|Tindakan|Ubah status menjadi `kedaluwarsa`, hapus password akun game (`password\_akun\_game = NULL`, `kredensial\_dihapus\_at` diisi), catat riwayat dengan `diubah\_oleh = NULL` dan catatan *Pesanan kedaluwarsa otomatis*|
|Sifat|Idempotent (aman dijalankan berulang)|
|Saat demo di localhost|Jalankan `php artisan schedule:work` di satu terminal terpisah, atau jalankan perintahnya manual untuk pengujian: `php artisan pesanan:kedaluwarsakan`|
|Pengaman|Pesanan yang sudah beralih ke `menunggu\_verifikasi` tidak tersentuh karena syarat statusnya tidak terpenuhi|



