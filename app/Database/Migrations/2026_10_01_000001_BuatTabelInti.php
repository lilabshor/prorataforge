<?php

namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class BuatTabelInti extends Migration
{
    public function up()  {
        $this->forge->addField([
                'id' => [
                        'type' => 'BIGINT',
                        'constraint' => 20,
                        'unsigned' => true,
                        'auto_increment' => true,
                ],
                'kode' => [
                        'type' => 'varchar',
                        'constraint' => 100,
                ],
                'nama' => [
                        'type' => 'varchar',
                        'constraint' => 100,
                ],
                'harga_sen' => [
                        'type' => 'BIGINT',
                ],
                'mata_uang' => [
                        'type' => 'char',
                        'constraint' => 3,
                        'default' => 'IDR',
                ],
                'interval_bulan' => [
                        'type' => 'tinyint',
                        'default' => 1,
                ],
                'fitur_json' => ['type' => 'json', 'null' => true],
                'batas_json' => ['type' => 'json', 'null' =>true],

                'is_aktif' => [
                        'type' => 'tinyint',
                        'default' => 1,
                ],
                'urutan' => [
                        'type' => 'tinyint',
                        'default' => 0,
                ],
                'created_at' => ['type' => 'datetime', 'null' => true],
                'updated_at' => ['type' => 'datetime', 'null' => true],
                'deleted_at' => ['type' => 'datetime', 'null' => true],

        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('paket_langganan');
    }
    public function down () {
        $this->forge->dropTable('paket_langganan', true);
    }
}