# 🏨 Hocky Guest House — Sistem Manajemen Penginapan

Sistem manajemen penginapan berbasis web untuk **Hocky Guest House**, dibangun menggunakan **Laravel 12** dengan tampilan dark mode yang modern dan responsif.

---

## ✨ Fitur Utama

### 🔐 Autentikasi
- Login admin dengan proteksi sesi
- Single admin system (tidak perlu registrasi publik)

### 🏠 Dashboard
- Grid status kamar secara real-time (Tersedia / Terisi / Perlu Dibersihkan)
- Statistik ringkasan: total kamar, kamar tersedia, terisi, kotor, dan pendapatan hari ini
- Aksi cepat: Check-In, Tambah Kamar, Tambah Pelanggan, Lihat Faktur

### 🛏 Manajemen Kamar
- CRUD kamar (Tambah, Edit, Hapus)
- Set nomor, nama, tipe, harga per malam, dan deskripsi
- Ubah status kamar secara manual (Tersedia / Kotor)
- Filter berdasarkan tipe dan status

### 👤 Manajemen Pelanggan
- CRUD data pelanggan (KTP / SIM / Passport)
- Pencarian berdasarkan nama, nomor identitas, atau nomor telepon
- Riwayat menginap per pelanggan

### ✅ Check-In & Check-Out
- Form check-in dengan autocomplete nama pelanggan
- Kalkulasi biaya otomatis (harga per malam × durasi)
- Minimal 1 malam meskipun menginap beberapa jam
- Metode pembayaran: Tunai, Transfer Bank, QRIS
- Setelah checkout, status kamar otomatis menjadi "Perlu Dibersihkan"

### 🧾 Faktur / Kwitansi
- Daftar semua faktur dengan filter status dan pencarian
- Halaman detail faktur yang dapat **dicetak / disimpan sebagai PDF**

### 💰 Laporan Pendapatan
- Ringkasan pendapatan: Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini
- Grafik garis pendapatan 12 bulan terakhir
- Tabel riwayat transaksi dengan filter bulan & tahun
- **Fitur cetak laporan** — memuat semua transaksi sekaligus tanpa pagination

---

## 🛠 Teknologi

| Komponen | Detail |
|---|---|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Database** | MySQL |
| **Frontend** | Blade Template + Vanilla CSS |
| **UI Theme** | Dark Mode, Indigo/Violet Accent |
| **Chart** | Chart.js v4 |
| **Font** | Google Fonts — Inter |

---

## 🚀 Instalasi

### Prasyarat
- PHP >= 8.2
- Composer
- MySQL
- Web Server (Laragon / XAMPP / Nginx)

### Langkah-langkah

```bash
# 1. Clone repository
git clone https://github.com/username/sistem-penginapan.git
cd sistem-penginapan

# 2. Install dependensi PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Konfigurasi database di file .env
# DB_DATABASE=db_penginapan
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Jalankan migrasi dan seeder
php artisan migrate:fresh --seed

# 7. Jalankan server lokal
php artisan serve
```

### Akun Default Admin
```
Email    : admin@hocky.com
Password : admin123
```

> ⚠️ Segera ubah password setelah pertama kali login di lingkungan produksi.

---

## 📁 Struktur Direktori Penting

```
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php          # Login & logout
│   │   ├── DashboardController.php     # Halaman utama
│   │   ├── KamarController.php         # CRUD kamar
│   │   ├── PelangganController.php     # CRUD pelanggan
│   │   ├── PemesananController.php     # Check-in & check-out
│   │   ├── FakturController.php        # Faktur / kwitansi
│   │   └── PendapatanController.php    # Laporan pendapatan
│   └── Models/
│       ├── Kamar.php
│       ├── Pelanggan.php
│       ├── Pemesanan.php
│       └── Pembayaran.php
├── database/
│   ├── migrations/                     # Struktur tabel
│   └── seeders/DatabaseSeeder.php      # Data awal (admin)
├── public/
│   ├── css/app.css                     # Design system (dark mode)
│   └── images/logo.png                 # Logo guest house
├── resources/views/
│   ├── layouts/app.blade.php           # Layout utama + sidebar
│   ├── auth/login.blade.php
│   ├── dashboard/index.blade.php
│   ├── kamar/
│   ├── pelanggan/
│   ├── pemesanan/
│   ├── faktur/
│   └── pendapatan/
└── routes/web.php                      # Definisi semua route
```

---

## 💡 Catatan Pengembangan

- **Laporan Pendapatan** menggunakan metode **Cash Basis** — transaksi dicatat berdasarkan tanggal pembayaran, bukan tanggal check-in
- **Timezone** aplikasi dikonfigurasi ke `Asia/Jakarta` (WIB)
- Kustomisasi warna tema dapat dilakukan di bagian `:root` pada `public/css/app.css`
- Untuk menambah metode pembayaran baru, update `enum` di migration `create_pembayaran_table` dan validasi di `PemesananController`

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan pembelajaran dan kebutuhan internal **Hocky Guest House**.

---

> Dibuat dengan ❤️ menggunakan [Laravel](https://laravel.com) dan [Google Antigravity]
 
> AMN
