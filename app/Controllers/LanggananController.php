<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LanggananModel;
use App\Models\PaketModel;
use App\Models\TagihanModel;
use App\Services\CreditService;
use App\Services\InvoiceService;
use App\Services\ProrataCalculator;
use App\Services\SubscriptionService;
use DomainException;
use Throwable;

final class LanggananController extends BaseController
{
    public function index()
    {
        $userId = (int) session('user_id');
        $list   = model(LanggananModel::class)
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('langganan/index', [
            'langgananList' => $list,
        ]);
    }

    public function detail(int $id)
    {
        $userId = (int) session('user_id');
        $lang   = model(LanggananModel::class)->find($id);

        if ($lang === null || (int) $lang['user_id'] !== $userId) {
            return redirect()->to('/langganan')->with('error', 'Langganan tidak ditemukan.');
        }

        $tagihanList = model(TagihanModel::class)
            ->where('langganan_id', $id)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('langganan/detail', [
            'langganan'   => $lang,
            'tagihanList' => $tagihanList,
        ]);
    }

    public function formGanti(int $id)
    {
        $userId = (int) session('user_id');
        $lang   = model(LanggananModel::class)->find($id);

        if ($lang === null || (int) $lang['user_id'] !== $userId) {
            return redirect()->to('/langganan')->with('error', 'Langganan tidak ditemukan.');
        }

        if (! in_array($lang['status'], ['active', 'trialing'], true)) {
            return redirect()->to('/langganan/' . $id)
                ->with('error', 'Status langganan saat ini tidak mengizinkan pergantian paket.');
        }

        $paketList = model(PaketModel::class)
            ->where('is_aktif', 1)
            ->where('id !=', $lang['paket_id'])
            ->orderBy('urutan', 'ASC')
            ->findAll();

        $idemKey = bin2hex(random_bytes(16));

        return view('langganan/ganti_paket', [
            'lang'      => $lang,
            'paketList' => $paketList,
            'idemKey'   => $idemKey,
        ]);
    }

    public function prosesGanti(int $id)
    {
        $userId = (int) session('user_id');

        $rules = [
            'paket_baru_id'   => 'required|is_natural_no_zero',
            'tgl_perubahan'   => 'required|valid_date[Y-m-d]',
            'idempotency_key' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $paketBaruId = (int) $this->request->getPost('paket_baru_id');
        $tgl         = (string) $this->request->getPost('tgl_perubahan');
        $idemKey     = (string) $this->request->getPost('idempotency_key');
        $kodeKupon   = $this->request->getPost('kode_kupon');

        $lang = model(LanggananModel::class)->find($id);
        if ($lang === null || (int) $lang['user_id'] !== $userId) {
            return redirect()->to('/langganan')->with('error', 'Langganan tidak ditemukan.');
        }

        if ($lang['status'] !== 'trialing') {
            if ($tgl < $lang['periode_mulai'] || $tgl >= $lang['periode_selesai']) {
                $tglBatasAkhir = date('Y-m-d', strtotime($lang['periode_selesai'] . ' -1 day'));

                return redirect()->back()->withInput()->with(
                    'error',
                    'Tanggal perubahan harus berada dalam rentang periode berjalan (' . $lang['periode_mulai'] . ' s/d ' . $tglBatasAkhir . ').'
                );
            }
        }

        $service = new SubscriptionService(
            new ProrataCalculator(),
            new InvoiceService(new CreditService()),
            new CreditService()
        );

        try {
            $service->gantiPaket(
                $userId,
                $id,
                $paketBaruId,
                $tgl,
                $idemKey,
                ! empty($kodeKupon) ? (string) $kodeKupon : null,
                'web'
            );

            return redirect()->to('/langganan/' . $id)
                ->with('sukses', 'Paket langganan berhasil diubah.');
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memproses pergantian paket: ' . $e->getMessage());
        }
    }
}
