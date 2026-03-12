# SIPO Website

<p align="center">
  <strong>Sistem Informasi Persuratan Online</strong><br/>
  Aplikasi manajemen dokumen internal (Memo, Undangan, Risalah) berbasis Laravel.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/License-MIT-blue" alt="MIT License" />
</p>

---

## 📌 Ringkasan

**SIPO Website** adalah aplikasi web untuk mengelola alur dokumen perusahaan secara terstruktur, mulai dari pembuatan, distribusi, arsip, hingga monitoring status dokumen.

Dokumen utama yang dikelola:
- **Memo**
- **Undangan**
- **Risalah**

---

## ✅ Prasyarat

Pastikan environment lokal sudah memiliki:

- **PHP 8.2+**
- **Composer 2+**
- **MySQL/MariaDB**
- **Node.js 18+** dan **npm**
- Git

Opsional (jika diperlukan project):
- Ekstensi PHP umum Laravel (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`)

---

## 🚀 Quick Start (setelah pull terbaru)

Ikuti langkah berikut dari root project.

### 1) Clone / Pull repository

```bash
git clone <url-repository>
cd SipoWebsite
# atau jika repo sudah ada
# git pull origin <branch>
```

### 2) Install dependency backend (PHP)

```bash
composer install
```

### 3) Install dependency frontend

```bash
npm install
```

### 4) Siapkan file environment

```bash
cp .env.example .env
```

Lalu sesuaikan konfigurasi utama di `.env`:

```env
APP_NAME="SIPO Website"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipo_website
DB_USERNAME=root
DB_PASSWORD=
```

> Pastikan database (`sipo_website`) sudah dibuat di MySQL sebelum migrasi.

### 5) Generate app key

```bash
php artisan key:generate
```

### 6) Jalankan migration (dan seeder bila diperlukan)

```bash
php artisan migrate
```

Jika ingin mengisi data awal:

```bash
php artisan db:seed
```

Atau reset total + seed:

```bash
php artisan migrate:fresh --seed
```

### 7) Build asset frontend

Untuk development:

```bash
npm run dev
```

Untuk production build:

```bash
npm run build
```

### 8) Jalankan aplikasi

Di terminal terpisah:

```bash
php artisan serve
```

Aplikasi dapat diakses di:

- `http://127.0.0.1:8000`

---

## 🧭 Alur Menjalankan Project (Rekomendasi Dev)

Gunakan 3 terminal agar nyaman:

1. **Terminal A** → `php artisan serve`
2. **Terminal B** → `npm run dev`
3. **Terminal C (opsional)** → `php artisan queue:listen`

Jika ingin menjalankan workflow dev dari Composer script:

```bash
composer run dev
```

---

## 🧪 Menjalankan Testing

```bash
php artisan test
```

Jika ada test terkait database, pastikan koneksi DB test sudah benar.

---

## 🛠️ Troubleshooting Umum

### 1) Error `vendor/autoload.php` tidak ditemukan
Jalankan:

```bash
composer install
```

### 2) Error koneksi database
- Cek host/port/user/password di `.env`
- Pastikan service MySQL aktif
- Pastikan nama database sudah dibuat

### 3) Perubahan `.env` tidak terbaca
Jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

### 4) Asset tidak muncul/styling berantakan
Jalankan ulang:

```bash
npm install
npm run dev
```

---

## 📦 Struktur Perintah Setup (Ringkas)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

---

## 🤝 Kontribusi

Jika ingin kontribusi:
1. Buat branch baru dari branch aktif.
2. Commit dengan pesan yang jelas.
3. Buat pull request dengan deskripsi perubahan.

---

## 📄 Lisensi

Project ini menggunakan lisensi **MIT**.
