<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('admin/paket') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">
        &larr; Kembali ke Daftar Paket
    </a>
</div>

<div class="panel" style="max-width: 640px; margin: 0 auto 2rem;">
    <div class="panel-header">
        <h1 class="panel-title"><?= $aksi === 'tambah' ? 'Tambah Paket Baru' : 'Ubah Paket Langganan' ?></h1>
    </div>

    <form method="post" action="<?= $aksi === 'tambah' ? site_url('admin/paket') : site_url('admin/paket/' . $paket['id']) ?>">
        <?= csrf_field() ?>
        <?php if ($aksi === 'ubah'): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="field-group">
                <label class="field-label">Kode Paket</label>
                <input type="text" name="kode" class="field-input"
                       value="<?= old('kode', $paket['kode'] ?? '') ?>"
                       placeholder="misal: starter_monthly" required>
            </div>

            <div class="field-group">
                <label class="field-label">Urutan Tampilan</label>
                <input type="number" name="urutan" class="field-input"
                       value="<?= old('urutan', $paket['urutan'] ?? 0) ?>" min="0">
            </div>
        </div>

        <div class="field-group">
            <label class="field-label">Nama Paket</label>
            <input type="text" name="nama" class="field-input"
                   value="<?= old('nama', $paket['nama'] ?? '') ?>"
                   placeholder="misal: Paket Profesional" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="field-group">
                <label class="field-label">Harga (Rupiah Penuh)</label>
                <?php 
                    $hargaRupiahVal = isset($paket['harga_sen']) ? ((int)$paket['harga_sen'] / 100) : '';
                ?>
                <input type="number" name="harga_rupiah" class="field-input"
                       value="<?= old('harga_rupiah', $hargaRupiahVal) ?>"
                       placeholder="misal: 150000" step="any" required>
                <span class="field-hint">Dikonversi otomatis ke sen integer di database.</span>
            </div>

            <div class="field-group">
                <label class="field-label">Interval Durasi (Bulan)</label>
                <input type="number" name="interval_bulan" class="field-input"
                       value="<?= old('interval_bulan', $paket['interval_bulan'] ?? 1) ?>"
                       min="1" required>
            </div>
        </div>

        <div class="field-group">
            <label class="field-label">Fitur JSON (Opsional)</label>
            <textarea name="fitur_json" class="field-input mono" rows="3"
                      placeholder='["Export PDF", "Unlimited Invoices"]'><?= old('fitur_json', $paket['fitur_json'] ?? '') ?></textarea>
        </div>

        <div class="field-group">
            <label class="field-label">Batas Kuota JSON (Opsional)</label>
            <textarea name="batas_json" class="field-input mono" rows="2"
                      placeholder='{"max_members": 10}'><?= old('batas_json', $paket['batas_json'] ?? '') ?></textarea>
        </div>

        <div class="field-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
            <input type="checkbox" name="is_aktif" id="is_aktif" value="1"
                   <?= old('is_aktif', $paket['is_aktif'] ?? 1) ? 'checked' : '' ?> style="width: 1rem; height: 1rem;">
            <label for="is_aktif" style="cursor: pointer; font-size: 0.875rem; font-weight: 500; color: var(--text-heading);">
                Paket aktif dan dapat dipilih oleh pelanggan
            </label>
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn-ui btn-ui-dark" style="width: 100%;">
                <?= $aksi === 'tambah' ? 'Simpan Paket Baru' : 'Perbarui Paket' ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
