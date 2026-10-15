<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div style="padding: 2.5rem 0 3rem; text-align: center; border-bottom: 1px solid var(--border-color); margin-bottom: 2.5rem;">
    <h1 style="font-size: 2.35rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.03em; line-height: 1.2;">
        Sistem Manajemen Billing &amp; Langganan SaaS
    </h1>
    <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 650px; margin: 0.85rem auto 2rem; line-height: 1.6;">
        Fondasi penagihan langganan dengan kalkulasi prorata integer sen presisi tinggi, pengelolaan saldo kredit terintegrasi, penerbitan faktur atomik, dan integrasi multi-gateway pembayaran.
    </p>

    <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2.5rem;">
        <a href="<?= site_url('playground') ?>" class="btn-ui btn-ui-primary" style="padding: 0.75rem 1.75rem; font-size: 0.9375rem; font-weight: 600; gap: 0.4rem;">
            <span>Buka Simulator Prorata</span>
            <span>&rarr;</span>
        </a>
        <a href="<?= site_url('docs') ?>" class="btn-ui btn-ui-dark" style="padding: 0.75rem 1.5rem; font-size: 0.9375rem;">
            Dokumentasi REST API
        </a>
        <a href="<?= site_url('langganan') ?>" class="btn-ui btn-ui-secondary" style="padding: 0.75rem 1.5rem; font-size: 0.9375rem;">
            Panel Langganan
        </a>
    </div>

    <!-- Highlight Metrics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; text-align: left; max-width: 980px; margin: 0 auto;">
        <div style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.15rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: var(--success);"></span>
                <span style="font-size: 0.8125rem; font-weight: 700; color: var(--text-heading);">Zero Float Drift</span>
            </div>
            <p style="font-size: 0.775rem; color: var(--text-muted); line-height: 1.45;">
                Aritmatika integer sen murni dengan proteksi overflow 64-bit PHP.
            </p>
        </div>

        <div style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.15rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: var(--primary);"></span>
                <span style="font-size: 0.8125rem; font-weight: 700; color: var(--text-heading);">Multi-Gateway</span>
            </div>
            <p style="font-size: 0.775rem; color: var(--text-muted); line-height: 1.45;">
                Adapter Midtrans (QRIS) &amp; Xendit siap diverifikasi tanda tangan SHA-512.
            </p>
        </div>

        <div style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.15rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: var(--warning);"></span>
                <span style="font-size: 0.8125rem; font-weight: 700; color: var(--text-heading);">Faktur Atomik</span>
            </div>
            <p style="font-size: 0.775rem; color: var(--text-muted); line-height: 1.45;">
                Penomoran urut bebas race-condition, PPN 11%, dan pembulatan rupiah.
            </p>
        </div>

        <div style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.15rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #8b5cf6;"></span>
                <span style="font-size: 0.8125rem; font-weight: 700; color: var(--text-heading);">Idempotent API</span>
            </div>
            <p style="font-size: 0.775rem; color: var(--text-muted); line-height: 1.45;">
                Dukungan header Idempotency-Key dan rate limiting 60 req/menit.
            </p>
        </div>
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
            <a href="<?= site_url('langganan') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Buka Halaman Langganan</a>
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
            <a href="<?= site_url('tagihan') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Lihat Daftar Faktur</a>
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
            <a href="<?= site_url('admin/laporan/mrr') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Buka Laporan Admin</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
