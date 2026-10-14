<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="panel">
    <div class="panel-header">
        <div>
            <h1 class="panel-title">Faktur Tagihan</h1>
            <p class="panel-subtitle">Daftar tagihan perpanjangan, biaya prorata, dan status pelunasan.</p>
        </div>
    </div>

    <?php if (empty($tagihanList)): ?>
        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
            <p style="font-weight: 500; margin-bottom: 0.25rem;">Belum ada data faktur.</p>
            <p style="font-size: 0.8125rem;">Faktur tagihan baru akan tercatat otomatis saat perpanjangan atau perubahan paket dilakukan.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nomor Faktur</th>
                        <th>Jenis</th>
                        <th>Subtotal</th>
                        <th>Kredit Saldo</th>
                        <th>PPN 11%</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tagihanList as $t): ?>
                        <tr>
                            <td class="mono" style="font-weight: 600;"><?= esc($t['nomor']) ?></td>
                            <td style="text-transform: capitalize;"><?= esc($t['jenis']) ?></td>
                            <td class="mono"><?= esc(formatRupiah((int) $t['subtotal_sen'])) ?></td>
                            <td class="mono" style="color: <?= (int) $t['kredit_dipakai_sen'] > 0 ? 'var(--success)' : 'inherit' ?>;">
                                <?= (int) $t['kredit_dipakai_sen'] > 0 ? '-' . esc(formatRupiah((int) $t['kredit_dipakai_sen'])) : 'Rp0,00' ?>
                            </td>
                            <td class="mono"><?= esc(formatRupiah((int) $t['pajak_sen'])) ?></td>
                            <td class="mono" style="font-weight: 700; color: var(--text-heading);">
                                <?= esc(formatRupiah((int) $t['total_sen'])) ?>
                            </td>
                            <td>
                                <span class="status-badge <?= esc($t['status']) ?>">
                                    <?= strtoupper(esc($t['status'])) ?>
                                </span>
                            </td>
                            <td><?= esc($t['tgl_jatuh_tempo']) ?></td>
                            <td style="text-align: right;">
                                <a href="<?= site_url('tagihan/' . $t['id']) ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
                                    Lihat
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
