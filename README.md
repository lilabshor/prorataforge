# ProrataForge ⚡

> **High-Precision SaaS Billing & Prorata Engine** dibangun di atas CodeIgniter 4 dan PHP 8.3+.  
> Menghitung kalkulasi pergantian paket langganan secara adil (*prorata*), mutasi saldo kredit pelanggan (*credit ledger*), penerbitan faktur atomik, dan integrasi multi-gateway pembayaran tanpa kesalahan pembulatan angka desimal (*zero float drift*).

---

## 🌟 Fitur Utama

- 🎯 **Aritmatika Integer Sen**: Semua nilai nominal uang disimpan dan dikalkulasi dalam satuan terkecil (sen integer) menggunakan algoritma *half-up integer division*. Tidak ada `float` untuk uang.
- 🎛️ **Simulator Interaktif Prorata (`/playground`)**: Visualisasi *real-time* pecahan hari terpakai vs sisa hari beserta kalkulator perbedaan biaya dan generator cuplikan kode (*cURL, JavaScript, PHP, Python*).
- 🔄 **Siklus Hidup Langganan Lengkap**:
  - **Upgrade**: Menagih selisih pemakaian hari tersisa secara adil.
  - **Downgrade**: Mengonversi kelebihan pembayaran paket lama menjadi saldo deposit kredit pelanggan.
- 🧾 **Faktur Otomatis & Siap Cetak**: Penerbitan faktur berurutan, pemotongan deposit kredit, PPN 11% (1100 basis poin), pembulatan rupiah penuh, serta layout cetak A4/PDF.
- 💳 **Multi-Gateway Adapter**:
  - Midtrans (QRIS & verifikasi SHA-512 signature).
  - Xendit (Invoice & token verification).
  - Adapter Factory Pattern siap diekstensi.
- 🛠️ **CLI Pengembang**:
  - `php spark billing:mock-webhook [ORDER_ID]` — Simulasi webhook pelunasan lokal tanpa ngrok.
  - `php spark billing:renew` — Command perpanjangan otomatis periode langganan berkala.
- 🔒 **Keamanan & Skalabilitas**: Dukungan `Idempotency-Key` header, rate limit sliding window 60 req/menit, dan transaksi database multi-tabel atomik.

---

## 🚀 Menjalankan via Docker (1 Perintah)

Prasyarat: Sudah terinstal [Docker Desktop](https://www.docker.com/) atau Docker Engine.

```bash
# 1. Clone repository
git clone https://github.com/your-username/prorataforge.git
cd prorataforge

# 2. Jalankan container (aplikasi + database MySQL otomatis dibuat & dimigrasi)
docker compose up -d
```

Buka peramban di **`http://localhost:8080`**.

---

## 💻 Menjalankan Manual (Lokal)

### Kebutuhan Sistem
- PHP 8.2 atau 8.3 (`ext-intl`, `ext-mbstring`, `ext-mysqli`, `ext-curl`)
- MySQL 8.0+
- Composer 2.x

### Langkah Instalasi
```bash
# 1. Pasang dependensi
composer install

# 2. Siapkan environment
cp env .env

# Atur koneksi database di file .env:
# database.default.hostname = localhost
# database.default.database = prorataforge
# database.default.username = root
# database.default.password = password_anda

# 3. Jalankan migrasi dan seeder awal
php spark migrate --all
php spark db:seed BillingSeeder

# 4. Jalankan server lokal
php spark serve --host 0.0.0.0 --port 8080
```

---

## 🧪 Menjalankan Pengujian (Testing)

Suite pengujian unit test PHPUnit telah disiapkan untuk memastikan kalkulasi finansial dan alur pembayaran selalu presisi:

```bash
./vendor/bin/phpunit --no-coverage
```

Output:
```
PHPUnit 10.5.66 by Sebastian Bergmann and contributors.
...............                                                   15 / 15 (100%)

Time: 00:00.024, Memory: 14.00 MB
OK (15 tests, 26 assertions)
```

---

## 🔌 Dokumentasi REST API

Aplikasi menyediakan dokumentasi API dan file Postman Collection yang dapat langsung diunduh:
- **Web Docs**: `http://localhost:8080/docs`
- **Postman File**: `public/prorataforge-postman-collection.json`
- **Endpoint Utama**:
  - `POST /api/kalkulator/simulasi` — Hitung simulasi prorata secara publik.
  - `POST /api/langganan/{id}/ganti-paket` — Eksekusi pergantian paket (wajib header `Idempotency-Key`).
  - `POST /api/tagihan/{id}/bayar` — Persiapkan pembayaran gateway QRIS.
  - `POST /webhook/pembayaran` — Endpoint penerima notifikasi webhook dari gateway.

---

## 📄 Lisensi
Didistribusikan di bawah lisensi open-source [MIT License](LICENSE).
