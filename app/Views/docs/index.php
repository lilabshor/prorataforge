<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.02em;">
            Dokumentasi REST API ProrataForge
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9375rem; margin-top: 0.25rem;">
            Panduan integrasi penagihan langganan untuk aplikasi front-end (React, Vue, Flutter, Next.js) atau microservice.
        </p>
    </div>
    <div>
        <a href="<?= base_url('prorataforge-postman-collection.json') ?>" download class="btn-ui btn-ui-dark btn-ui-sm">
            Unduh Postman Collection (.json)
        </a>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Standar Parameter &amp; Format Uang</h2>
    </div>
    <div style="font-size: 0.875rem; color: var(--text-body); line-height: 1.6;">
        <ul style="margin-left: 1.25rem;">
            <li><strong>Mata Uang</strong>: Nilai nominal uang dalam API selalu menggunakan satuan integer terkecil (sen). <code>Rp 10.000 = 1000000 sen</code> (dikalikan 100).</li>
            <li><strong>Idempotency</strong>: Endpoint pergantian paket mewajibkan header <code>Idempotency-Key</code> untuk mencegah tagihan ganda saat terjadi kegagalan jaringan atau retry request.</li>
            <li><strong>Rate Limiting</strong>: Setiap klien dibatasi maksimal 60 permintaan per menit per rute. Melebihi batas ini akan mengembalikan status <code>429 Too Many Requests</code>.</li>
        </ul>
    </div>
</div>

<!-- Endpoint 1 -->
<div class="panel">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span class="status-badge" style="background-color: #ecfdf5; color: #047857; font-weight: 700;">POST</span>
        <span class="mono" style="font-weight: 600; color: var(--text-heading);">/api/kalkulator/simulasi</span>
    </div>
    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
        Menghitung simulasi prorata secara publik tanpa mengubah status database.
    </p>

    <div style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-heading);">Request Body (JSON):</div>
    <pre class="mono" style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 0.875rem; font-size: 0.8125rem; overflow-x: auto; margin-bottom: 1rem;">{
  "harga_lama_sen": 10000000,
  "harga_baru_sen": 20000000,
  "tgl_mulai": "2026-10-01",
  "tgl_selesai": "2026-10-31",
  "tgl_ubah": "2026-10-16"
}</pre>

    <div style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-heading);">Response Body (200 OK):</div>
    <pre class="mono" style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 0.875rem; font-size: 0.8125rem; overflow-x: auto;">{
  "status": "sukses",
  "data": {
    "total_hari": 30,
    "hari_terpakai": 15,
    "sisa_hari": 15,
    "kredit_sen": 5000000,
    "tagihan_baru_sen": 10000000,
    "bersih_sen": 5000000,
    "pajak_sen": 550000,
    "total_bayar_sen": 5550000,
    "jenis_aksi": "upgrade"
  }
}</pre>
</div>

<!-- Endpoint 2 -->
<div class="panel">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span class="status-badge" style="background-color: #ecfdf5; color: #047857; font-weight: 700;">POST</span>
        <span class="mono" style="font-weight: 600; color: var(--text-heading);">/api/langganan/{id}/ganti-paket</span>
    </div>
    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
        Mengeksekusi proses perubahan paket langganan secara atomik.
    </p>

    <div style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-heading);">Headers:</div>
    <pre class="mono" style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 0.5rem 0.875rem; font-size: 0.8125rem; margin-bottom: 1rem;">Idempotency-Key: unik-uuid-v4-string
Content-Type: application/json</pre>

    <div style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-heading);">Request Body (JSON):</div>
    <pre class="mono" style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 0.875rem; font-size: 0.8125rem; overflow-x: auto;">{
  "paket_baru_id": 2,
  "tgl_perubahan": "2026-10-15",
  "kode_kupon": "HEMAT10"
}</pre>
</div>

<!-- Endpoint 3 -->
<div class="panel">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
        <span class="status-badge" style="background-color: #ecfdf5; color: #047857; font-weight: 700;">POST</span>
        <span class="mono" style="font-weight: 600; color: var(--text-heading);">/api/tagihan/{id}/bayar</span>
    </div>
    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
        Mempersiapkan transaksi gateway pembayaran Midtrans QRIS untuk faktur yang berstatus <code>terbit</code>.
    </p>
</div>
<?= $this->endSection() ?>
