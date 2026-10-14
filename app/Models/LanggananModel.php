<?php
namespace App\Models;

use CodeIgniter\Model;

class LanggananModel extends Model
{
    protected $table            = 'langganan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'paket_id', 'status',
        'periode_mulai', 'periode_selesai',
        'trial_selesai', 'auto_perpanjang',
        'versi', 'dibatalkan_pada',
        'diubah_oleh', 'sumber',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = ['catatRiwayatBuat'];
    protected $beforeUpdate   = ['simpanSnapshotLama'];
    protected $afterUpdate    = ['catatRiwayatUbah'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    private array $snapshotLama = [];

    protected function simpanSnapshotLama(array $event)
    {
        $ids = (array) ($event['id'] ?? []);
        if (isset($ids[0])) {
            $lama = $this->find($ids[0]);
            if ($lama !== null) {
                $this->snapshotLama[(int) $ids[0]] = $lama;
            }
        }
        return $event;
    }

    protected function catatRiwayatBuat(array $event)
    {
        $data    = $event['data'] ?? [];
        $riwayat = model(RiwayatPerubahanModel::class);
        $riwayat->insert([
            'langganan_id'  => $event['id'] ?? null,
            'jenis'         => 'buat',
            'paket_lama_id' => null,
            'paket_baru_id' => $data['paket_id'] ?? null,
            'tgl_efektif'   => $data['periode_mulai'] ?? date('Y-m-d'),
            'rincian_json'  => json_encode($data),
            'diubah_oleh'   => $data['diubah_oleh'] ?? null,
            'sumber'        => $data['sumber'] ?? 'web',
        ]);
        return $event;
    }

    protected function catatRiwayatUbah(array $event)
    {
        $data = $event['data'] ?? [];
        foreach ((array) ($event['id'] ?? []) as $id) {
            $lama = $this->snapshotLama[(int) $id] ?? null;
            if ($lama === null) {
                continue;
            }
            $paketBerubah  = isset($data['paket_id'])
                && (int) $data['paket_id'] !== (int) $lama['paket_id'];
            $statusBerubah = isset($data['status'])
                && $data['status'] !== $lama['status'];
            if (! $paketBerubah && ! $statusBerubah) {
                continue;
            }
            $riwayat = model(RiwayatPerubahanModel::class);
            $riwayat->insert([
                'langganan_id'  => $id,
                'jenis'         => $paketBerubah ? 'ganti_paket' : 'status',
                'paket_lama_id' => $lama['paket_id'],
                'paket_baru_id' => $data['paket_id'] ?? $lama['paket_id'],
                'tgl_efektif'   => date('Y-m-d'),
                'rincian_json'  => json_encode([
                    'sebelum' => $lama,
                    'sesudah' => $data,
                ]),
                'diubah_oleh'   => $data['diubah_oleh'] ?? null,
                'sumber'        => $data['sumber'] ?? 'web',
            ]);
        }
        return $event;
    }
}
