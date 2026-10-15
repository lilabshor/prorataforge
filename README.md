<div align="center">

<h1>⚡ ProrataForge</h1>
<h3>High-Precision Enterprise SaaS Billing &amp; Prorata Engine</h3>


<p align="center">
  <a href="https://codeigniter.com"><img src="https://img.shields.io/badge/CodeIgniter-4.5%2B-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white" alt="CodeIgniter 4"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+"></a>
  <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-8.0_InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0"></a>
  <a href="https://www.php.net/manual/en/function.intdiv.php"><img src="https://img.shields.io/badge/Precision-Zero_Float_Drift-10B981?style=for-the-badge&logo=shield&logoColor=white" alt="Zero Float Drift"></a>
  <a href="https://phpunit.de"><img src="https://img.shields.io/badge/PHPUnit-15%2F15_Passing-38BDF8?style=for-the-badge&logo=phpunit&logoColor=white" alt="PHPUnit 15/15"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge" alt="MIT License"></a>
</p>

<p align="center">
  <strong>High-Precision SaaS Billing &amp; Prorata Engine</strong> yang dibangun di atas <strong>CodeIgniter 4</strong> dan <strong>PHP 8.3+</strong>.<br>
  Menghitung kalkulasi pergantian paket langganan secara adil (<em>prorata</em>), mutasi saldo kredit pelanggan (<em>credit ledger</em>), penerbitan faktur atomik, dan integrasi multi-gateway pembayaran tanpa kesalahan pembulatan angka desimal (<em>zero float drift</em>).
</p>

</div>

---

## 🏛️ Live Runtime Architecture Map

<div align="center">
  <img src="./assets/architecture.svg" alt="ProrataForge Runtime Architecture Map" width="100%">
</div>

<br>

| 🎯 Guided Story: Zero Float Drift | 🔒 Route Probe: Atomic & Lock | 💳 Security Lens: Multi-Gateway |
| :--- | :--- | :--- |
| **Aritmatika Integer Sen Murni**<br>Seluruh nominal uang disimpan sebagai `BIGINT` sen (`Rp150.000 = 15000000 sen`) dan dihitung menggunakan `intdiv()` + *half-up rounding* tanpa pernah menyentuh tipe data `float`. | **Anti-Race Condition & Idempoten**<br>Transaksi pergantian paket mengunci baris langganan & saldo via `SELECT ... FOR UPDATE` serta memvalidasi `Idempotency-Key` ber-hash SHA-256 agar bebas tagihan ganda. | **Verifikasi Webhook & Audit Trail**<br>Mendukung Midtrans (QRIS & verifikasi tanda tangan SHA-512 `hash_equals`) dan Xendit, dilengkapi *append-only* histori via CI4 Model Events. |

---

## 🌟 Fitur Utama

- 🎯 **Aritmatika Integer Sen**: Semua nilai nominal uang disimpan dan dikalkulasi dalam satuan terkecil (sen integer) menggunakan algoritma *half-up integer division*. Tidak ada `float` untuk uang.
- 🎛️ **Simulator Interaktif Prorata (`/playground`)**: Visualisasi *real-time* pecahan hari terpakai vs sisa hari beserta kalkulator perbedaan biaya dan generator cuplikan kode (*cURL, JavaScript, PHP, Python*).
- 🔄 **Siklus Hidup Langganan Lengkap**:
  - **Upgrade**: Menagih selisih pemakaian hari tersisa secara adil dan berlaku seketika.
  - **Downgrade**: Mengonversi kelebihan pembayaran paket lama menjadi saldo deposit kredit pelanggan (*credit ledger*) yang otomatis memotong tagihan berikutnya.
- 🧾 **Faktur Otomatis & Siap Cetak**: Penerbitan faktur berurutan (`INV/YYYY/MM/000001`), pemotongan deposit kredit otomatis, PPN 11% (`1100` basis poin), pembulatan rupiah penuh yang transparan, serta layout cetak A4/PDF.
- 💳 **Multi-Gateway Adapter**:
  - **Midtrans** (QRIS & verifikasi tanda tangan SHA-512 waktu-konstan).
  - **Xendit** (Invoice & token callback verification).
  - **Adapter Factory Pattern** yang siap diekstensi ke penyedia pembayaran lain.
- 🛠️ **CLI Pengembang (`php spark`)**:
  - `php spark billing:mock-webhook [ORDER_ID]` — Simulasi webhook pelunasan lokal tanpa memerlukan ngrok.
  - `php spark billing:renew` — Command perpanjangan otomatis periode langganan berkala (*auto-renewal*).
  - `php spark billing:dunning` & `php spark billing:expire` — Pengingat tunggakan bertingkat (H+1, H+3, H+7) dan penutupan masa *trial*/*suspended*.
- 🔒 **Keamanan & Skalabilitas Kelas Produksi**: Dukungan wajib header `Idempotency-Key`, *rate limit sliding window* 60 req/menit, transaksi database multi-tabel atomik, dan pencatatan histori *append-only* otomatis melalui CodeIgniter 4 *Model Events*.

---

## 📐 Prinsip Desain & Konvensi Prorata

ProrataForge menghilangkan ambiguitas perhitungan hari dan kebocoran pembulatan melalui **konvensi interval setengah-terbuka `[tanggal_mulai, tanggal_selesai)`** dan prinsip **kalikan dulu, bagi terakhir**:

```text
total_hari    = selesai - mulai                          (kalender nyata: 28 / 29 / 30 / 31 hari)
hari_terpakai = tgl_ubah - mulai                         (dinormalkan ke pukul 00:00:00 UTC)
sisa_hari     = total_hari - hari_terpakai

kredit_sen    = half_up((harga_lama_sen * sisa_hari) / total_hari)
tagihan_sen   = half_up((harga_baru_sen * sisa_hari) / total_hari)
bersih_sen    = tagihan_sen - kredit_sen
```

### 📊 Contoh Kasus Terverifikasi (`ProrataCalculator`)

| Kasus | Skenario Perubahan | Periode & Tanggal Ubah | Sisa / Total Hari | Kredit Paket Lama | Tagihan Paket Baru | Tagihan Bersih (Sebelum PPN) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Kasus A** | Upgrade Basic (`Rp90.000`) ke Pro (`Rp150.000`) | 1 Apr – 1 Mei (Ubah 11 Apr) | 20 / 30 hari | `Rp60.000,00` | `Rp100.000,00` | **`Rp40.000,00`** (Tagih) |
| **Kasus B** | Upgrade Tahun Kabisat (`Rp290.000` ke `Rp580.000`) | 1 Feb – 1 Mar 2024 (Ubah 15 Feb) | 15 / 29 hari | `Rp150.000,00` | `Rp300.000,00` | **`Rp150.000,00`** (Tagih) |
| **Kasus E** | Downgrade Bisnis (`Rp300.000`) ke Basic (`Rp100.000`) | 1 Okt – 1 Nov (Ubah 11 Okt) | 21 / 31 hari | `Rp203.225,81` | `Rp67.741,94` | **`-Rp135.483,87`** (Jadi Saldo Kredit) |
| **Kasus F** | Harga Ganjil (`Rp99.999` ke `Rp149.999`) | 1 Apr – 1 Mei (Ubah 11 Apr) | 20 / 30 hari | `Rp66.666,00` | `Rp99.999,33` | **`Rp33.333,33`** (Bebas Selisih Rp6) |
| **Kasus H** | Februari Non-Kabisat (`Rp280.000` ke `Rp560.000`) | 1 Feb – 1 Mar 2026 (Ubah 15 Feb) | 14 / 28 hari | `Rp140.000,00` | `Rp280.000,00` | **`Rp140.000,00`** (Tagih) |

---

## ⚡ Alur Transaksi Atomik & Concurrency Defense

Setiap eksekusi pergantian paket (`SubscriptionService::gantiPaket()`) berjalan di dalam satu transaksi database terisolasi dengan penguncian baris pesimistis (`SELECT ... FOR UPDATE`):

```text
[ Incoming Request: POST /api/langganan/{id}/ganti-paket ]
          │
          ▼
1. Validasi Header Idempotency-Key & Format Input (YYYY-MM-DD)
          │
          ▼
$db->transBegin();
          │
          ├─► 2. Cek Tabel idempotency_keys (user_id + endpoint + kunci)
          │      └─► Jika sudah ada: Kembalikan respons JSON tersimpan (Idempotent Replay)
          │
          ├─► 3. SELECT * FROM langganan WHERE id = ? FOR UPDATE;
          │      └─► Kunci baris langganan & verifikasi kepemilikan (Anti-IDOR & Anti-Race)
          │
          ├─► 4. Ambil harga paket lama & baru langsung dari tabel paket_langganan di Server
          │      └─► Terapkan diskon kupon (bila ada) sebelum kalkulasi prorata
          │
          ├─► 5. Eksekusi ProrataCalculator::hitung() -> HasilProrata DTO (Murni Integer Sen)
          │
          ├─► 6. InvoiceService::buatInvoiceProrata() & CreditService
          │      ├─► Bersih < 0 (Downgrade): Tambah ke saldo_kredit + tulis buku_kredit
          │      └─► Bersih > 0 (Upgrade): Potong saldo_kredit (FOR UPDATE) + PPN 11% + Pembulatan
          │
          ├─► 7. Update paket_id & versi (+1) via LanggananModel
          │      └─► Model Events (beforeUpdate & afterUpdate) otomatis mencatat riwayat_perubahan
          │
          ├─► 8. Simpan hash_request (SHA-256) & respons_json ke tabel idempotency_keys
          │
$db->transCommit(); ──► [ Kunci Dilepas & Antrekan Notifikasi Email ]
```

---

## 📁 Arsitektur Direktori & 14 Tabel InnoDB

```text
prorataforge/
├── assets/
│   └── architecture.svg                         # Diagram arsitektur interaktif beranimasi
├── app/
│   ├── Config/
│   │   ├── Billing.php                          # Konfigurasi PPN 1100 bps, dunning, & masa tenggang
│   │   ├── Filters.php                          # Registrasi AuthFilter, AdminFilter, ThrottleFilter
│   │   └── Routes.php                           # Definisi rute Web, Playground, REST API, & Webhook
│   ├── Controllers/
│   │   ├── Home.php & PlaygroundController.php  # Dashboard & Simulator Interaktif (/playground)
│   │   ├── LanggananController.php              # Antarmuka pengelolaan langganan web
│   │   ├── TagihanController.php                # Daftar & cetak faktur A4/PDF
│   │   └── Api/
│   │       ├── LanggananApiController.php       # Endpoint simulasi & gantiPaket() idempoten
│   │       ├── TagihanApiController.php         # Endpoint detail invoice & inisiasi QRIS/VA
│   │       └── WebhookController.php            # Callback Midtrans (SHA-512) & Xendit
│   ├── Services/
│   │   ├── HasilProrata.php                     # Immutable readonly DTO hasil kalkulasi prorata
│   │   ├── ProrataCalculator.php                # Mesin hitung integer sen & pembulatan half-up
│   │   ├── SubscriptionService.php              # Orkestrator transaksi atomik & row locking
│   │   ├── InvoiceService.php                   # Generator invoice urut, kupon, & pajak PPN 11%
│   │   ├── CreditService.php                    # Mutasi saldo kredit & buku besar (credit ledger)
│   │   ├── PaymentService.php                   # Multi-Gateway Adapter Factory (Midtrans & Xendit)
│   │   └── NotificationService.php              # Antrean notifikasi email pasca-commit
│   ├── Models/
│   │   ├── LanggananModel.php                   # Model langganan + Model Events audit otomatis
│   │   ├── RiwayatPerubahanModel.php            # Model histori perubahan (append-only)
│   │   └── ...                                  # 12 Model InnoDB lainnya dengan allowedFields ketat
│   ├── Commands/
│   │   ├── RenewalCommand.php                   # php spark billing:renew / billing:renewal
│   │   ├── MockWebhookCommand.php               # php spark billing:mock-webhook [ORDER_ID]
│   │   └── DunningCommand.php & ExpireCommand   # Pengingat tagihan & kadaluwarsa otomatis
│   └── Helpers/
│       └── uang_helper.php                      # Helper formatRupiah() & formatRupiahPenuh() tanpa float
├── public/
│   └── prorataforge-postman-collection.json     # Koleksi pengujian REST API siap impor
├── tests/
│   ├── unit/ProrataCalculatorTest.php           # Unit test presisi kalkulator (Kasus A-H & penolakan)
│   ├── unit/SubscriptionServiceTest.php         # Pengujian transaksi, rollback, & saldo kredit
│   └── feature/GantiPaketApiTest.php            # Pengujian endpoint REST API & Idempotency-Key
├── docker-compose.yml                           # Orkestrasi kontainer siap jalan (App + MySQL 8)
└── README.md                                    # Dokumentasi utama proyek
```

---

## 🚀 Menjalankan via Docker (1 Perintah)

Prasyarat: Sudah terinstal [Docker Desktop](https://www.docker.com/) atau Docker Engine.

```bash
# 1. Clone repository
git clone [https://github.com/lilabshor/prorataforge.git](https://github.com/lilabshor/prorataforge.git)
cd prorataforge

# 2. Jalankan container (aplikasi + database MySQL otomatis dibuat & dimigrasi)
docker compose up -d
```

Buka peramban di **`http://localhost:8080`** atau coba simulator langsung di **`http://localhost:8080/playground`**.

---

## 💻 Menjalankan Manual (Lokal)

### Kebutuhan Sistem
- **PHP 8.2 atau 8.3+** (`ext-intl`, `ext-mbstring`, `ext-mysqli`, `ext-curl`, `ext-openssl`)
- **MySQL 8.0+** (InnoDB Engine dengan dukungan `utf8mb4`)
- **Composer 2.x**

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

# 3. Jalankan migrasi 14 tabel dan seeder awal
php spark migrate --all
php spark db:seed BillingSeeder

# 4. Jalankan server pengembangan lokal
php spark serve --host 0.0.0.0 --port 8080
```

---

## 🧪 Menjalankan Pengujian (Testing & Security Audit)

Suite pengujian unit test PHPUnit telah disiapkan untuk memastikan kalkulasi finansial, pembulatan *half-up*, dan alur pembayaran selalu presisi:

```bash
./vendor/bin/phpunit --no-coverage
```

Output terverifikasi:

```text
PHPUnit 10.5.66 by Sebastian Bergmann and contributors.

...............                                                   15 / 15 (100%)

Time: 00:00.024, Memory: 14.00 MB

OK (15 tests, 26 assertions)
```

### 🛡️ Matriks Keamanan & Ketahanan yang Diuji

| Kode Uji | Vektor Pengujian | Mekanisme Pertahanan ProrataForge | Status |
| :--- | :--- | :--- | :--- |
| **FT-19 / PT-10** | **Idempotency Replay & Klik Ganda** | Menyimpan sidik jari SHA-256 request di `idempotency_keys`; mengembalikan respons asli tanpa membuat tagihan kedua. | **PASS** |
| **FT-20** | **Penyalahgunaan Key pada Payload Berbeda** | Membandingkan `hash_request`; menolak otomatis dengan status `HTTP 409 Conflict`. | **PASS** |
| **FT-22 / PT-13** | **Race Condition Ganti Paket & Saldo Kredit** | Mengunci baris via `SELECT ... FOR UPDATE` di dalam transaksi; eksekusi berjalan serial dan saldo aman. | **PASS** |
| **PT-01 & PT-02** | **IDOR & Manipulasi Harga dari Client** | `user_id` diambil murni dari sesi server; harga diambil 100% dari tabel `paket_langganan` di database. | **PASS** |
| **PT-07 & PT-08** | **Webhook Palsu & Manipulasi Nominal Bayar** | Verifikasi signature SHA-512 dengan `hash_equals()` (anti *timing attack*) dan pencocokan `jumlah_sen`. | **PASS** |

---

## 🔌 Dokumentasi REST API

Aplikasi menyediakan dokumentasi interaktif dan file Postman Collection yang dapat langsung diunduh:
- **Interactive Simulator**: `http://localhost:8080/playground`
- **Web API Docs**: `http://localhost:8080/docs`
- **Postman Collection**: `public/prorataforge-postman-collection.json`

| Method | Endpoint | Header Wajib | Deskripsi Fungsional |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/kalkulator/simulasi` | `Content-Type: application/json` | Hitung simulasi prorata secara publik tanpa menulis ke database. |
| `POST` | `/api/langganan/{id}/ganti-paket` | `Idempotency-Key: <UUID>` | Eksekusi pergantian paket atomik, terbitkan faktur, atau mutasi kredit. |
| `GET` | `/api/tagihan/{id}` | Session / Bearer Auth | Ambil rincian faktur beserta baris item prorata, kredit, dan PPN. |
| `POST` | `/api/tagihan/{id}/bayar` | Session / Bearer Auth | Inisiasi pembayaran ke gateway (QRIS Midtrans / Invoice Xendit). |
| `POST` | `/webhook/pembayaran` | Gateway Signature | Endpoint penerima notifikasi callback pelunasan dari gateway pembayaran. |

---

## 📄 Lisensi

Didistribusikan di bawah lisensi open-source [MIT License](LICENSE).
