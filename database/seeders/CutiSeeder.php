<?php

namespace Database\Seeders;

use App\Models\TrCuti;
use Illuminate\Database\Seeder;

class CutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data contoh untuk testing approval flow
        $cutiData = [
            [
                'requestor_email' => 'andi@kantor.com',
                'tanggal' => now()->addDays(5),
                'keterangan' => 'Cuti untuk keperluan keluarga',
                'status' => TrCuti::STATUS_PENDING_SPV,
            ],
            [
                'requestor_email' => 'andi@kantor.com',
                'tanggal' => now()->addDays(10),
                'keterangan' => 'Cuti tahunan',
                'status' => TrCuti::STATUS_PENDING_MGR,
                'spv_email' => 'budi@kantor.com',
                'tgl_approve_spv' => now()->subHours(3),
            ],
            [
                'requestor_email' => 'andi@kantor.com',
                'tanggal' => now()->addDays(15),
                'keterangan' => 'Cuti untuk liburan',
                'status' => TrCuti::STATUS_APPROVED,
                'spv_email' => 'budi@kantor.com',
                'tgl_approve_spv' => now()->subDays(2),
                'mgr_email' => 'citra@kantor.com',
                'tgl_approve_mgr' => now()->subHours(6),
            ],
        ];

        foreach ($cutiData as $data) {
            TrCuti::create($data);
        }

        $this->command->info('Data contoh cuti berhasil dibuat!');
    }
}
