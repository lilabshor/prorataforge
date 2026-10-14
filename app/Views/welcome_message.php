<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div style="padding: 2.5rem 0 3.5rem; text-align: center; border-bottom: 1px solid var(--border-color); margin-bottom: 2.5rem;">
    <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.03em;">
        Sistem Manajemen Billing &amp; Langganan SaaS
    </h1>
    <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 620px; margin: 0.75rem auto 2rem;">
        Fondasi penagihan langganan dengan kalkulasi prorata integer sen, pengelolaan saldo kredit, penerbitan faktur atomik, dan integrasi gateway pembayaran.
    </p>

    <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
        <a href="<?= site_url('playground') ?>" class="btn-ui btn-ui-dark" style="padding: 0.75rem 1.5rem; font-size: 0.9375rem;">
            Buka Simulator Prorata
        </a>
        <a href="<?= site_url('demo/user') ?>" class="btn-ui btn-ui-primary" style="padding: 0.75rem 1.5rem; font-size: 0.9375rem;">
            Masuk sebagai Pelanggan
        </a>
        <a href="<?= site_url('demo/admin') ?>" class="btn-ui btn-ui-secondary" style="padding: 0.75rem 1.5rem; font-size: 0.9375rem;">
            Masuk sebagai Administrator
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    <div class="panel">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-heading); margin-bottom: 0.5rem;">
            Kalkulasi Prorata Presisi
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
            Menghitung sisa hari paket berjalan secara akurat. Penyesuaian upgrade menagih selisih pemakaian, sedangkan downgrade mengonversi kelebihan biaya menjadi saldo kredit pelanggan.
        </p>
        <div style="margin-top: 1.25rem;">
            <a href="<?= site_url('demo/user') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Buka Halaman Langganan</a>
        </div>
    </div>

    <div class="panel">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-heading); margin-bottom: 0.5rem;">
            Faktur &amp; Saldo Kredit
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
            Penerbitan faktur tagihan berurutan bulanan, pemotongan otomatis dari deposit saldo kredit, pengenaan PPN 11%, serta pembulatan nominal ke rupiah penuh.
        </p>
        <div style="margin-top: 1.25rem;">
            <a href="<?= site_url('demo/user') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Lihat Daftar Faktur</a>
        </div>
    </div>

    <div class="panel">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-heading); margin-bottom: 0.5rem;">
            Analitik MRR &amp; ARR
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;">
            Monitoring finansial bisnis berbasis metrik Monthly Recurring Revenue, proyeksi Annual Run Rate tahunan, serta sebaran porsi pendapatan berdasarkan paket aktif.
        </p>
        <div style="margin-top: 1.25rem;">
            <a href="<?= site_url('demo/admin') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Buka Laporan Admin</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
