<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php 
$paketAktif = model(\App\Models\PaketModel::class)->find($lang['paket_id']);
?>
<div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('langganan/' . $lang['id']) ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
        &larr; Kembali ke Rincian Langganan
    </a>
</div>

<div class="panel" style="max-width: 640px; margin: 0 auto 2rem;">
    <div class="panel-header">
        <div>
            <h1 class="panel-title">Ganti Paket Langganan</h1>
            <p class="panel-subtitle">Penyesuaian prorata akan dihitung otomatis sesuai sisa hari periode berjalan.</p>
        </div>
    </div>

    <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Paket Saat Ini</div>
        <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-heading); margin-top: 0.2rem;">
            <?= esc($paketAktif['nama'] ?? 'Paket #' . $lang['paket_id']) ?>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.2rem;">
            Periode Berjalan: <span style="color: var(--text-heading); font-weight: 500;"><?= esc($lang['periode_mulai']) ?></span> s/d <span style="color: var(--text-heading); font-weight: 500;"><?= esc($lang['periode_selesai']) ?></span>
        </div>
    </div>

    <form method="post" action="<?= site_url('langganan/' . $lang['id'] . '/ganti-paket') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="idempotency_key" value="<?= esc($idemKey) ?>">

        <div class="field-group">
            <label class="field-label">Pilih Paket Baru</label>
            <select name="paket_baru_id" class="field-input" required>
                <option value="">-- Pilih Paket Baru --</option>
                <?php foreach ($paketList as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= old('paket_baru_id') == $p['id'] ? 'selected' : '' ?>>
                        <?= esc($p['nama']) ?> &mdash; <?= esc(formatRupiah((int) $p['harga_sen'])) ?> / <?= (int) $p['interval_bulan'] ?> bulan
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="field-group">
            <label class="field-label">Tanggal Efektif Perubahan</label>
            <input type="date" name="tgl_perubahan" class="field-input"
                   value="<?= old('tgl_perubahan', date('Y-m-d')) ?>"
                   min="<?= esc($lang['periode_mulai']) ?>"
                   max="<?= esc(date('Y-m-d', strtotime($lang['periode_selesai'] . ' -1 day'))) ?>"
                   required>
            <span class="field-hint">
                Harus berada dalam rentang periode berjalan (<?= esc($lang['periode_mulai']) ?> s/d <?= esc(date('Y-m-d', strtotime($lang['periode_selesai'] . ' -1 day'))) ?>).
            </span>
        </div>

        <div class="field-group">
            <label class="field-label">Kode Kupon Diskon (Opsional)</label>
            <input type="text" name="kode_kupon" class="field-input"
                   value="<?= old('kode_kupon') ?>"
                   placeholder="Masukkan kode promo (misal: HEMAT10)"
                   style="text-transform: uppercase;">
        </div>

        <div style="background-color: #f1f5f9; border-radius: 6px; padding: 0.875rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #475569;">
            <strong>Catatan Prorata:</strong>
            <ul style="margin-left: 1.25rem; margin-top: 0.25rem;">
                <li>Upgrade: Anda hanya membayar selisih biaya untuk sisa hari aktif ditambah PPN 11%.</li>
                <li>Downgrade: Kelebihan biaya sisa hari paket lama otomatis dialokasikan menjadi saldo kredit akun.</li>
            </ul>
        </div>

        <button type="submit" class="btn-ui btn-ui-primary" style="width: 100%;">
            Hitung Prorata &amp; Konfirmasi
        </button>
    </form>
</div>
<?= $this->endSection() ?>
