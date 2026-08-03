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

- PHP 8.3+
- Laravel 12
- Filament 5
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
