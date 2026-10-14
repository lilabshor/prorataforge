<?php
namespace App\Models;

use CodeIgniter\Model;

class TagihanItemModel extends Model
{
    protected $table            = 'tagihan_item';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tagihan_id', 'jenis', 'keterangan', 'jumlah',
        'satuan_sen', 'subtotal_sen', 'paket_id',
    ];

    protected $useTimestamps = false;
}
