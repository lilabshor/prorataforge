<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PaketModel;
use App\Services\ProrataCalculator;
use DateTimeImmutable;
use Exception;

final class PlaygroundController extends BaseController
{
    public function index()
    {
        $paketList = model(PaketModel::class)
            ->where('is_aktif', 1)
            ->orderBy('urutan', 'ASC')
            ->findAll();

        return view('playground/index', [
            'paketList' => $paketList,
        ]);
    }

    public function hitung()
    {
        $hargaLamaSen = (int) $this->request->getVar('harga_lama_sen');
        $hargaBaruSen = (int) $this->request->getVar('harga_baru_sen');
        $tglMulai     = (string) $this->request->getVar('tgl_mulai');
        $tglSelesai   = (string) $this->request->getVar('tgl_selesai');
        $tglUbah      = (string) $this->request->getVar('tgl_ubah');

        if (! $tglMulai || ! $tglSelesai || ! $tglUbah) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter tanggal mulai, tanggal selesai, dan tanggal ubah wajib diisi.',
            ]);
        }

        try {
            $kalkulator = new ProrataCalculator();
            $hasil = $kalkulator->hitung(
                $hargaLamaSen,
                $hargaBaruSen,
                new DateTimeImmutable($tglMulai),
                new DateTimeImmutable($tglSelesai),
                new DateTimeImmutable($tglUbah)
            );

            // Perhitungan PPN 11% dan pembulatan rupiah jika ada tagihan bersih
            $bersihSen    = $hasil->bersihSen;
            $dasarPajak   = max(0, $bersihSen);
            $pajakSen     = intdiv($dasarPajak * 1100 + 5000, 10000);
            $totalSen     = $dasarPajak + $pajakSen;
            $totalPenuh   = ProrataCalculator::keRupiahPenuh($totalSen);
            $penyesuaian  = $totalPenuh - $totalSen;

            return $this->response->setJSON([
                'status' => 'sukses',
                'data'   => [
                    'total_hari'      => $hasil->totalHari,
                    'hari_terpakai'   => $hasil->hariTerpakai,
                    'sisa_hari'       => $hasil->sisaHari,
                    'kredit_sen'      => $hasil->kreditSen,
                    'tagihan_baru_sen'=> $hasil->tagihanBaruSen,
                    'bersih_sen'      => $hasil->bersihSen,
                    'pajak_sen'       => $pajakSen,
                    'penyesuaian_sen' => $penyesuaian,
                    'total_bayar_sen' => $bersihSen > 0 ? $totalPenuh : 0,
                    'saldo_kredit_sen'=> $bersihSen < 0 ? abs($bersihSen) : 0,
                    'jenis_aksi'      => $bersihSen > 0 ? 'upgrade' : ($bersihSen < 0 ? 'downgrade' : 'sama'),
                ],
            ]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
