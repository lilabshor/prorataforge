<!-- app/Views/langganan/ganti_paket.php (potongan) -->
<form method="post"
      action="<?= site_url('langganan/' . $lang['id'] . '/ganti-paket') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="idempotency_key"
           value="<?= esc($idemKey) ?>">
    <label>Paket baru
        <select name="paket_baru_id" required>
            <?php foreach ($paketList as $p): ?>
                <option value="<?= (int) $p['id'] ?>">
                    <?= esc($p['nama']) ?> -
                    <?= esc(formatRupiah((int) $p['harga_sen'])) ?>/bulan
                </option>
            <?php endforeach ?>
        </select>
    </label>
    <label>Tanggal efektif
        <input type="date" name="tgl_perubahan"
               value="<?= date('Y-m-d') ?>" required>
    </label>
    <button type="submit">Hitung &amp; Ganti Paket</button>
</form>
