<h1 align="center">📚 E-Perpus Standalone</h1>

<p align="center">
  <strong>Sistem Manajemen Perpustakaan Digital dengan Integrasi RFID</strong><br>
  Dibangun dengan Laravel 11 · Alpine.js · TailwindCSS · MySQL
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

---

## ✨ Fitur Utama

| Fitur | Keterangan |
|---|---|
| 📊 **Dashboard** | Statistik real-time: total buku, anggota, kunjungan hari ini, peminjaman aktif |
| 📖 **Katalog Buku** | CRUD buku dengan upload cover, kelola stok, dan pencarian |
| 👥 **Buku Tamu** | Log kunjungan via scan RFID otomatis atau input manual petugas |
| 📤 **Peminjaman** | Verifikasi anggota via RFID atau pilih manual, dengan tanggal tenggat |
| 📥 **Pengembalian** | Kembalikan cepat via RFID + kalkulasi denda otomatis keterlambatan |
| 📡 **Manajemen RFID** | Kelola perangkat scanner, lihat API Key, dan halaman simulator testing |
| 👤 **Multi-sekolah** | Isolasi data per `school_id`, cocok untuk deployment multi-tenant |
| 🌙 **Dark Mode** | Tampilan gelap/terang otomatis sesuai preferensi sistem |

---

## 🔧 Persyaratan Sistem

- **PHP** >= 8.2 (dengan ekstensi: `pdo_mysql`, `fileinfo`, `curl`, `openssl`)
- **Composer** >= 2.x
- **MySQL** >= 8.0 atau MariaDB >= 10.4
- **Node.js** >= 18.x & NPM (untuk build assets)
- **Web Server**: Apache / Nginx / `php artisan serve`

> **Untuk pengguna XAMPP:** Pastikan menggunakan XAMPP dengan PHP 8.2+.

---

## 🚀 Cara Setup (Development)

### 1. Clone Repository

```bash
git clone https://github.com/kangdaqiq/e-perpus.git
cd e-perpus
```

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` sesuai konfigurasi lokal Anda:

```env
APP_NAME="E-Perpus Standalone"
APP_URL=http://localhost:8001

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eperpus
DB_USERNAME=root
DB_PASSWORD=

# Koneksi ke database Sistem Absensi (untuk sinkronisasi data siswa/guru)
ABSEN_DB_HOST=127.0.0.1
ABSEN_DB_PORT=3306
ABSEN_DB_DATABASE=absen_sell
ABSEN_DB_USERNAME=root
ABSEN_DB_PASSWORD=

# Denda per hari keterlambatan (dalam Rupiah)
FINE_PER_DAY=1000
```

### 4. Buat Database & Jalankan Migrasi

```bash
# Buat database MySQL terlebih dahulu:
mysql -u root -e "CREATE DATABASE eperpus;"

# Jalankan migrasi
php artisan migrate

# (Opsional) Jalankan seeder untuk data dummy
php artisan db:seed
```

### 5. Setup Storage

```bash
php artisan storage:link
```

### 6. Jalankan Aplikasi

```bash
php artisan serve --port=8001
```

Buka browser: **http://localhost:8001**

---

## 🔑 Login Default

Buat akun admin pertama melalui database atau dengan menambahkan seeder. Akun user disimpan di tabel `users` dengan:

| Field | Keterangan |
|---|---|
| `email` | Email login |
| `password` | Password (bcrypt) |
| `school_id` | ID sekolah (foreign key ke tabel `schools`) |
| `role` | `admin` atau `superadmin` |

Contoh insert manual via MySQL:

```sql
INSERT INTO schools (id, name, created_at, updated_at)
VALUES (1, 'SMKN 1 Contoh', NOW(), NOW());

INSERT INTO users (name, email, password, school_id, role, created_at, updated_at)
VALUES ('Admin', 'admin@eperpus.com', '$2y$12$...', 1, 'admin', NOW(), NOW());
```

> Gunakan `php artisan tinker` dan `Hash::make('password123')` untuk generate password.

---

## 📡 Integrasi RFID

Sistem ini mendukung perangkat RFID reader (ESP8266/ESP32 atau Arduino) yang mengirim data via HTTP POST.

### Endpoint API

```
POST /api/rfid
Content-Type: application/json
```

### Payload Request

```json
{
  "api_key": "API_KEY_PERANGKAT_ANDA",
  "uid": "AB:CD:EF:12"
}
```

### Response Sukses (Kunjungan)

```json
{
  "success": true,
  "message": "Kunjungan dicatat.",
  "member": {
    "name": "Siti Aminah",
    "code": "12345",
    "class": "XII RPL 1"
  }
}
```

### Jenis Perangkat

| Tipe | Fungsi |
|---|---|
| `rfid_perpus_kunjungan` | Scanner di pintu masuk — mencatat kunjungan otomatis |
| `rfid_perpus_pinjam` | Scanner di meja petugas — verifikasi anggota saat pinjam/kembali |

### Menambah Perangkat Baru

1. Masuk ke halaman **Scanner RFID** (`/devices`)
2. Klik **Tambah Perangkat**
3. Isi nama dan pilih tipe perangkat
4. Salin **API Key** yang di-generate otomatis
5. Masukkan API Key ke firmware perangkat RFID

---

## 📋 Cara Pakai

### Alur Peminjaman Buku

```
1. Petugas buka halaman "Katalog Buku"
2. Klik tombol "Pinjam" pada buku yang dipilih
3. Pilih mode: Scan RFID atau Pilih Manual
   - RFID: anggota tap kartu → sistem otomatis mendeteksi
   - Manual: petugas pilih nama anggota dari dropdown
4. Set tanggal pinjam & tenggat → Konfirmasi
5. Sistem mencatat peminjaman + mengurangi stok buku
```

### Alur Pengembalian Buku

```
1. Petugas buka halaman "Peminjaman Buku"
2. Klik tombol "Kembalikan Buku" (RFID scan)
3. Anggota tap kartu → sistem menampilkan daftar buku yang dipinjam
4. Set tanggal kembali → sistem hitung denda otomatis
5. Konfirmasi → stok buku dipulihkan
```

### Sinkronisasi Data Anggota

Sistem ini terhubung ke database Sistem Absensi untuk sinkronisasi data siswa/guru sebagai anggota perpustakaan:

1. Buka **Dashboard**
2. Klik tombol **Sinkronisasi Data** (atau melalui API)
3. Data siswa & guru dari database absensi akan diimport sebagai anggota perpustakaan

---

## 🧪 Simulator API (Testing)

Halaman khusus untuk testing integrasi RFID tanpa perangkat fisik:

**URL:** `/devices/simulator`

Fitur:
- Kirim dummy request scan RFID dengan UID pilihan
- Pilih perangkat (kunjungan / pinjam)
- Lihat response JSON real-time
- Simulasi alur peminjaman end-to-end

---

## 🗂️ Struktur Database

```
schools          — Data sekolah (multi-tenant)
users            — Akun petugas perpustakaan
members          — Anggota perpustakaan (siswa & guru dari db absensi)
books            — Katalog buku
visits           — Log kunjungan perpustakaan
loans            — Transaksi peminjaman & pengembalian
devices          — Perangkat RFID reader
pending_verifications — Session verifikasi RFID sementara
```

---

## 🏗️ Teknologi yang Digunakan

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11 (PHP 8.2) |
| Frontend | Blade Templates + Alpine.js |
| Styling | TailwindCSS (via CDN) |
| Database | MySQL 8.0 |
| Icons | Font Awesome 6 |
| Fonts | Google Fonts (Inter) |

---

## 📁 Struktur Direktori Penting

```
app/
├── Http/Controllers/
│   ├── Api/RfidController.php      ← Endpoint RFID scanner
│   ├── BookController.php          ← CRUD katalog buku
│   ├── LoanController.php          ← Peminjaman & pengembalian
│   ├── VisitController.php         ← Buku tamu kunjungan
│   ├── DeviceController.php        ← Manajemen perangkat RFID
│   └── DashboardController.php     ← Dashboard & statistik
├── Models/
│   ├── Book.php
│   ├── Loan.php
│   ├── Member.php
│   ├── Visit.php
│   └── Device.php
resources/views/perpus/
├── layouts/app.blade.php           ← Layout utama (sidebar, navbar)
├── dashboard.blade.php
├── buku/index.blade.php            ← Katalog buku
├── kunjungan/index.blade.php       ← Buku tamu
├── loan/index.blade.php            ← Peminjaman
└── device/simulator.blade.php     ← API Simulator
```

---

## 🤝 Kontribusi

Pull request sangat diterima! Untuk perubahan besar, harap buka issue terlebih dahulu untuk mendiskusikan apa yang ingin Anda ubah.

1. Fork repository ini
2. Buat branch fitur baru: `git checkout -b fitur/nama-fitur`
3. Commit perubahan: `git commit -m 'feat: tambah fitur X'`
4. Push ke branch: `git push origin fitur/nama-fitur`
5. Buka Pull Request

---

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

---

<p align="center">
  Dibuat dengan ❤️ untuk membantu digitalisasi perpustakaan sekolah
</p>
