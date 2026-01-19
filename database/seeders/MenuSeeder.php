<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            // Dashboard - untuk semua jabatan dan department
            [
                'label' => 'Dashboard',
                'icon' => 'dashboard',
                'menu_link' => '/dashboard',
                'jabatan' => null, // Untuk semua jabatan
                'department' => null, // Untuk semua department
                'order' => 0,
                'is_active' => true,
            ],

            // Menu untuk Staff IT
            [
                'label' => 'Lembur Saya',
                'icon' => 'access_time',
                'menu_link' => '/lembur',
                'jabatan' => 'Staff',
                'department' => 'IT',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'label' => 'Buat Lembur',
                'icon' => 'add_circle',
                'menu_link' => '/lembur/create',
                'jabatan' => 'Staff',
                'department' => 'IT',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'label' => 'PO Saya',
                'icon' => 'shopping_cart',
                'menu_link' => '/po',
                'jabatan' => 'Staff',
                'department' => 'IT',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'label' => 'Buat PO',
                'icon' => 'add',
                'menu_link' => '/po/create',
                'jabatan' => 'Staff',
                'department' => 'IT',
                'order' => 4,
                'is_active' => true,
            ],

            // Menu untuk Supervisor IT
            [
                'label' => 'Approval Lembur',
                'icon' => 'rule',
                'menu_link' => '/lembur/approval',
                'jabatan' => 'Supervisor',
                'department' => 'IT',
                'order' => 1,
                'is_active' => true,
            ],

            // Menu untuk Manager IT
            [
                'label' => 'Approval Lembur',
                'icon' => 'rule',
                'menu_link' => '/lembur/approval',
                'jabatan' => 'Manager',
                'department' => 'IT',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'label' => 'Approval PO',
                'icon' => 'shopping_cart',
                'menu_link' => '/po/approval',
                'jabatan' => 'Manager',
                'department' => 'IT',
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                [
                    'label' => $menu['label'],
                    'jabatan' => $menu['jabatan'],
                    'department' => $menu['department'],
                ],
                $menu
            );
        }

        $this->command->info('Menu berhasil dibuat!');
    }
}
