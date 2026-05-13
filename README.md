# SIPO Web

SIPO Web adalah aplikasi sistem informasi persuratan online berbasis web yang dikembangkan menggunakan **Laravel**. Aplikasi ini digunakan untuk mengelola surat masuk, surat keluar, disposisi, serta monitoring proses persuratan secara terintegrasi.

## ✨ Fitur Utama

* Autentikasi dan manajemen pengguna
* Role-based access control (Super Admin, Admin, Manager, Staff)
* Pengelolaan surat masuk dan surat keluar
* Disposisi surat
* Dashboard monitoring
* Upload dan manajemen dokumen
* Notifikasi status proses surat
* Soft delete dan restore data

---

## 🛠️ Teknologi yang Digunakan

* PHP 8.x
* Laravel
* MySQL / MariaDB
* Blade Template Engine
* Tailwind CSS / Bootstrap (sesuaikan dengan project)
* JavaScript
* Composer
* Node.js & npm

---

## 📋 Persyaratan Sistem

Pastikan perangkat pengembangan telah terpasang:

* PHP >= 8.1
* Composer
* MySQL atau MariaDB
* Node.js dan npm
* Git

---

## 🚀 Instalasi Project

### 1. Clone Repository

```bash
git clone https://github.com/BudHand/SipoWebsite.git
cd SipoWebsite
```

### 2. Install Dependency PHP

```bash
composer install
```

### 3. Install Dependency Frontend

```bash
npm install
```

### 4. Copy File Environment

```bash
cp .env.example .env
```

> Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipo
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Jalankan Migrasi dan Seeder

```bash
php artisan migrate --seed
```

### 8. Build Asset Frontend

```bash
npm run build
```

### 9. Jalankan Server Development

```bash
php artisan serve
```

Aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

---

## 👤 Default Login

Jika project menyediakan seeder akun default, gunakan kredensial berikut (sesuaikan jika berbeda):

```text
Email    : admin@example.com
Password : password
```

---

## 📁 Struktur Direktori Penting

| Direktori/File         | Fungsi                                         |
| ---------------------- | ---------------------------------------------- |
| `app/`                 | Logic aplikasi (Controller, Model, Middleware) |
| `resources/views/`     | File Blade Template                            |
| `routes/web.php`       | Routing aplikasi                               |
| `database/migrations/` | Struktur tabel database                        |
| `database/seeders/`    | Data awal aplikasi                             |
| `public/`              | File publik (CSS, JS, gambar)                  |
| `.env`                 | Konfigurasi environment                        |

---

## 🔐 Role Pengguna

* **Super Admin** → Akses penuh ke seluruh modul.
* **Admin** → Mengelola data operasional tertentu.
* **Manager** → Melihat dan memproses disposisi sesuai departemen.
* **Staff** → Mengelola surat sesuai kewenangan.

---

## 🧰 Perintah Artisan yang Sering Digunakan

```bash
php artisan serve
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed
php artisan route:list
php artisan optimize:clear
```

---

## 🎨 Perintah Frontend

```bash
npm run dev
npm run build
```

---

## 🐛 Troubleshooting

### Error `Class not found`

```bash
composer dump-autoload
```

### Error `APP_KEY missing`

```bash
php artisan key:generate
```

### Error `Storage link not found`

```bash
php artisan storage:link
```

### Error `Permission denied`

Pastikan folder berikut dapat ditulis:

* `storage/`
* `bootstrap/cache/`

---

## 🚀 Deployment Production

Perintah yang umum digunakan setelah deployment:

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

---

## 🔗 Repository

* Website: [https://github.com/BudHand/SipoWebsite](https://github.com/BudHand/SipoWebsite)
* Mobile App: [https://github.com/BudHand/SipoApp](https://github.com/BudHand/SipoApp)

---

## 👨‍💻 Developer

Dikembangkan oleh **BudHand**.

GitHub: [https://github.com/BudHand](https://github.com/BudHand)

---

## 📄 License

Project ini menggunakan lisensi MIT.
