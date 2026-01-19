<?php

namespace Database\Seeders;

use App\Models\TrLembur;
use Illuminate\Database\Seeder;

class LemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data contoh untuk testing approval flow
        $lemburData = [
            [
                'requestor_email' => 'andi@kantor.com',
                'tanggal' => now()->addDays(1),
                'keterangan' => 'Lembur untuk maintenance server',
                'status' => TrLembur::STATUS_PENDING_SPV,
            ],
            [
                'requestor_email' => 'andi@kantor.com',
                'tanggal' => now()->addDays(2),
                'keterangan' => 'Lembur untuk deploy aplikasi',
                'status' => TrLembur::STATUS_PENDING_MGR,
                'spv_email' => 'budi@kantor.com',
                'tgl_approve_spv' => now()->subHours(2),
            ],
            [
                'requestor_email' => 'andi@kantor.com',
                'tanggal' => now()->addDays(3),
                'keterangan' => 'Lembur untuk testing aplikasi',
                'status' => TrLembur::STATUS_APPROVED,
                'spv_email' => 'budi@kantor.com',
                'tgl_approve_spv' => now()->subDays(1),
                'mgr_email' => 'citra@kantor.com',
                'tgl_approve_mgr' => now()->subHours(5),
            ],
        ];

        foreach ($lemburData as $data) {
            TrLembur::create($data);
        }

        $this->command->info('Data contoh lembur berhasil dibuat!');
    }
}

