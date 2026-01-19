<?php

namespace Database\Seeders;

use App\Models\TrPo;
use Illuminate\Database\Seeder;

class PoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data contoh untuk testing approval flow
        $poData = [
            [
                'creator_email' => 'andi@kantor.com',
                'nama_barang' => 'Laptop Dell XPS 15',
                'total_harga' => 25000000,
                'status' => TrPo::STATUS_PENDING,
            ],
            [
                'creator_email' => 'andi@kantor.com',
                'nama_barang' => 'Monitor LG 27 inch',
                'total_harga' => 5000000,
                'status' => TrPo::STATUS_APPROVED,
                'approver_email' => 'citra@kantor.com',
                'tgl_approve' => now()->subHours(3),
            ],
            [
                'creator_email' => 'andi@kantor.com',
                'nama_barang' => 'Keyboard Mechanical',
                'total_harga' => 1500000,
                'status' => TrPo::STATUS_REJECTED,
                'approver_email' => 'citra@kantor.com',
                'tgl_approve' => now()->subDays(1),
                'reject_reason' => 'Budget tidak mencukupi',
            ],
        ];

        foreach ($poData as $data) {
            TrPo::create($data);
        }

        $this->command->info('Data contoh PO berhasil dibuat!');
    }
}

