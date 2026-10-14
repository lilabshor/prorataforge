<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PaketModel;
use CodeIgniter\RESTful\ResourceController;

final class PaketController extends ResourceController
{
    protected $modelName = PaketModel::class;
    protected $format    = 'html';
    protected $helpers   = ['uang'];

    public function index()
    {
        $paketList = $this->model->orderBy('urutan', 'ASC')->findAll();

        if ($this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')) {
            return $this->respond($paketList);
        }

        return view('admin/paket/index', [
            'paketList' => $paketList,
        ]);
    }

    public function show($id = null)
    {
        $paket = $this->model->find($id);
        if ($paket === null) {
            return redirect()->to('/admin/paket')->with('error', 'Paket tidak ditemukan.');
        }

        if ($this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')) {
            return $this->respond($paket);
        }

        return view('admin/paket/show', ['paket' => $paket]);
    }

    public function new()
    {
        return view('admin/paket/form', [
            'paket' => null,
            'aksi'  => 'tambah',
        ]);
    }

    public function create()
    {
        $rules = [
            'kode'           => 'required|alpha_numeric_punct|is_unique[paket_langganan.kode]',
            'nama'           => 'required|min_length[3]|max_length[100]',
            'harga_rupiah'   => 'required|numeric',
            'interval_bulan' => 'required|is_natural_no_zero',
            'urutan'         => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hargaRupiah = (float) $this->request->getPost('harga_rupiah');
        $hargaSen    = (int) round($hargaRupiah * 100);

        $fiturRaw = (string) $this->request->getPost('fitur_json');
        $batasRaw = (string) $this->request->getPost('batas_json');

        $this->model->insert([
            'kode'           => (string) $this->request->getPost('kode'),
            'nama'           => (string) $this->request->getPost('nama'),
            'harga_sen'      => $hargaSen,
            'mata_uang'      => 'IDR',
            'interval_bulan' => (int) $this->request->getPost('interval_bulan'),
            'fitur_json'     => ! empty($fiturRaw) ? $fiturRaw : null,
            'batas_json'     => ! empty($batasRaw) ? $batasRaw : null,
            'is_aktif'       => $this->request->getPost('is_aktif') ? 1 : 0,
            'urutan'         => (int) ($this->request->getPost('urutan') ?? 0),
        ]);

        return redirect()->to('/admin/paket')->with('sukses', 'Paket langganan berhasil ditambahkan.');
    }

    public function edit($id = null)
    {
        $paket = $this->model->find($id);
        if ($paket === null) {
            return redirect()->to('/admin/paket')->with('error', 'Paket tidak ditemukan.');
        }

        return view('admin/paket/form', [
            'paket' => $paket,
            'aksi'  => 'ubah',
        ]);
    }

    public function update($id = null)
    {
        $paket = $this->model->find($id);
        if ($paket === null) {
            return redirect()->to('/admin/paket')->with('error', 'Paket tidak ditemukan.');
        }

        $rules = [
            'kode'           => "required|alpha_numeric_punct|is_unique[paket_langganan.kode,id,{$id}]",
            'nama'           => 'required|min_length[3]|max_length[100]',
            'harga_rupiah'   => 'required|numeric',
            'interval_bulan' => 'required|is_natural_no_zero',
            'urutan'         => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hargaRupiah = (float) $this->request->getPost('harga_rupiah');
        $hargaSen    = (int) round($hargaRupiah * 100);

        $fiturRaw = (string) $this->request->getPost('fitur_json');
        $batasRaw = (string) $this->request->getPost('batas_json');

        $this->model->update($id, [
            'kode'           => (string) $this->request->getPost('kode'),
            'nama'           => (string) $this->request->getPost('nama'),
            'harga_sen'      => $hargaSen,
            'interval_bulan' => (int) $this->request->getPost('interval_bulan'),
            'fitur_json'     => ! empty($fiturRaw) ? $fiturRaw : null,
            'batas_json'     => ! empty($batasRaw) ? $batasRaw : null,
            'is_aktif'       => $this->request->getPost('is_aktif') ? 1 : 0,
            'urutan'         => (int) ($this->request->getPost('urutan') ?? 0),
        ]);

        return redirect()->to('/admin/paket')->with('sukses', 'Paket langganan berhasil diperbarui.');
    }

    public function delete($id = null)
    {
        $paket = $this->model->find($id);
        if ($paket === null) {
            return redirect()->to('/admin/paket')->with('error', 'Paket tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to('/admin/paket')->with('sukses', 'Paket langganan berhasil dinonaktifkan.');
    }
}
