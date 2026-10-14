<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php 
$paket = model(\App\Models\PaketModel::class)->find($langganan['paket_id']);
?>
<div style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
    <a href="<?= site_url('langganan') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
        &larr; Kembali ke Daftar Langganan
    </a>
    <?php if (in_array($langganan['status'], ['active', 'trialing'], true)): ?>
        <a href="<?= site_url('langganan/' . $langganan['id'] . '/ganti-paket') ?>" class="btn-ui btn-ui-primary btn-ui-sm">
            Ganti Paket Langganan
        </a>
    <?php endif ?>
</div>

<div class="panel">
    <div class="panel-header">
        <div>
            <h1 class="panel-title">Langganan #<?= esc($langganan['id']) ?></h1>
            <p class="panel-subtitle">Informasi status dan parameter paket langganan saat ini.</p>
        </div>
        <div>
            <span class="status-badge <?= esc($langganan['status']) ?>">
                <?= strtoupper(esc($langganan['status'])) ?>
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 0.5rem;">
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Paket Aktif</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-heading); margin-top: 0.25rem;">
                <?= esc($paket['nama'] ?? 'Paket #' . $langganan['paket_id']) ?>
            </div>
            <div class="mono" style="font-size: 0.8125rem; color: var(--text-muted);">
                <?= esc(formatRupiah((int) ($paket['harga_sen'] ?? 0))) ?> / <?= (int) ($paket['interval_bulan'] ?? 1) ?> bln
            </div>
        </div>

        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Periode Berjalan</div>
            <div style="font-size: 0.9375rem; font-weight: 600; color: var(--text-heading); margin-top: 0.25rem;">
                <?= esc($langganan['periode_mulai']) ?> s/d <?= esc($langganan['periode_selesai']) ?>
            </div>
        </div>

        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Perpanjangan</div>
            <div style="font-size: 0.9375rem; font-weight: 600; margin-top: 0.25rem; color: <?= $langganan['auto_perpanjang'] ? 'var(--success)' : 'var(--text-muted)' ?>;">
                <?= $langganan['auto_perpanjang'] ? 'Otomatis' : 'Manual' ?>
            </div>
        </div>

        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Versi Perubahan</div>
            <div style="font-size: 0.9375rem; font-weight: 600; margin-top: 0.25rem;" class="mono">
                v<?= esc($langganan['versi']) ?>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Riwayat Faktur Tagihan</h2>
    </div>

    <?php if (empty($tagihanList)): ?>
        <p style="color: var(--text-muted); padding: 1rem 0; font-size: 0.875rem;">Belum ada riwayat tagihan pada langganan ini.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nomor Faktur</th>
                        <th>Jenis</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tgl Terbit</th>
                        <th>Jatuh Tempo</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tagihanList as $t): ?>
                        <tr>
                            <td class="mono" style="font-weight: 600;"><?= esc($t['nomor']) ?></td>
                            <td style="text-transform: capitalize;"><?= esc($t['jenis']) ?></td>
                            <td class="mono" style="font-weight: 600;">
                                <?= esc(formatRupiah((int) $t['total_sen'])) ?>
                            </td>
                            <td>
                                <span class="status-badge <?= esc($t['status']) ?>">
                                    <?= strtoupper(esc($t['status'])) ?>
                                </span>
                            </td>
                            <td><?= esc($t['tgl_terbit']) ?></td>
                            <td><?= esc($t['tgl_jatuh_tempo']) ?></td>
                            <td style="text-align: right;">
                                <a href="<?= site_url('tagihan/' . $t['id']) ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
                                    Buka Faktur
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
