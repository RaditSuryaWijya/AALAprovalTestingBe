<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\TrCuti;
use App\Models\TrLembur;
use App\Models\TrPo;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MasterExportController extends Controller
{
    /**
     * Export PDF generik berdasarkan nama master.
     *
     * API endpoint: GET /api/export/{master}
     * Contoh: GET /api/export/users
     */
    public function export(string $master)
    {
        [$title, $columns, $rows] = $this->getConfigFor($master);

        $pdf = Pdf::loadView('pdf.master', [
            'title'   => $title,
            'columns' => $columns,
            'rows'    => $rows,
        ])->setPaper('A4', 'portrait');

        return $pdf->download($master . '.pdf');
    }

    /**
     * Export PDF berdasarkan ID tertentu dari master.
     *
     * API endpoint: GET /api/export/{master}/{id}
     * Contoh: GET /api/export/users/1
     */
    public function exportById(string $master, int $id)
    {
        [$title, $columns, $rows] = $this->getConfigForById($master, $id);

        $pdf = Pdf::loadView('pdf.master', [
            'title'   => $title,
            'columns' => $columns,
            'rows'    => $rows,
        ])->setPaper('A4', 'portrait');

        return $pdf->download($master . '-' . $id . '.pdf');
    }

    /**
     * Mapping konfigurasi untuk tiap master.
     *
     * Tambahkan case baru di sini jika ada master baru.
     */
    protected function getConfigFor(string $master): array
    {
        return match ($master) {
            // Master: Users
            'users' => [
                'Daftar User',
                [
                    ['label' => 'ID',        'field' => 'id'],
                    ['label' => 'Nama',      'field' => 'name'],
                    ['label' => 'Email',     'field' => 'email'],
                    ['label' => 'Jabatan',   'field' => 'jabatan'],
                    ['label' => 'Department','field' => 'department'],
                    ['label' => 'Created At','field' => 'created_at'],
                ],
                User::orderBy('id', 'asc')->get(),
            ],

            // Master: Menu
            'menus' => [
                'Daftar Menu',
                [
                    ['label' => 'ID',        'field' => 'id'],
                    ['label' => 'Label',     'field' => 'label'],
                    ['label' => 'Icon',      'field' => 'icon'],
                    ['label' => 'Menu Link', 'field' => 'menu_link'],
                    ['label' => 'Jabatan',   'field' => 'jabatan'],
                    ['label' => 'Department','field' => 'department'],
                    ['label' => 'Order',     'field' => 'order'],
                    ['label' => 'Status',    'field' => 'is_active'],
                ],
                Menu::orderBy('order', 'asc')->get(),
            ],

            // Master: Cuti
            'cuti' => [
                'Daftar Transaksi Cuti',
                [
                    ['label' => 'ID',              'field' => 'id'],
                    ['label' => 'Requestor Email', 'field' => 'requestor_email'],
                    ['label' => 'Tanggal',         'field' => 'tanggal'],
                    ['label' => 'Keterangan',      'field' => 'keterangan'],
                    ['label' => 'Status',          'field' => 'status'],
                    ['label' => 'SPV Email',       'field' => 'spv_email'],
                    ['label' => 'Tgl Approve SPV', 'field' => 'tgl_approve_spv'],
                    ['label' => 'MGR Email',       'field' => 'mgr_email'],
                    ['label' => 'Tgl Approve MGR', 'field' => 'tgl_approve_mgr'],
                    ['label' => 'Reject Reason',   'field' => 'reject_reason'],
                ],
                TrCuti::orderBy('id', 'desc')->get(),
            ],

            // Master: Lembur
            'lembur' => [
                'Daftar Transaksi Lembur',
                [
                    ['label' => 'ID',              'field' => 'id'],
                    ['label' => 'Requestor Email', 'field' => 'requestor_email'],
                    ['label' => 'Tanggal',         'field' => 'tanggal'],
                    ['label' => 'Keterangan',      'field' => 'keterangan'],
                    ['label' => 'Status',          'field' => 'status'],
                    ['label' => 'SPV Email',       'field' => 'spv_email'],
                    ['label' => 'Tgl Approve SPV', 'field' => 'tgl_approve_spv'],
                    ['label' => 'MGR Email',       'field' => 'mgr_email'],
                    ['label' => 'Tgl Approve MGR', 'field' => 'tgl_approve_mgr'],
                    ['label' => 'Reject Reason',   'field' => 'reject_reason'],
                ],
                TrLembur::orderBy('id', 'desc')->get(),
            ],

            // Master: PO (Purchase Order)
            'po' => [
                'Daftar Purchase Order',
                [
                    ['label' => 'ID',            'field' => 'id'],
                    ['label' => 'Creator Email', 'field' => 'creator_email'],
                    ['label' => 'Nama Barang',   'field' => 'nama_barang'],
                    ['label' => 'Total Harga',   'field' => 'total_harga'],
                    ['label' => 'Status',        'field' => 'status'],
                    ['label' => 'Approver Email','field' => 'approver_email'],
                    ['label' => 'Tgl Approve',   'field' => 'tgl_approve'],
                    ['label' => 'Reject Reason', 'field' => 'reject_reason'],
                ],
                TrPo::orderBy('id', 'desc')->get(),
            ],

            default => abort(404, 'Master tidak dikenal'),
        };
    }

    /**
     * Mapping konfigurasi untuk tiap master berdasarkan ID.
     *
     * Mengembalikan 1 row data berdasarkan ID.
     */
    protected function getConfigForById(string $master, int $id): array
    {
        return match ($master) {
            // Master: Users
            'users' => [
                'Detail User',
                [
                    ['label' => 'ID',        'field' => 'id'],
                    ['label' => 'Nama',      'field' => 'name'],
                    ['label' => 'Email',     'field' => 'email'],
                    ['label' => 'Jabatan',   'field' => 'jabatan'],
                    ['label' => 'Department','field' => 'department'],
                    ['label' => 'Created At','field' => 'created_at'],
                ],
                User::where('id', $id)->get(),
            ],

            // Master: Menu
            'menus' => [
                'Detail Menu',
                [
                    ['label' => 'ID',        'field' => 'id'],
                    ['label' => 'Label',     'field' => 'label'],
                    ['label' => 'Icon',      'field' => 'icon'],
                    ['label' => 'Menu Link', 'field' => 'menu_link'],
                    ['label' => 'Jabatan',   'field' => 'jabatan'],
                    ['label' => 'Department','field' => 'department'],
                    ['label' => 'Order',     'field' => 'order'],
                    ['label' => 'Status',    'field' => 'is_active'],
                ],
                Menu::where('id', $id)->get(),
            ],

            // Master: Cuti
            'cuti' => [
                'Detail Transaksi Cuti',
                [
                    ['label' => 'ID',              'field' => 'id'],
                    ['label' => 'Requestor Email', 'field' => 'requestor_email'],
                    ['label' => 'Tanggal',         'field' => 'tanggal'],
                    ['label' => 'Keterangan',      'field' => 'keterangan'],
                    ['label' => 'Status',          'field' => 'status'],
                    ['label' => 'SPV Email',       'field' => 'spv_email'],
                    ['label' => 'Tgl Approve SPV', 'field' => 'tgl_approve_spv'],
                    ['label' => 'MGR Email',       'field' => 'mgr_email'],
                    ['label' => 'Tgl Approve MGR', 'field' => 'tgl_approve_mgr'],
                    ['label' => 'Reject Reason',   'field' => 'reject_reason'],
                ],
                TrCuti::where('id', $id)->get(),
            ],

            // Master: Lembur
            'lembur' => [
                'Detail Transaksi Lembur',
                [
                    ['label' => 'ID',              'field' => 'id'],
                    ['label' => 'Requestor Email', 'field' => 'requestor_email'],
                    ['label' => 'Tanggal',         'field' => 'tanggal'],
                    ['label' => 'Keterangan',      'field' => 'keterangan'],
                    ['label' => 'Status',          'field' => 'status'],
                    ['label' => 'SPV Email',       'field' => 'spv_email'],
                    ['label' => 'Tgl Approve SPV', 'field' => 'tgl_approve_spv'],
                    ['label' => 'MGR Email',       'field' => 'mgr_email'],
                    ['label' => 'Tgl Approve MGR', 'field' => 'tgl_approve_mgr'],
                    ['label' => 'Reject Reason',   'field' => 'reject_reason'],
                ],
                TrLembur::where('id', $id)->get(),
            ],

            // Master: PO (Purchase Order)
            'po' => [
                'Detail Purchase Order',
                [
                    ['label' => 'ID',            'field' => 'id'],
                    ['label' => 'Creator Email', 'field' => 'creator_email'],
                    ['label' => 'Nama Barang',   'field' => 'nama_barang'],
                    ['label' => 'Total Harga',   'field' => 'total_harga'],
                    ['label' => 'Status',        'field' => 'status'],
                    ['label' => 'Approver Email','field' => 'approver_email'],
                    ['label' => 'Tgl Approve',   'field' => 'tgl_approve'],
                    ['label' => 'Reject Reason', 'field' => 'reject_reason'],
                ],
                TrPo::where('id', $id)->get(),
            ],

            default => abort(404, 'Master tidak dikenal'),
        };
    }
}

