<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User contoh sesuai schema
        $users = [
            [
                'name' => 'Andi',
                'email' => 'andi@kantor.com',
                'password' => Hash::make('password'),
                'jabatan' => 'Staff',
                'department' => 'IT',
            ],
            [
                'name' => 'Budi',
                'email' => 'budi@kantor.com',
                'password' => Hash::make('password'),
                'jabatan' => 'Supervisor',
                'department' => 'IT',
            ],
            [
                'name' => 'Citra',
                'email' => 'citra@kantor.com',
                'password' => Hash::make('password'),
                'jabatan' => 'Manager',
                'department' => 'IT',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('User contoh berhasil dibuat!');
        $this->command->info('Email: andi@kantor.com, budi@kantor.com, citra@kantor.com');
        $this->command->info('Password: password (untuk semua user)');
    }
}

