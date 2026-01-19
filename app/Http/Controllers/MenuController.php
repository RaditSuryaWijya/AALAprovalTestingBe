<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get menu berdasarkan jabatan dan department user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $jabatan = $user->jabatan;
        $department = strtoupper($user->department ?? '');
        $isITDepartment = $department === 'IT';

        // Query menu dari database berdasarkan jabatan dan department
        // Logic: Menu dengan department null = untuk semua department
        // Menu dengan department IT = khusus untuk IT
        // Menu dengan department non-IT = khusus untuk department tersebut
        $query = Menu::active()
            ->forJabatan($jabatan)
            ->ordered();

        // Filter berdasarkan department
        if ($isITDepartment) {
            // Jika IT, ambil menu dengan department IT atau null (untuk semua)
            $query->where(function ($q) {
                $q->where('department', 'IT')
                  ->orWhereNull('department');
            });
        } else {
            // Jika non-IT, ambil menu dengan department null saja (untuk semua non-IT)
            // atau menu dengan department yang sesuai
            $query->where(function ($q) use ($department) {
                $q->whereNull('department')
                  ->orWhere('department', $department);
            });
        }

        $menus = $query->get();

        // Format response
        $formattedMenus = $menus->map(function ($menu, $index) {
            return [
                'id' => $menu->id,
                'label' => $menu->label,
                'icon' => $menu->icon,
                'menu_link' => $menu->menu_link,
                'index' => $index,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'menus' => $formattedMenus,
                'user' => [
                    'jabatan' => $jabatan,
                    'department' => $user->department,
                    'isITDepartment' => $isITDepartment,
                ],
            ],
        ]);
    }
}

