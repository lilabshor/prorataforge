<?php
namespace App\Models;

use CodeIgniter\Model;

class RiwayatPerubahanModel extends Model
{
    protected $table            = 'riwayat_perubahan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'langganan_id', 'jenis',
        'paket_lama_id', 'paket_baru_id',
        'tgl_efektif', 'rincian_json',
        'diubah_oleh', 'sumber',
    ];

    protected $useTimestamps = false;
}
