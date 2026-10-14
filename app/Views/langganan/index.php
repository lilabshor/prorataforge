<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="panel">
    <div class="panel-header">
        <div>
            <h1 class="panel-title">Langganan Saya</h1>
            <p class="panel-subtitle">Daftar paket langganan aktif, siklus penagihan, dan pengelolaan layanan.</p>
        </div>
    </div>

    <?php if (empty($langgananList)): ?>
        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
            <p style="font-weight: 500; margin-bottom: 0.25rem;">Belum ada data langganan.</p>
            <p style="font-size: 0.8125rem;">Langganan yang Anda buat akan muncul di tabel ini.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paket</th>
                        <th>Status</th>
                        <th>Periode Aktif</th>
                        <th>Perpanjangan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $paketModel = model(\App\Models\PaketModel::class);
                    foreach ($langgananList as $lang): 
                        $paket = $paketModel->find($lang['paket_id']);
                    ?>
                        <tr>
                            <td class="mono">#<?= esc($lang['id']) ?></td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-heading);"><?= esc($paket['nama'] ?? 'Paket #' . $lang['paket_id']) ?></div>
                                <?php if ($paket): ?>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);" class="mono">
                                        <?= esc(formatRupiah((int) $paket['harga_sen'])) ?> / <?= (int) $paket['interval_bulan'] ?> bln
                                    </div>
                                <?php endif ?>
                            </td>
                            <td>
                                <span class="status-badge <?= esc($lang['status']) ?>">
                                    <?= strtoupper(esc($lang['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div><?= esc($lang['periode_mulai']) ?> &mdash; <?= esc($lang['periode_selesai']) ?></div>
                                <?php if ($lang['status'] === 'trialing' && ! empty($lang['trial_selesai'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--warning);">
                                        Trial s/d: <?= esc($lang['trial_selesai']) ?>
                                    </div>
                                <?php endif ?>
                            </td>
                            <td>
                                <?= $lang['auto_perpanjang'] 
                                    ? '<span style="color: var(--success); font-weight: 500;">Otomatis</span>' 
                                    : '<span style="color: var(--text-muted);">Manual</span>' ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <a href="<?= site_url('langganan/' . $lang['id']) ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
                                        Rincian
                                    </a>
                                    <?php if (in_array($lang['status'], ['active', 'trialing'], true)): ?>
                                        <a href="<?= site_url('langganan/' . $lang['id'] . '/ganti-paket') ?>" class="btn-ui btn-ui-primary btn-ui-sm">
                                            Ganti Paket
                                        </a>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
