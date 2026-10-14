<?php

declare(strict_types=1);

namespace App\Controllers;

final class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function docs(): string
    {
        return view('docs/index');
    }

    public function demoUser()
    {
        session()->set([
            'user_id'   => 1,
            'user_nama' => 'Pelanggan Demo',
            'is_admin'  => false,
        ]);

        return redirect()->to('/langganan')->with('sukses', 'Masuk sebagai sesi Pelanggan.');
    }

    public function demoAdmin()
    {
        session()->set([
            'user_id'   => 1,
            'user_nama' => 'Administrator',
            'is_admin'  => true,
        ]);

        return redirect()->to('/admin/laporan/mrr')->with('sukses', 'Masuk sebagai sesi Administrator.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('sukses', 'Sesi telah ditutup.');
    }
}
