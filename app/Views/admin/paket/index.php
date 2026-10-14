<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="panel">
    <div class="panel-header">
        <div>
            <h1 class="panel-title">Kelola Paket Langganan</h1>
            <p class="panel-subtitle">Konfigurasi paket SaaS, penetapan harga (sen), dan siklus penagihan.</p>
        </div>
        <div>
            <a href="<?= site_url('admin/paket/new') ?>" class="btn-ui btn-ui-dark btn-ui-sm">
                Tambah Paket Baru
            </a>
        </div>
    </div>

    <?php if (empty($paketList)): ?>
        <p style="text-align: center; color: var(--text-muted); padding: 2rem; font-size: 0.875rem;">Belum ada paket langganan terdaftar.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Kode</th>
                        <th>Nama Paket</th>
                        <th>Harga</th>
                        <th>Interval</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paketList as $p): ?>
                        <tr>
                            <td class="mono"><?= (int) $p['urutan'] ?></td>
                            <td class="mono" style="font-weight: 600;"><?= esc($p['kode']) ?></td>
                            <td style="font-weight: 500; color: var(--text-heading);"><?= esc($p['nama']) ?></td>
                            <td class="mono" style="font-weight: 600;">
                                <?= esc(formatRupiah((int) $p['harga_sen'])) ?>
                            </td>
                            <td><?= (int) $p['interval_bulan'] ?> Bulan</td>
                            <td>
                                <span class="status-badge <?= $p['is_aktif'] ? 'active' : 'canceled' ?>">
                                    <?= $p['is_aktif'] ? 'AKTIF' : 'NONAKTIF' ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <a href="<?= site_url('admin/paket/' . $p['id'] . '/edit') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
                                        Edit
                                    </a>
                                    <form method="post" action="<?= site_url('admin/paket/' . $p['id']) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan paket ini?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn-ui btn-ui-danger btn-ui-sm">Hapus</button>
                                    </form>
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
