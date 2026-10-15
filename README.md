<div align="center">⚡ ProrataForgeHigh-Precision Enterprise SaaS Billing & Prorata EngineGanti paket di tengah bulan? Tagihannya tetap adil, sampai ke sen terakhir.CodeIgniter 4PHP VersionMySQL EngineArithmeticTestsLicense<p align="center"><strong>High-Precision SaaS Billing &amp; Prorata Engine</strong> yang dibangun di atas <strong>CodeIgniter 4</strong> dan <strong>PHP 8.3+</strong>.<br>Menghitung kalkulasi pergantian paket langganan secara adil (<em>prorata</em>), mutasi saldo kredit pelanggan (<em>credit ledger</em>), penerbitan faktur atomik, dan integrasi multi-gateway pembayaran tanpa kesalahan pembulatan angka desimal (<em>zero float drift</em>).</p></div>🏛️ Live Runtime Architecture Map<div align="center"><img src="./assets/architecture.svg" alt="ProrataForge Runtime Architecture Map" width="100%"></div>🎯 Guided Story: Zero Float Drift🔒 Route Probe: Atomic & Lock💳 Security Lens: Multi-GatewayAritmatika Integer Sen MurniSeluruh nominal uang disimpan sebagai BIGINT sen (Rp150.000 = 15000000 sen) dan dihitung menggunakan intdiv() + half-up rounding tanpa pernah menyentuh tipe data float.Anti-Race Condition & IdempotenTransaksi pergantian paket mengunci baris langganan & saldo via SELECT ... FOR UPDATE serta memvalidasi Idempotency-Key ber-hash SHA-256 agar bebas tagihan ganda.Verifikasi Webhook & Audit TrailMendukung Midtrans (QRIS & verifikasi tanda tangan SHA-512 hash_equals) dan Xendit, dilengkapi append-only histori via CI4 Model Events.🌟 Fitur Utama🎯 Aritmatika Integer Sen: Semua nilai nominal uang disimpan dan dikalkulasi dalam satuan terkecil (sen integer) menggunakan algoritma half-up integer division. Tidak ada float untuk uang.🎛️ Simulator Interaktif Prorata (/playground): Visualisasi real-time pecahan hari terpakai vs sisa hari beserta kalkulator perbedaan biaya dan generator cuplikan kode (cURL, JavaScript, PHP, Python).🔄 Siklus Hidup Langganan Lengkap:Upgrade: Menagih selisih pemakaian hari tersisa secara adil dan berlaku seketika.Downgrade: Mengonversi kelebihan pembayaran paket lama menjadi saldo deposit kredit pelanggan (credit ledger) yang otomatis memotong tagihan berikutnya.🧾 Faktur Otomatis & Siap Cetak: Penerbitan faktur berurutan (INV/YYYY/MM/000001), pemotongan deposit kredit otomatis, PPN 11% (1100 basis poin), pembulatan rupiah penuh yang transparan, serta layout cetak A4/PDF.💳 Multi-Gateway Adapter:Midtrans (QRIS & verifikasi tanda tangan SHA-512 waktu-konstan).Xendit (Invoice & token callback verification).Adapter Factory Pattern yang siap diekstensi ke penyedia pembayaran lain.🛠️ CLI Pengembang (php spark):php spark billing:mock-webhook [ORDER_ID] — Simulasi webhook pelunasan lokal tanpa memerlukan ngrok.php spark billing:renew — Command perpanjangan otomatis periode langganan berkala (auto-renewal).php spark billing:dunning & php spark billing:expire — Pengingat tunggakan bertingkat (H+1, H+3, H+7) dan penutupan masa trial/suspended.🔒 Keamanan & Skalabilitas Kelas Produksi: Dukungan wajib header Idempotency-Key, rate limit sliding window 60 req/menit, transaksi database multi-tabel atomik, dan pencatatan histori append-only otomatis melalui CodeIgniter 4 Model Events.📐 Prinsip Desain & Konvensi ProrataProrataForge menghilangkan ambiguitas perhitungan hari dan kebocoran pembulatan melalui konvensi interval setengah-terbuka [tanggal_mulai, tanggal_selesai) dan prinsip kalikan dulu, bagi terakhir:total_hari    = selesai - mulai                          (kalender nyata: 28 / 29 / 30 / 31 hari)
hari_terpakai = tgl_ubah - mulai                         (dinormalkan ke pukul 00:00:00 UTC)
sisa_hari     = total_hari - hari_terpakai

kredit_sen    = half_up((harga_lama_sen * sisa_hari) / total_hari)
tagihan_sen   = half_up((harga_baru_sen * sisa_hari) / total_hari)
bersih_sen    = tagihan_sen - kredit_sen
Contoh Kasus Terverifikasi (ProrataCalculator)KasusSkenario PerubahanPeriode & Tanggal UbahSisa / Total HariKredit Paket LamaTagihan Paket BaruTagihan Bersih (Sebelum PPN)Kasus AUpgrade Basic (Rp90.000) ke Pro (Rp150.000)1 Apr – 1 Mei (Ubah 11 Apr)20 / 30 hariRp60.000,00Rp100.000,00Rp40.000,00 (Tagih)Kasus BUpgrade Tahun Kabisat (Rp290.000 ke Rp580.000)1 Feb – 1 Mar 2024 (Ubah 15 Feb)15 / 29 hariRp150.000,00Rp300.000,00Rp150.000,00 (Tagih)Kasus EDowngrade Bisnis (Rp300.000) ke Basic (Rp100.000)1 Okt – 1 Nov (Ubah 11 Okt)21 / 31 hariRp203.225,81Rp67.741,94-Rp135.483,87 (Jadi Saldo Kredit)Kasus FHarga Ganjil (Rp99.999 ke Rp149.999)1 Apr – 1 Mei (Ubah 11 Apr)20 / 30 hariRp66.666,00Rp99.999,33Rp33.333,33 (Bebas Selisih Rp6)Kasus HFebruari Non-Kabisat (Rp280.000 ke Rp560.000)1 Feb – 1 Mar 2026 (Ubah 15 Feb)14 / 28 hariRp140.000,00Rp280.000,00Rp140.000,00 (Tagih)⚡ Alur Transaksi Atomik & Concurrency DefenseSetiap eksekusi pergantian paket (SubscriptionService::gantiPaket()) berjalan di dalam satu transaksi database terisolasi dengan penguncian baris pesimistis (SELECT ... FOR UPDATE):[ Incoming Request: POST /api/langganan/{id}/ganti-paket ]
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
📁 Arsitektur Direktori & 14 Tabel InnoDBprorataforge/
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
🚀 Menjalankan via Docker (1 Perintah)Prasyarat: Sudah terinstal Docker Desktop atau Docker Engine.# 1. Clone repository
git clone https://github.com/lilabshor/prorataforge.git
cd prorataforge

# 2. Jalankan container (aplikasi + database MySQL otomatis dibuat & dimigrasi)
docker compose up -d
Buka peramban di http://localhost:8080 atau coba simulator langsung di http://localhost:8080/playground.💻 Menjalankan Manual (Lokal)Kebutuhan SistemPHP 8.2 atau 8.3+ (ext-intl, ext-mbstring, ext-mysqli, ext-curl, ext-openssl)MySQL 8.0+ (InnoDB Engine dengan dukungan utf8mb4)Composer 2.xLangkah Instalasi# 1. Pasang dependensi
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
🧪 Menjalankan Pengujian (Testing & Security Audit)Suite pengujian unit test PHPUnit telah disiapkan untuk memastikan kalkulasi finansial, pembulatan half-up, dan alur pembayaran selalu presisi:./vendor/bin/phpunit --no-coverage
Output terverifikasi:PHPUnit 10.5.66 by Sebastian Bergmann and contributors.

...............                                                   15 / 15 (100%)

Time: 00:00.024, Memory: 14.00 MB

OK (15 tests, 26 assertions)
Matriks Keamanan & Ketahanan yang DiujiKode UjiVektor PengujianMekanisme Pertahanan ProrataForgeStatusFT-19 / PT-10Idempotency Replay & Klik GandaMenyimpan sidik jari SHA-256 request di idempotency_keys; mengembalikan respons asli tanpa membuat tagihan kedua.PASSFT-20Penyalahgunaan Key pada Payload BerbedaMembandingkan hash_request; menolak otomatis dengan status HTTP 409 Conflict.PASSFT-22 / PT-13Race Condition Ganti Paket & Saldo KreditMengunci baris via SELECT ... FOR UPDATE di dalam transaksi; eksekusi berjalan serial dan saldo aman.PASSPT-01 & PT-02IDOR & Manipulasi Harga dari Clientuser_id diambil murni dari sesi server; harga diambil 100% dari tabel paket_langganan di database.PASSPT-07 & PT-08Webhook Palsu & Manipulasi Nominal BayarVerifikasi signature SHA-512 dengan hash_equals() (anti timing attack) dan pencocokan jumlah_sen.PASS🔌 Dokumentasi REST APIAplikasi menyediakan dokumentasi interaktif dan file Postman Collection yang dapat langsung diunduh:Interactive Simulator: http://localhost:8080/playgroundWeb API Docs: http://localhost:8080/docsPostman Collection: public/prorataforge-postman-collection.jsonMethodEndpointHeader WajibDeskripsi FungsionalPOST/api/kalkulator/simulasiContent-Type: application/jsonHitung simulasi prorata secara publik tanpa menulis ke database.POST/api/langganan/{id}/ganti-paketIdempotency-Key: <UUID>Eksekusi pergantian paket atomik, terbitkan faktur, atau mutasi kredit.GET/api/tagihan/{id}Session / Bearer AuthAmbil rincian faktur beserta baris item prorata, kredit, dan PPN.POST/api/tagihan/{id}/bayarSession / Bearer AuthInisiasi pembayaran ke gateway (QRIS Midtrans / Invoice Xendit).POST/webhook/pembayaranGateway SignatureEndpoint penerima notifikasi callback pelunasan dari gateway pembayaran.📄 LisensiDidistribusikan di bawah lisensi open-source MIT License.
