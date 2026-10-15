<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelBilling extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'paket_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'trialing', 'canceled', 'expired'],
                'default'    => 'active',
            ],
            'periode_mulai' => [
                'type' => 'DATE',
            ],
            'periode_selesai' => [
                'type' => 'DATE',
            ],
            'trial_selesai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'auto_perpanjang' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'versi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'dibatalkan_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'diubah_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'sumber' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'web',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('langganan', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nomor' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'langganan_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'jenis' => [
                'type'       => 'ENUM',
                'constraint' => ['prorata', 'perpanjangan'],
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['terbit', 'gratis', 'dibayar', 'kedaluwarsa'],
                'default'    => 'terbit',
            ],
            'subtotal_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'kredit_dipakai_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'pajak_persen' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 11.00,
            ],
            'pajak_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'penyesuaian_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'total_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'mata_uang' => [
                'type'       => 'CHAR',
                'constraint' => 3,
                'default'    => 'IDR',
            ],
            'tgl_terbit' => [
                'type' => 'DATE',
            ],
            'tgl_jatuh_tempo' => [
                'type' => 'DATE',
            ],
            'dibayar_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'idempotency_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor');
        $this->forge->addUniqueKey('idempotency_key');
        $this->forge->addKey('user_id');
        $this->forge->createTable('tagihan_user', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tagihan_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jumlah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'satuan_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'subtotal_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
            ],
            'paket_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tagihan_id');
        $this->forge->createTable('tagihan_item', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tagihan_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'midtrans',
            ],
            'ref_gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'metode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'jumlah_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'berhasil', 'gagal', 'expired'],
                'default'    => 'pending',
            ],
            'payload_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'dibayar_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tagihan_id');
        $this->forge->createTable('pembayaran', true);

        $this->forge->addField([
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'saldo_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'default'    => 0,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->createTable('saldo_kredit', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'delta_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
            ],
            'saldo_setelah_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
            ],
            'tagihan_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('buku_kredit', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'langganan_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'paket_lama_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'paket_baru_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tgl_efektif' => [
                'type' => 'DATE',
            ],
            'rincian_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'diubah_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'sumber' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'web',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('langganan_id');
        $this->forge->createTable('riwayat_perubahan', true);

        $this->forge->addField([
            'tahun' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
            ],
            'bulan' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
            ],
            'terakhir' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
        ]);
        $this->forge->addKey(['tahun', 'bulan'], true);
        $this->forge->createTable('penomoran_invoice', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kunci' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'endpoint' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'hash_request' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'respons_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status_http' => [
                'type'       => 'SMALLINT',
                'constraint' => 6,
                'default'    => 200,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'endpoint', 'kunci']);
        $this->forge->createTable('idempotency_keys', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'jenis' => [
                'type'       => 'ENUM',
                'constraint' => ['persen', 'nominal'],
            ],
            'nilai' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'maks_diskon_sen' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
            ],
            'is_aktif' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('kupon', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('kupon', true);
        $this->forge->dropTable('idempotency_keys', true);
        $this->forge->dropTable('penomoran_invoice', true);
        $this->forge->dropTable('riwayat_perubahan', true);
        $this->forge->dropTable('buku_kredit', true);
        $this->forge->dropTable('saldo_kredit', true);
        $this->forge->dropTable('pembayaran', true);
        $this->forge->dropTable('tagihan_item', true);
        $this->forge->dropTable('tagihan_user', true);
        $this->forge->dropTable('langganan', true);
    }
}
