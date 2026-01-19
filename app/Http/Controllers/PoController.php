<?php

namespace App\Http\Controllers;

use App\Models\TrPo;
use Illuminate\Http\Request;

class PoController extends Controller
{
    /**
     * Endpoint generic: reject (Double Validation)
     * POST /api/po/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        return $this->decision($request, $id, 'reject');
    }

    /**
     * Endpoint tunggal approve/reject (Double Validation)
     *
     * Backend cek:
     * - Dokumen sedang menunggu siapa? (cek status)
     * - Apakah user yang login sesuai yang ditunggu? (cek role/jabatan + department)
     */
    protected function decision(Request $request, $id, string $action)
    {
        if (!in_array($action, ['approve', 'reject'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Action tidak valid',
            ], 400);
        }

        if ($action === 'reject') {
            $request->validate([
                'reject_reason' => 'required|string',
            ]);
        }

        $user = $request->user();
        $jabatan = $user->jabatan;
        $department = $user->department;

        // Double Validation: Role/Jabatan + department (Manager IT)
        if ($jabatan !== 'Manager' || $department !== 'IT') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk memproses approval PO (menunggu Manager IT)',
            ], 403);
        }

        $po = TrPo::findOrFail($id);

        // Double Validation: Status dokumen (giliran siapa)
        if ($po->status !== TrPo::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen PO tidak sedang menunggu approval Manager',
            ], 400);
        }

        if ($action === 'approve') {
            $po->update([
                'status' => TrPo::STATUS_APPROVED,
                'approver_email' => $user->email,
                'tgl_approve' => now(),
                'reject_reason' => null,
            ]);
        } else {
            $po->update([
                'status' => TrPo::STATUS_REJECTED,
                'approver_email' => $user->email,
                'tgl_approve' => now(),
                'reject_reason' => $request->reject_reason,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $action === 'approve'
                ? 'PO berhasil diapprove'
                : 'PO ditolak',
            'data' => $po,
        ]);
    }

    /**
     * Get list PO berdasarkan jabatan user
     * - Manager: melihat status PENDING
     * - Staff: hanya melihat PO mereka sendiri
     * 
     * Response menggunakan struktur Server-Driven UI yang standar
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $jabatan = $user->jabatan;
        $department = $user->department;

        $query = TrPo::query();

        // Filter berdasarkan jabatan
        if ($jabatan === 'Manager' && $department === 'IT') {
            $query->where('status', TrPo::STATUS_PENDING);
        } else {
            // Staff hanya bisa melihat PO mereka sendiri
            $query->where('creator_email', $user->email);
        }

        $po = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'config' => [
                'page_title' => 'Daftar Purchase Order',
                'mapping' => [
                    'title' => 'nama_barang',
                    'subtitle' => 'total_harga',
                    'date' => 'created_at',
                    'status' => 'status',
                ],
            ],
            'data' => $po,
        ]);
    }

    /**
     * Create new PO request (untuk Staff)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:100',
            'total_harga' => 'required|numeric|min:0',
        ]);

        $user = $request->user();

        $po = TrPo::create([
            'creator_email' => $user->email,
            'nama_barang' => $request->nama_barang,
            'total_harga' => $request->total_harga,
            'status' => TrPo::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request PO berhasil dibuat',
            'data' => $po,
        ], 201);
    }

    /**
     * Approve atau Reject oleh Manager
     */
    public function approve(Request $request, $id)
    {
        // Backward-compatible: endpoint lama tetap memanggil decision approve
        return $this->decision($request, $id, 'approve');
    }

    /**
     * Get detail PO
     */
    public function show($id)
    {
        $po = TrPo::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $po,
        ]);
    }
}

