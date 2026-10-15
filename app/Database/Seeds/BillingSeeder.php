<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class BillingSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        $userAda = $db->table('users')->where('id', 1)->get()->getRowArray();
        if ($userAda === null) {
            $db->table('users')->insert([
                'id'            => 1,
                'nama'          => 'Pelanggan Demo',
                'email'         => 'demo@prorataforge.test',
                'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
                'peran'         => 'admin',
                'status'        => 'aktif',
            ]);
        }

        $paketData = [
            [
                'kode'           => 'starter_monthly',
                'nama'           => 'Paket Starter',
                'harga_sen'      => 9900000,
                'mata_uang'      => 'IDR',
                'interval_bulan' => 1,
                'fitur_json'     => json_encode(['Hingga 50 transaksi/bln', 'Export PDF', 'Support Email']),
                'batas_json'     => json_encode(['max_invoices' => 50, 'max_users' => 2]),
                'is_aktif'       => 1,
                'urutan'         => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'kode'           => 'pro_monthly',
                'nama'           => 'Paket Professional',
                'harga_sen'      => 19900000,
                'mata_uang'      => 'IDR',
                'interval_bulan' => 1,
                'fitur_json'     => json_encode(['Unlimited transaksi', 'Integrasi Midtrans', 'Prorata Engine', 'Priority Support']),
                'batas_json'     => json_encode(['max_invoices' => 1000, 'max_users' => 10]),
                'is_aktif'       => 1,
                'urutan'         => 2,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'kode'           => 'enterprise_monthly',
                'nama'           => 'Paket Enterprise',
                'harga_sen'      => 49900000,
                'mata_uang'      => 'IDR',
                'interval_bulan' => 1,
                'fitur_json'     => json_encode(['Dedicated Account Manager', 'Custom SLA 99.9%', 'Custom Gateway']),
                'batas_json'     => json_encode(['max_invoices' => 10000, 'max_users' => 50]),
                'is_aktif'       => 1,
                'urutan'         => 3,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'kode'           => 'pro_annual',
                'nama'           => 'Paket Pro Tahunan',
                'harga_sen'      => 199000000,
                'mata_uang'      => 'IDR',
                'interval_bulan' => 12,
                'fitur_json'     => json_encode(['Semua fitur Pro', 'Diskon 2 bulan', 'Free onboarding']),
                'batas_json'     => json_encode(['max_invoices' => 15000, 'max_users' => 15]),
                'is_aktif'       => 1,
                'urutan'         => 4,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($paketData as $paket) {
            $ada = $db->table('paket_langganan')->where('kode', $paket['kode'])->get()->getRowArray();
            if ($ada === null) {
                $db->table('paket_langganan')->insert($paket);
            }
        }

        $kuponData = [
            [
                'kode'            => 'HEMAT10',
                'jenis'           => 'persen',
                'nilai'           => 10,
                'maks_diskon_sen' => 5000000,
                'is_aktif'        => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'kode'            => 'POTONG50K',
                'jenis'           => 'nominal',
                'nilai'           => 5000000,
                'maks_diskon_sen' => 5000000,
                'is_aktif'        => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($kuponData as $kupon) {
            $ada = $db->table('kupon')->where('kode', $kupon['kode'])->get()->getRowArray();
            if ($ada === null) {
                $db->table('kupon')->insert($kupon);
            }
        }

        $langgananAda = $db->table('langganan')->where('user_id', 1)->get()->getRowArray();
        if ($langgananAda === null) {
            $starterPaket = $db->table('paket_langganan')->where('kode', 'starter_monthly')->get()->getRowArray();
            if ($starterPaket !== null) {
                $db->table('langganan')->insert([
                    'user_id'         => 1,
                    'paket_id'        => (int) $starterPaket['id'],
                    'status'          => 'active',
                    'periode_mulai'   => date('Y-m-01'),
                    'periode_selesai' => date('Y-m-t', strtotime('+1 month')),
                    'auto_perpanjang' => 1,
                    'versi'           => 1,
                    'diubah_oleh'     => 1,
                    'sumber'          => 'seeder',
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
