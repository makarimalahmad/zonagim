# Panduan Deployment Zonagim ke VPS (Docker)

Panduan ini ditujukan untuk deployment **Zonagim Marketplace** ke VPS (Ubuntu 22.04 / 24.04 LTS, RAM 4GB, 2 CPU).

---

## 1. Persiapan Awal di VPS

### A. Update Sistem & Install Docker
Jalankan perintah berikut di VPS via SSH:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git ufw ca-certificates gnupg

# Install Docker Engine & Docker Compose Plugin resmi
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Tambahkan user aktif ke grup docker (opsional agar tidak perlu sudo)
sudo usermod -aG docker $USER
newgrp docker
```

### B. Konfigurasi Firewall (UFW)
```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## 2. Clone Repository & Konfigurasi Lingkungan

### A. Clone Project
```bash
sudo mkdir -p /var/www
cd /var/www
git clone <URL_REPO_GITHUB_KAMU> zonagim
cd zonagim
```

### B. Setup File `.env`
Salin template `.env.production.example` menjadi `.env`:

```bash
cp .env.production.example .env
nano .env
```

Sesuaikan nilai-nilai penting berikut:
- `APP_URL=https://zonagim.my.id` (atau domain VPS kamu)
- `DB_HOST=db` (nama service di docker-compose)
- `DB_DATABASE=zonagim_db`
- `DB_USERNAME=zonagim_app`
- `DB_PASSWORD=<buat_password_database_kuat>`
- `DB_ROOT_PASSWORD=<buat_password_root_kuat>`
- `REDIS_HOST=redis`
- `MAIL_*` (konfigurasi SMTP email untuk pengiriman OTP & reset password)
- `GROQ_API_KEY=<api_key_groq_kamu>`
- `TURNSTILE_SITE_KEY` & `TURNSTILE_SECRET_KEY` (Cloudflare Turnstile)

---

## 3. Build & Jalankan Container

Jalankan perintah berikut untuk meng-compile image dan menjalankan seluruh container:

```bash
docker compose up -d --build
```

### Cek Status & Log Container
```bash
# Cek semua container yang berjalan
docker compose ps

# Cek log aplikasi
docker compose logs -f app
```

### Generate `APP_KEY` (Jika Belum Ada)
```bash
docker compose exec app php artisan key:generate --force
```

---

## 4. Langkah Pasca-Deploy (Wajib)

### A. Seeding Data Wilayah Indonesia (Laravolt)
Aplikasi membutuhkan database wilayah (Provinsi, Kota, Kecamatan, Kelurahan) untuk profil pengguna:

```bash
docker compose exec app php artisan laravolt:indonesia:seed
```

### B. Membuat Akun Admin Pertama
Gunakan Tinker di dalam container untuk membuat akun administrator:

```bash
docker compose exec app php artisan tinker
```

Lalu ketikkan di dalam tinker:
```php
\App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@zonagim.my.id',
    'password' => bcrypt('PasswordAdminSangatKuat123!'),
    'email_verified_at' => now(),
    'role' => 'admin',
]);
exit;
```
Setelah itu, kamu dapat mengakses panel admin di `https://domain-kamu/admin`.

---

## 5. Konfigurasi Domain & SSL (HTTPS)

### Rekomendasi: Gunakan Cloudflare (Paling Cepat & Aman)
1. Arahkan DNS **A Record** domain kamu (`@` dan `www`) ke IP Publik VPS di dashboard Cloudflare.
2. Pastikan icon awan oranye (Proxy) aktif.
3. Di menu **SSL/TLS** Cloudflare, pilih mode **Full** atau **Full (Strict)**.
4. Situs kamu langsung aman dengan HTTPS otomatis tanpa perlu install certbot di VPS.

---

## 6. Perintah Pemeliharaan Harian

| Kebutuhan | Perintah |
| :--- | :--- |
| **Update Aplikasi (Git Pull & Rebuild)** | `git pull && docker compose up -d --build` |
| **Restart Semua Service** | `docker compose restart` |
| **Stop Semua Service** | `docker compose down` |
| **Lihat Log Nginx** | `docker compose logs -f web` |
| **Lihat Log Laravel** | `docker compose logs -f app` |
| **Clear / Refresh Cache Laravel** | `docker compose exec app php artisan optimize:clear && docker compose exec app php artisan optimize` |
| **Backup Database MySQL** | `docker compose exec db mysqldump -u root -p<DB_ROOT_PASSWORD> zonagim_db > backup_$(date +%F).sql` |
