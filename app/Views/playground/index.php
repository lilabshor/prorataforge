<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.02em;">
        Simulator Interaktif Prorata
    </h1>
    <p style="color: var(--text-muted); font-size: 0.9375rem; margin-top: 0.25rem;">
        Visualisasi langsung bagaimana algoritma integer sen menghitung sisa hari, kredit pengembalian, dan tagihan pergantian paket.
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; align-items: start;">
    <!-- Panel Input Pengaturan -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Parameter Simulasi</h2>
        </div>

        <div class="field-group">
            <label class="field-label">Paket Langganan Saat Ini (Lama)</label>
            <select id="selectPaketLama" class="field-input" onchange="sinkronHargaLama()">
                <?php foreach ($paketList as $p): ?>
                    <option value="<?= (int) $p['harga_sen'] ?>" <?= $p['kode'] === 'starter_monthly' ? 'selected' : '' ?>>
                        <?= esc($p['nama']) ?> (<?= esc(formatRupiah((int) $p['harga_sen'])) ?>)
                    </option>
                <?php endforeach ?>
                <option value="custom">Nominal Kustom...</option>
            </select>
        </div>

        <div class="field-group" id="groupCustomLama" style="display: none;">
            <label class="field-label">Harga Paket Lama (Rupiah)</label>
            <input type="number" id="inputCustomLama" class="field-input" value="100000" oninput="hitungSimulasi()">
        </div>

        <div class="field-group">
            <label class="field-label">Paket Tujuan (Baru)</label>
            <select id="selectPaketBaru" class="field-input" onchange="sinkronHargaBaru()">
                <?php foreach ($paketList as $p): ?>
                    <option value="<?= (int) $p['harga_sen'] ?>" <?= $p['kode'] === 'pro_monthly' ? 'selected' : '' ?>>
                        <?= esc($p['nama']) ?> (<?= esc(formatRupiah((int) $p['harga_sen'])) ?>)
                    </option>
                <?php endforeach ?>
                <option value="custom">Nominal Kustom...</option>
            </select>
        </div>

        <div class="field-group" id="groupCustomBaru" style="display: none;">
            <label class="field-label">Harga Paket Baru (Rupiah)</label>
            <input type="number" id="inputCustomBaru" class="field-input" value="200000" oninput="hitungSimulasi()">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="field-group">
                <label class="field-label">Mulai Periode</label>
                <input type="date" id="tglMulai" class="field-input" value="<?= date('Y-m-01') ?>" onchange="updateBatasTanggal()">
            </div>
            <div class="field-group">
                <label class="field-label">Selesai Periode</label>
                <input type="date" id="tglSelesai" class="field-input" value="<?= date('Y-m-t') ?>" onchange="updateBatasTanggal()">
            </div>
        </div>

        <div class="field-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                <label class="field-label" style="margin-bottom: 0;">Tanggal Perubahan Paket</label>
                <span class="mono" id="labelTglUbah" style="font-weight: 600; color: var(--primary); font-size: 0.8125rem;">
                    <?= date('Y-m-15') ?>
                </span>
            </div>
            <input type="date" id="tglUbah" class="field-input" value="<?= date('Y-m-15') ?>" onchange="hitungSimulasi()">
        </div>
    </div>

    <!-- Panel Hasil & Visualisasi -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Hasil Kalkulasi Prorata</h2>
            <span id="badgeAksi" class="status-badge active">UPGRADE</span>
        </div>

        <!-- Visual Timeline Bar -->
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.4rem;">
                <span style="color: var(--text-muted);">Hari Terpakai: <strong id="valHariTerpakai" class="mono" style="color: var(--text-heading);">14 hari</strong></span>
                <span style="color: var(--text-muted);">Sisa Hari: <strong id="valSisaHari" class="mono" style="color: var(--primary);">16 hari</strong></span>
            </div>
            <div style="height: 10px; background-color: #e2e8f0; border-radius: 5px; overflow: hidden; display: flex;">
                <div id="barTerpakai" style="width: 46%; background-color: #94a3b8; transition: width 0.2s;"></div>
                <div id="barSisa" style="width: 54%; background-color: var(--primary); transition: width 0.2s;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                <span id="lblAwalPeriode">01</span>
                <span>Total Periode: <span id="valTotalHari" class="mono">30</span> Hari</span>
                <span id="lblAkhirPeriode">31</span>
            </div>
        </div>

        <!-- Breakdown Angka -->
        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                <span style="color: var(--text-muted);">Kredit Sisa Paket Lama</span>
                <span class="mono" id="valKreditSen" style="color: var(--success); font-weight: 500;">Rp0</span>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                <span style="color: var(--text-muted);">Biaya Sisa Paket Baru</span>
                <span class="mono" id="valTagihanBaruSen" style="font-weight: 500;">Rp0</span>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem; padding-top: 0.5rem; border-top: 1px dashed var(--border-color);">
                <span style="color: var(--text-heading); font-weight: 600;">Selisih Bersih (Prorata)</span>
                <span class="mono" id="valBersihSen" style="font-weight: 600;">Rp0</span>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8125rem; color: var(--text-muted);">
                <span>PPN 11% (Basis Poin 1100)</span>
                <span class="mono" id="valPajakSen">Rp0</span>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8125rem; color: var(--text-muted);">
                <span>Pembulatan Rupiah Penuh</span>
                <span class="mono" id="valPenyesuaianSen">Rp0</span>
            </div>

            <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid var(--border-color); margin-top: 0.5rem; font-size: 1.05rem; font-weight: 700; color: var(--text-heading);">
                <span id="labelTotalAkhir">Total Bayar Sekarang</span>
                <span class="mono" id="valTotalAkhir" style="color: var(--primary);">Rp0</span>
            </div>
        </div>

        <!-- Penjelasan Formula Algoritma -->
        <div style="margin-top: 1.25rem; font-size: 0.8125rem; color: var(--text-muted); line-height: 1.6;">
            <strong>Formula Integer Sen:</strong>
            <div class="mono" style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.5rem; margin-top: 0.35rem; font-size: 0.75rem;">
                Prorata = intdiv(HargaSen &times; SisaHari, TotalHari) [Half-Up]
            </div>
        </div>
    </div>
</div>

<!-- Panel Integrasi API & Salin Kode -->
<div class="panel" style="margin-top: 1.5rem;">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h2 class="panel-title">Salin Kode Integrasi API</h2>
            <p class="panel-subtitle">Gunakan cuplikan kode ini langsung di backend atau aplikasi frontend Anda.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="copyNotice" style="display: none; font-size: 0.75rem; color: var(--success); font-weight: 600;">Kode Tersalin!</span>
            <button type="button" class="btn-ui btn-ui-dark btn-ui-sm" id="btnCopyCode" onclick="salinKode()">
                Salin Kode
            </button>
        </div>
    </div>

    <!-- Tab Selector Bahasa -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; overflow-x: auto;">
        <button type="button" class="btn-tab active" onclick="pilihBahasa('curl')" id="tab-curl">cURL (CLI)</button>
        <button type="button" class="btn-tab" onclick="pilihBahasa('js')" id="tab-js">JavaScript (Fetch)</button>
        <button type="button" class="btn-tab" onclick="pilihBahasa('php')" id="tab-php">PHP (cURL)</button>
        <button type="button" class="btn-tab" onclick="pilihBahasa('python')" id="tab-python">Python (requests)</button>
    </div>

    <pre id="codeDisplay" class="mono" style="background-color: #0f172a; color: #f8fafc; border-radius: 8px; padding: 1.25rem; font-size: 0.8125rem; overflow-x: auto; line-height: 1.6; margin: 0;"></pre>
</div>

<style>
.btn-tab {
    background: none;
    border: 1px solid transparent;
    padding: 0.4rem 0.85rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.btn-tab:hover {
    color: var(--text-heading);
    background-color: #f1f5f9;
}
.btn-tab.active {
    background-color: #eff6ff;
    color: var(--primary);
    border-color: #bfdbfe;
}
</style>

<script>
let bahasaAktif = 'curl';
let paramsTerkini = {
    hargaLamaSen: 10000000,
    hargaBaruSen: 20000000,
    tglMulai: '<?= date('Y-m-01') ?>',
    tglSelesai: '<?= date('Y-m-t') ?>',
    tglUbah: '<?= date('Y-m-15') ?>'
};

const apiUrlSimulasi = '<?= site_url('api/kalkulator/simulasi') ?>';

function pilihBahasa(bahasa) {
    bahasaAktif = bahasa;
    ['curl', 'js', 'php', 'python'].forEach(b => {
        const tab = document.getElementById('tab-' + b);
        if (tab) {
            tab.className = b === bahasa ? 'btn-tab active' : 'btn-tab';
        }
    });
    renderSnippet();
}

function renderSnippet() {
    const p = paramsTerkini;
    let snippet = '';

    if (bahasaAktif === 'curl') {
        snippet = `curl -X POST "${apiUrlSimulasi}" \\
  -H "Content-Type: application/json" \\
  -d '{
    "harga_lama_sen": ${p.hargaLamaSen},
    "harga_baru_sen": ${p.hargaBaruSen},
    "tgl_mulai": "${p.tglMulai}",
    "tgl_selesai": "${p.tglSelesai}",
    "tgl_ubah": "${p.tglUbah}"
  }'`;
    } else if (bahasaAktif === 'js') {
        snippet = `const response = await fetch('${apiUrlSimulasi}', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    harga_lama_sen: ${p.hargaLamaSen},
    harga_baru_sen: ${p.hargaBaruSen},
    tgl_mulai: '${p.tglMulai}',
    tgl_selesai: '${p.tglSelesai}',
    tgl_ubah: '${p.tglUbah}'
  })
});

const res = await response.json();
console.log('Hasil Prorata:', res.data);`;
    } else if (bahasaAktif === 'php') {
        snippet = '<' + '?php\n\n' + `$payload = json_encode([
    'harga_lama_sen' => ${p.hargaLamaSen},
    'harga_baru_sen' => ${p.hargaBaruSen},
    'tgl_mulai'      => '${p.tglMulai}',
    'tgl_selesai'    => '${p.tglSelesai}',
    'tgl_ubah'       => '${p.tglUbah}',
]);

$ch = curl_init('${apiUrlSimulasi}');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

$result = json_decode(curl_exec($ch), true);
curl_close($ch);

print_r($result['data']);`;
    } else if (bahasaAktif === 'python') {
        snippet = `import requests

url = "${apiUrlSimulasi}"
payload = {
    "harga_lama_sen": ${p.hargaLamaSen},
    "harga_baru_sen": ${p.hargaBaruSen},
    "tgl_mulai": "${p.tglMulai}",
    "tgl_selesai": "${p.tglSelesai}",
    "tgl_ubah": "${p.tglUbah}"
}

res = requests.post(url, json=payload).json()
print("Hasil Prorata:", res["data"])`;
    }

    const display = document.getElementById('codeDisplay');
    if (display) {
        display.textContent = snippet;
    }
}

async function salinKode() {
    const display = document.getElementById('codeDisplay');
    if (!display) return;

    try {
        await navigator.clipboard.writeText(display.textContent);
        const notice = document.getElementById('copyNotice');
        const btn = document.getElementById('btnCopyCode');
        notice.style.display = 'inline';
        btn.innerText = 'Tersalin!';
        setTimeout(() => {
            notice.style.display = 'none';
            btn.innerText = 'Salin Kode';
        }, 2000);
    } catch (e) {
        alert('Gagal menyalin kode ke clipboard.');
    }
}

function formatRupiahJs(sen) {
    const rupiah = Math.floor(Math.abs(sen) / 100);
    const sisa = Math.abs(sen) % 100;
    const format = rupiah.toLocaleString('id-ID');
    const sisaStr = sisa < 10 ? '0' + sisa : sisa;
    return (sen < 0 ? '-' : '') + 'Rp' + format + ',' + sisaStr;
}

function sinkronHargaLama() {
    const val = document.getElementById('selectPaketLama').value;
    document.getElementById('groupCustomLama').style.display = val === 'custom' ? 'block' : 'none';
    hitungSimulasi();
}

function sinkronHargaBaru() {
    const val = document.getElementById('selectPaketBaru').value;
    document.getElementById('groupCustomBaru').style.display = val === 'custom' ? 'block' : 'none';
    hitungSimulasi();
}

function updateBatasTanggal() {
    const tglMulai = document.getElementById('tglMulai').value;
    const tglSelesai = document.getElementById('tglSelesai').value;
    const tglUbahInput = document.getElementById('tglUbah');

    tglUbahInput.min = tglMulai;
    tglUbahInput.max = tglSelesai;
    hitungSimulasi();
}

async function hitungSimulasi() {
    let hargaLamaSen = 0;
    const selLama = document.getElementById('selectPaketLama').value;
    if (selLama === 'custom') {
        hargaLamaSen = Math.round(parseFloat(document.getElementById('inputCustomLama').value || 0) * 100);
    } else {
        hargaLamaSen = parseInt(selLama, 10);
    }

    let hargaBaruSen = 0;
    const selBaru = document.getElementById('selectPaketBaru').value;
    if (selBaru === 'custom') {
        hargaBaruSen = Math.round(parseFloat(document.getElementById('inputCustomBaru').value || 0) * 100);
    } else {
        hargaBaruSen = parseInt(selBaru, 10);
    }

    const tglMulai = document.getElementById('tglMulai').value;
    const tglSelesai = document.getElementById('tglSelesai').value;
    const tglUbah = document.getElementById('tglUbah').value;

    paramsTerkini = {
        hargaLamaSen: hargaLamaSen,
        hargaBaruSen: hargaBaruSen,
        tglMulai: tglMulai,
        tglSelesai: tglSelesai,
        tglUbah: tglUbah
    };
    renderSnippet();

    document.getElementById('labelTglUbah').innerText = tglUbah;
    document.getElementById('lblAwalPeriode').innerText = tglMulai;
    document.getElementById('lblAkhirPeriode').innerText = tglSelesai;

    try {
        const response = await fetch(`<?= site_url('api/kalkulator/simulasi') ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                harga_lama_sen: hargaLamaSen,
                harga_baru_sen: hargaBaruSen,
                tgl_mulai: tglMulai,
                tgl_selesai: tglSelesai,
                tgl_ubah: tglUbah
            })
        });

        const res = await response.json();
        if (response.ok && res.status === 'sukses') {
            const d = res.data;
            document.getElementById('valTotalHari').innerText = d.total_hari;
            document.getElementById('valHariTerpakai').innerText = d.hari_terpakai + ' hari';
            document.getElementById('valSisaHari').innerText = d.sisa_hari + ' hari';

            const pctTerpakai = (d.hari_terpakai / d.total_hari) * 100;
            const pctSisa = 100 - pctTerpakai;
            document.getElementById('barTerpakai').style.width = pctTerpakai + '%';
            document.getElementById('barSisa').style.width = pctSisa + '%';

            document.getElementById('valKreditSen').innerText = '-' + formatRupiahJs(d.kredit_sen);
            document.getElementById('valTagihanBaruSen').innerText = '+' + formatRupiahJs(d.tagihan_baru_sen);
            document.getElementById('valBersihSen').innerText = formatRupiahJs(d.bersih_sen);
            document.getElementById('valPajakSen').innerText = formatRupiahJs(d.pajak_sen);
            document.getElementById('valPenyesuaianSen').innerText = (d.penyesuaian_sen > 0 ? '+' : '') + formatRupiahJs(d.penyesuaian_sen);

            const badge = document.getElementById('badgeAksi');
            const lblTotal = document.getElementById('labelTotalAkhir');
            const valTotal = document.getElementById('valTotalAkhir');

            if (d.jenis_aksi === 'upgrade') {
                badge.className = 'status-badge active';
                badge.innerText = 'UPGRADE';
                lblTotal.innerText = 'Total Tagihan Dibayar';
                valTotal.innerText = formatRupiahJs(d.total_bayar_sen);
                valTotal.style.color = 'var(--primary)';
            } else if (d.jenis_aksi === 'downgrade') {
                badge.className = 'status-badge gratis';
                badge.innerText = 'DOWNGRADE';
                lblTotal.innerText = 'Saldo Kredit Masuk Akun';
                valTotal.innerText = formatRupiahJs(d.saldo_kredit_sen);
                valTotal.style.color = 'var(--success)';
            } else {
                badge.className = 'status-badge pending';
                badge.innerText = 'TIDAK BERUBAH';
                lblTotal.innerText = 'Total Selisih';
                valTotal.innerText = 'Rp0,00';
                valTotal.style.color = 'var(--text-heading)';
            }
        }
    } catch (e) {
        console.error(e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateBatasTanggal();
    renderSnippet();
});
</script>
<?= $this->endSection() ?>
