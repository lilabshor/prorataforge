<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TagihanItemModel;
use App\Models\TagihanModel;

class TagihanController extends BaseController
{
    public function index()
    {
        $userId = (int) session('user_id');
        $list   = model(TagihanModel::class)
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('tagihan/index', [
            'tagihanList' => $list,
        ]);
    }

    public function detail(int $id)
    {
        $userId  = (int) session('user_id');
        $tagihan = model(TagihanModel::class)->find($id);

        if ($tagihan === null || (int) $tagihan['user_id'] !== $userId) {
            return redirect()->to('/tagihan')->with('error', 'Tagihan tidak ditemukan.');
        }

        $items = model(TagihanItemModel::class)
            ->where('tagihan_id', $id)
            ->findAll();

        return view('tagihan/detail', [
            'tagihan' => $tagihan,
            'items'   => $items,
        ]);
    }
}
