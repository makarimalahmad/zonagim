# Zonagim

<p align="center">
  <img src="public/images/zonagim.png" alt="Zonagim" width="180">
</p>

<p align="center">
  Marketplace akun game berbasis Laravel, React, Inertia, dan Filament.
</p>

## Tentang Proyek

Zonagim merupakan aplikasi marketplace untuk menampilkan dan mengelola akun game. Pengunjung dapat menelusuri katalog berdasarkan game, sedangkan pengguna terautentikasi dapat membuka detail produk dan mengelola profil.

Proyek ini juga menyediakan panel admin untuk pengelolaan kategori, produk, pengguna, dan statistik marketplace.

## Fitur Utama

### Marketplace

- Landing page responsif
- Daftar kategori game
- Katalog akun berdasarkan kategori
- Detail akun khusus pengguna terautentikasi
- Pencarian dan navigasi produk
- Dukungan tema terang dan gelap
- Animasi antarmuka dan smooth scrolling

### Autentikasi dan Keamanan

- Registrasi dengan verifikasi OTP melalui email
- Login dan logout
- Verifikasi alamat email
- Lupa dan reset password
- Konfirmasi dan pembaruan password
- Pengelolaan serta penghapusan profil
- Cloudflare Turnstile pada alur autentikasi
- Pembatasan akses panel berdasarkan role
- CSRF protection dan password hashing dari Laravel

### Panel Admin

- Autentikasi admin
- Pengelolaan kategori game
- Pengelolaan produk
- Ringkasan statistik
- Grafik pertumbuhan produk
- Daftar pengguna terbaru

### Integrasi dan SEO

- AI chatbot menggunakan Groq
- Sitemap XML
- `robots.txt`
- Metadata dan structured data
- Halaman kebijakan privasi
- Halaman syarat dan ketentuan

## Stack Teknologi

### Backend

- PHP 8.2+
- Laravel 12
- Filament 5
- Laravel Sanctum 4
- Inertia Laravel 2
- MySQL
- PHPUnit 11

### Frontend

- React 18
- Inertia.js 2
- Tailwind CSS 4
- daisyUI 5
- Vite 7
- Headless UI
- Framer Motion
- GSAP
- Lenis
- Lucide React
- SweetAlert2

## Arsitektur Singkat

```text
Browser
  |
  | HTTP / Inertia requests
  v
Laravel routes and controllers
  |
  +-- Authentication, OTP, profile, marketplace
  +-- Groq AI service
  +-- Mail and queue
  +-- Eloquent models
  |
  +--> MySQL
  |
  v
React pages and components

Admin
  |
  v
Filament panel
  |
  v
Categories, products, users, and dashboard widgets
```

## Persyaratan

Pastikan perangkat memiliki:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- MySQL
- Ekstensi PHP yang dibutuhkan Laravel
- SMTP account untuk pengiriman OTP dan reset password
- Groq API key untuk AI chatbot
- Cloudflare Turnstile keys untuk captcha

## Instalasi Lokal

1. Clone repository.

   ```bash
   git clone https://github.com/makarimalahmad/lapakakunid.git
   cd lapakakunid
   ```

2. Instal dependency PHP.

   ```bash
   composer install
   ```

3. Salin konfigurasi environment lokal. Gunakan `.env.example` hanya untuk development; deployment produksi wajib dimulai dari `.env.production.example`.

   Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

   Linux atau macOS:

   ```bash
   cp .env.example .env
   ```

4. Buat application key.

   ```bash
   php artisan key:generate
   ```

5. Buat database MySQL, lalu sesuaikan bagian berikut di `.env`.

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lapakakunid_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Jalankan migration.

   ```bash
   php artisan migrate
   ```

7. Buat symbolic link untuk storage.

   ```bash
   php artisan storage:link
   ```

8. Instal dependency frontend.

   ```bash
   npm install
   ```

9. Jalankan aplikasi untuk development.

   ```bash
   composer dev
   ```

   Perintah tersebut menjalankan server Laravel, queue worker, log viewer, dan Vite secara bersamaan.

10. Buka aplikasi di `http://127.0.0.1:8000`.

## Konfigurasi Layanan

### Email

Isi konfigurasi SMTP untuk OTP, verifikasi email, dan reset password.

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Jangan commit username atau password SMTP ke repository.

### Groq AI

```dotenv
GROQ_API_KEY=
```

Buat API key melalui platform resmi Groq, lalu simpan hanya di `.env`.

### Cloudflare Turnstile

```dotenv
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
```

Gunakan test keys resmi Cloudflare untuk development bila diperlukan. Jangan masukkan secret key produksi ke source code.

### Queue

Konfigurasi default memakai database queue.

```dotenv
QUEUE_CONNECTION=database
```

Jalankan worker secara terpisah bila tidak menggunakan `composer dev`.

```bash
php artisan queue:work
```

## Membuat Administrator

Repository tidak menyediakan credential admin bawaan. Buat akun secara interaktif agar tidak ada password publik.

1. Buat user melalui perintah Filament.

   ```bash
   php artisan make:filament-user
   ```

2. Promosikan user tersebut menjadi admin.

   ```bash
   php artisan tinker
   ```

   Jalankan kode berikut di Tinker dengan alamat email yang sesuai:

   ```php
    $admin = App\Models\User::where('email', 'admin@example.com')->firstOrFail();
    $admin->forceFill(['role' => 'admin'])->save();
   ```

3. Keluar dari Tinker dan buka `/admin`.

Gunakan password unik dan kuat. Jangan menjalankan seeder yang membuat admin dengan credential tetap pada lingkungan produksi.

## Build Produksi

Buat `.env` produksi dari template aman sebelum mengisi credential melalui secret manager atau konfigurasi hosting.

Windows PowerShell:

```powershell
Copy-Item .env.production.example .env
```

Linux atau macOS:

```bash
cp .env.production.example .env
```

Jangan menimpa `.env` produksi yang sudah berisi credential. Setelah konfigurasi selesai:

```bash
npm run build
php artisan optimize
```

Konfigurasi produksi minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
LOG_LEVEL=warning
```

Pastikan web server mengarah ke direktori `public`, HTTPS aktif, queue worker berjalan, dan permission direktori `storage` serta `bootstrap/cache` benar.

## Pengujian

Jalankan test backend:

```bash
composer test
```

Jalankan build frontend:

```bash
npm run build
```

Audit dependency:

```bash
composer audit
npm audit
```

Status verifikasi dapat berubah mengikuti pengembangan. Jalankan seluruh perintah audit di atas pada commit yang akan diterapkan dan jadikan hasil CI commit tersebut sebagai sumber kebenaran.

## Struktur Direktori

```text
app/
  Filament/        Resource dan widget panel admin
  Http/            Controller, middleware, dan request
  Models/          Model Eloquent
  Services/        Integrasi layanan aplikasi
database/
  migrations/      Skema database
  seeders/         Seeder tanpa credential bawaan
resources/
  css/             Stylesheet aplikasi
  js/              React pages dan components
routes/
  web.php          Marketplace, profil, halaman statis, dan chatbot
  auth.php         Rute autentikasi
tests/
  Feature/         Test alur aplikasi
  Unit/            Unit test
```

## Praktik Keamanan Repository

- `.env` tidak boleh masuk Git.
- Gunakan `.env.example` hanya untuk nama variabel dan nilai contoh.
- Jangan commit API key, token, password, database dump, log, atau data pengguna.
- Rotasi credential bila pernah masuk riwayat Git.
- Aktifkan GitHub Secret Scanning, Push Protection, dan Dependabot.
- Jalankan `composer audit` dan `npm audit` secara berkala.
- Periksa seluruh perubahan sebelum mengubah repository menjadi public.

## Lisensi dan Aset Pihak Ketiga

Kode sumber proyek dirilis menggunakan lisensi MIT yang tersedia di `LICENSE`.

Nama game, logo, gambar, dan merek pihak ketiga tetap menjadi hak pemilik masing-masing. Pengguna repository wajib memastikan izin penggunaan aset untuk konteks dan wilayah penerapannya. Aset pihak ketiga tidak tercakup oleh lisensi MIT proyek dan tidak boleh dianggap sebagai dukungan resmi dari pemilik merek.