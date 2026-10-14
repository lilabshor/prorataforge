<?php
namespace App\Models;

use CodeIgniter\Model;

class TagihanModel extends Model
{
    protected $table            = 'tagihan_user';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nomor', 'user_id', 'langganan_id', 'jenis', 'status',
        'subtotal_sen', 'kredit_dipakai_sen', 'pajak_persen',
        'pajak_sen', 'penyesuaian_sen', 'total_sen', 'mata_uang',
        'tgl_terbit', 'tgl_jatuh_tempo', 'dibayar_pada',
        'idempotency_key', 'catatan',
    ];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
