<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="panel">
    <div class="panel-header">
        <div>
            <h1 class="panel-title">Laporan MRR &amp; Kinerja Langganan</h1>
            <p class="panel-subtitle">Pendapatan berulang bulanan (Monthly Recurring Revenue) dan metrik retensi pelanggan.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
            <div style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                Monthly Recurring Revenue
            </div>
            <div class="mono" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); margin-top: 0.4rem;">
                <?= esc(formatRupiah((int) $totalMrrSen)) ?>
            </div>
            <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
                Normalisasi pendapatan bulanan
            </div>
        </div>

        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
            <div style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                Annual Run Rate
            </div>
            <div class="mono" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); margin-top: 0.4rem;">
                <?= esc(formatRupiah((int) $totalArrSen)) ?>
            </div>
            <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
                Proyeksi tahunan (MRR &times; 12)
            </div>
        </div>

        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
            <div style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                Pelanggan Aktif
            </div>
            <div class="mono" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); margin-top: 0.4rem;">
                <?= (int) $totalAktif ?>
            </div>
            <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
                + <?= (int) $totalTrialing ?> dalam masa uji coba
            </div>
        </div>

        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
            <div style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                Pelanggan Berhenti (Churn)
            </div>
            <div class="mono" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading); margin-top: 0.4rem;">
                <?= (int) $churnedCount ?>
            </div>
            <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
                Total akun dibatalkan
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Distribusi Pendapatan per Paket</h2>
    </div>

    <?php if (empty($paketBreakdown)): ?>
        <p style="color: var(--text-muted); padding: 1rem 0; font-size: 0.875rem;">Belum ada langganan aktif untuk dihitung sebaran pendapatannya.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Paket</th>
                        <th>Pelanggan Aktif</th>
                        <th>Kontribusi MRR</th>
                        <th style="width: 250px;">Porsi (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paketBreakdown as $b): 
                        $porsi = $totalMrrSen > 0 ? round(($b['total_sen'] / $totalMrrSen) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-heading);"><?= esc($b['nama']) ?></td>
                            <td class="mono"><?= (int) $b['jumlah_user'] ?> pelanggan</td>
                            <td class="mono" style="font-weight: 600;">
                                <?= esc(formatRupiah((int) $b['total_sen'])) ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="flex: 1; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?= $porsi ?>%; height: 100%; background-color: var(--primary); border-radius: 3px;"></div>
                                    </div>
                                    <span class="mono" style="font-size: 0.8125rem; font-weight: 600; width: 45px; text-align: right;"><?= $porsi ?>%</span>
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
