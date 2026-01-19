<?php

namespace App\Http\Controllers;

use App\Models\TrCuti;
use Illuminate\Http\Request;

class CutiController extends Controller
{
    /**
     * Endpoint generic: approve (Double Validation)
     * POST /api/cuti/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        return $this->decision($request, $id, 'approve');
    }

    /**
     * Endpoint generic: reject (Double Validation)
     * POST /api/cuti/{id}/reject
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

        $cuti = TrCuti::findOrFail($id);

        // Double Validation: status menentukan "giliran siapa"
        if ($cuti->status === TrCuti::STATUS_PENDING_SPV) {
            // Double Validation: role yang sesuai (Supervisor HRD)
            if ($jabatan !== 'Supervisor' || $department !== 'HRD') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki akses untuk memproses approval cuti (menunggu Supervisor HRD)',
                ], 403);
            }

            if ($action === 'approve') {
                $cuti->update([
                    'status' => TrCuti::STATUS_PENDING_MGR,
                    'spv_email' => $user->email,
                    'tgl_approve_spv' => now(),
                ]);
            } else {
                $cuti->update([
                    'status' => TrCuti::STATUS_REJECTED,
                    'spv_email' => $user->email,
                    'tgl_approve_spv' => now(),
                    'reject_reason' => $request->reject_reason,
                ]);
            }
        } elseif ($cuti->status === TrCuti::STATUS_PENDING_MGR) {
            // Double Validation: role yang sesuai (Manager HRD)
            if ($jabatan !== 'Manager' || $department !== 'HRD') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki akses untuk memproses approval cuti (menunggu Manager HRD)',
                ], 403);
            }

            if ($action === 'approve') {
                $cuti->update([
                    'status' => TrCuti::STATUS_APPROVED,
                    'mgr_email' => $user->email,
                    'tgl_approve_mgr' => now(),
                ]);
            } else {
                $cuti->update([
                    'status' => TrCuti::STATUS_REJECTED,
                    'mgr_email' => $user->email,
                    'tgl_approve_mgr' => now(),
                    'reject_reason' => $request->reject_reason,
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen cuti tidak sedang menunggu approval',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $action === 'approve'
                ? 'Request cuti berhasil diapprove'
                : 'Request cuti berhasil ditolak',
            'data' => $cuti,
        ]);
    }

    /**
     * Get list cuti berdasarkan jabatan user
     * - Supervisor: hanya melihat status PENDING_SPV
     * - Manager: hanya melihat status PENDING_MGR
     * 
     * Response menggunakan struktur Server-Driven UI yang standar
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $jabatan = $user->jabatan;
        $department = $user->department;

        $query = TrCuti::query();

        // Filter berdasarkan jabatan
        if ($jabatan === 'Supervisor' && $department === 'HRD') {
            $query->where('status', TrCuti::STATUS_PENDING_SPV);
        } elseif ($jabatan === 'Manager' && $department === 'HRD') {
            $query->where('status', TrCuti::STATUS_PENDING_MGR);
        } else {
            // Staff hanya bisa melihat request mereka sendiri
            $query->where('requestor_email', $user->email);
        }

        $cuti = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'config' => [
                'page_title' => 'Daftar Approval Cuti',
                'mapping' => [
                    'title' => 'requestor_email',
                    'subtitle' => 'keterangan',
                    'date' => 'tanggal',
                    'status' => 'status',
                ],
            ],
            'data' => $cuti,
        ]);
    }

    /**
     * Create new cuti request (untuk Staff)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        $user = $request->user();

        $cuti = TrCuti::create([
            'requestor_email' => $user->email,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'status' => TrCuti::STATUS_PENDING_SPV,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request cuti berhasil dibuat',
            'data' => $cuti,
        ], 201);
    }

    /**
     * Approve atau Reject oleh Supervisor
     */
    public function approveBySupervisor(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reject_reason' => 'required_if:action,reject|string|nullable',
        ]);

        $user = $request->user();

        // Cek apakah user adalah Supervisor
        if ($user->jabatan !== 'Supervisor') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Supervisor yang dapat melakukan approval ini',
            ], 403);
        }

        $cuti = TrCuti::findOrFail($id);

        // Cek apakah status sesuai
        if ($cuti->status !== TrCuti::STATUS_PENDING_SPV) {
            return response()->json([
                'success' => false,
                'message' => 'Status request tidak valid untuk approval Supervisor',
            ], 400);
        }

        if ($request->action === 'approve') {
            $cuti->update([
                'status' => TrCuti::STATUS_PENDING_MGR,
                'spv_email' => $user->email,
                'tgl_approve_spv' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request cuti berhasil diapprove oleh Supervisor',
                'data' => $cuti,
            ]);
        } else {
            $cuti->update([
                'status' => TrCuti::STATUS_REJECTED,
                'spv_email' => $user->email,
                'tgl_approve_spv' => now(),
                'reject_reason' => $request->reject_reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request cuti ditolak oleh Supervisor',
                'data' => $cuti,
            ]);
        }
    }

    /**
     * Approve atau Reject oleh Manager
     */
    public function approveByManager(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reject_reason' => 'required_if:action,reject|string|nullable',
        ]);

        $user = $request->user();

        // Cek apakah user adalah Manager
        if ($user->jabatan !== 'Manager') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Manager yang dapat melakukan approval ini',
            ], 403);
        }

        $cuti = TrCuti::findOrFail($id);

        // Cek apakah status sesuai
        if ($cuti->status !== TrCuti::STATUS_PENDING_MGR) {
            return response()->json([
                'success' => false,
                'message' => 'Status request tidak valid untuk approval Manager',
            ], 400);
        }

        if ($request->action === 'approve') {
            $cuti->update([
                'status' => TrCuti::STATUS_APPROVED,
                'mgr_email' => $user->email,
                'tgl_approve_mgr' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request cuti berhasil diapprove oleh Manager',
                'data' => $cuti,
            ]);
        } else {
            $cuti->update([
                'status' => TrCuti::STATUS_REJECTED,
                'mgr_email' => $user->email,
                'tgl_approve_mgr' => now(),
                'reject_reason' => $request->reject_reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request cuti ditolak oleh Manager',
                'data' => $cuti,
            ]);
        }
    }

    /**
     * Get detail cuti
     */
    public function show($id)
    {
        $cuti = TrCuti::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cuti,
        ]);
    }
}
