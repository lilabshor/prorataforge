<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
    <a href="<?= site_url('tagihan') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
        &larr; Kembali ke Daftar Faktur
    </a>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" onclick="window.print()" class="btn-ui btn-ui-secondary btn-ui-sm">
            Cetak Faktur (Print / PDF)
        </button>
        <span class="status-badge <?= esc($tagihan['status']) ?>">
            STATUS: <?= strtoupper(esc($tagihan['status'])) ?>
        </span>
    </div>
</div>

<style>
@media print {
    .top-navbar, .btn-ui, #btnBayar, #bayarResponse {
        display: none !important;
    }
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    .page-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .panel {
        border: 1px solid #cccccc !important;
        box-shadow: none !important;
    }
}
</style>

<div class="panel" id="invoiceCard">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
        <div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.02em;">FAKTUR PENAGIHAN</div>
            <div class="mono" style="font-size: 1rem; color: var(--primary); font-weight: 600; margin-top: 0.2rem;">
                <?= esc($tagihan['nomor']) ?>
            </div>
            <div style="font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.2rem;">
                Jenis: <span style="text-transform: capitalize; color: var(--text-heading); font-weight: 500;"><?= esc($tagihan['jenis']) ?></span>
            </div>
        </div>

        <div style="text-align: right; font-size: 0.8125rem;">
            <div style="color: var(--text-muted);">Tanggal Terbit: <span style="color: var(--text-heading); font-weight: 500;"><?= esc($tagihan['tgl_terbit']) ?></span></div>
            <div style="color: var(--text-muted); margin-top: 0.2rem;">Jatuh Tempo: <span style="color: var(--text-heading); font-weight: 500;"><?= esc($tagihan['tgl_jatuh_tempo']) ?></span></div>
            <?php if (! empty($tagihan['dibayar_pada'])): ?>
                <div style="color: var(--success); margin-top: 0.2rem; font-weight: 500;">Lunas: <?= esc($tagihan['dibayar_pada']) ?></div>
            <?php endif ?>
        </div>
    </div>

    <div style="margin-bottom: 2rem;">
        <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-heading);">Rincian Pos Tagihan</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Deskripsi Item</th>
                        <th style="text-align: center;">Jumlah</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500; color: var(--text-heading);"><?= esc($it['keterangan']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);" class="mono">
                                    Pos: <?= esc($it['jenis']) ?>
                                </div>
                            </td>
                            <td style="text-align: center;" class="mono"><?= (int) $it['jumlah'] ?></td>
                            <td style="text-align: right;" class="mono" style="font-weight: 500;">
                                <?= esc(formatRupiah((int) $it['subtotal_sen'])) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end;">
        <div style="width: 100%; max-width: 360px; background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.125rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8125rem;">
                <span style="color: var(--text-muted);">Subtotal</span>
                <span class="mono"><?= esc(formatRupiah((int) $tagihan['subtotal_sen'])) ?></span>
            </div>

            <?php if ((int) $tagihan['kredit_dipakai_sen'] > 0): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8125rem; color: var(--success);">
                    <span>Saldo Kredit Dipakai</span>
                    <span class="mono">-<?= esc(formatRupiah((int) $tagihan['kredit_dipakai_sen'])) ?></span>
                </div>
            <?php endif ?>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8125rem;">
                <span style="color: var(--text-muted);">PPN (<?= (float) $tagihan['pajak_persen'] ?>%)</span>
                <span class="mono"><?= esc(formatRupiah((int) $tagihan['pajak_sen'])) ?></span>
            </div>

            <?php if ((int) $tagihan['penyesuaian_sen'] !== 0): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8125rem; color: var(--text-muted);">
                    <span>Pembulatan Rupiah</span>
                    <span class="mono"><?= ((int) $tagihan['penyesuaian_sen'] > 0 ? '+' : '') . esc(formatRupiah((int) $tagihan['penyesuaian_sen'])) ?></span>
                </div>
            <?php endif ?>

            <div style="display: flex; justify-content: space-between; padding-top: 0.6rem; border-top: 1px solid var(--border-color); margin-top: 0.4rem; font-size: 1rem; font-weight: 700; color: var(--text-heading);">
                <span>Total Tagihan</span>
                <span class="mono"><?= esc(formatRupiah((int) $tagihan['total_sen'])) ?></span>
            </div>
        </div>
    </div>

    <?php if ($tagihan['status'] === 'terbit' && (int) $tagihan['total_sen'] > 0): ?>
        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color); text-align: right;">
            <button id="btnBayar" class="btn-ui btn-ui-primary" onclick="prosesBayar(<?= (int) $tagihan['id'] ?>)">
                Bayar Sekarang via Midtrans QRIS
            </button>
            <div id="bayarResponse" style="margin-top: 0.875rem; font-size: 0.8125rem;"></div>
        </div>
    <?php elseif ($tagihan['status'] === 'gratis'): ?>
        <div style="margin-top: 1.5rem; padding: 0.875rem; border-radius: 6px; background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; text-align: center; font-size: 0.8125rem;">
            Faktur ini ditutup tanpa tagihan berbayar (dicakup penuh oleh saldo kredit).
        </div>
    <?php elseif ($tagihan['status'] === 'dibayar'): ?>
        <div style="margin-top: 1.5rem; padding: 0.875rem; border-radius: 6px; background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; text-align: center; font-size: 0.8125rem;">
            Faktur tagihan ini telah lunas dibayar.
        </div>
    <?php endif ?>
</div>

<script>
async function prosesBayar(tagihanId) {
    const btn = document.getElementById('btnBayar');
    const respDiv = document.getElementById('bayarResponse');
    btn.disabled = true;
    btn.innerText = 'Menghubungkan Payment Gateway...';

    try {
        const response = await fetch(`<?= site_url('api/tagihan/') ?>${tagihanId}/bayar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const res = await response.json();
        if (response.ok) {
            respDiv.innerHTML = `
                <div class="alert-box success" style="display: block; text-align: left;">
                    <strong>Order ID: ${res.order_id}</strong><br>
                    Pembayaran telah siap diproses melalui gateway Midtrans.<br>
                    Nominal: Rp${(res.amount / 100).toLocaleString('id-ID')}
                </div>
            `;
        } else {
            respDiv.innerHTML = `<div class="alert-box error">${res.message || 'Gagal memproses pembayaran.'}</div>`;
            btn.disabled = false;
            btn.innerText = 'Bayar Sekarang via Midtrans QRIS';
        }
    } catch (e) {
        respDiv.innerHTML = `<div class="alert-box error">Koneksi ke gateway gagal.</div>`;
        btn.disabled = false;
        btn.innerText = 'Bayar Sekarang via Midtrans QRIS';
    }
}
</script>
<?= $this->endSection() ?>
