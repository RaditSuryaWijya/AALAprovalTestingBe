<?php

namespace App\Http\Controllers;

use App\Models\TrLembur;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    /**
     * Endpoint generic: approve (Double Validation)
     * POST /api/lembur/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        return $this->decision($request, $id, 'approve');
    }

    /**
     * Endpoint generic: reject (Double Validation)
     * POST /api/lembur/{id}/reject
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

        $lembur = TrLembur::findOrFail($id);

        // Double Validation: status menentukan "giliran siapa"
        if ($lembur->status === TrLembur::STATUS_PENDING_SPV) {
            // Double Validation: role yang sesuai (Supervisor IT)
            if ($jabatan !== 'Supervisor' || $department !== 'IT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki akses untuk memproses approval lembur (menunggu Supervisor IT)',
                ], 403);
            }

            if ($action === 'approve') {
                $lembur->update([
                    'status' => TrLembur::STATUS_PENDING_MGR,
                    'spv_email' => $user->email,
                    'tgl_approve_spv' => now(),
                ]);
            } else {
                $lembur->update([
                    'status' => TrLembur::STATUS_REJECTED,
                    'spv_email' => $user->email,
                    'tgl_approve_spv' => now(),
                    'reject_reason' => $request->reject_reason,
                ]);
            }
        } elseif ($lembur->status === TrLembur::STATUS_PENDING_MGR) {
            // Double Validation: role yang sesuai (Manager IT)
            if ($jabatan !== 'Manager' || $department !== 'IT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki akses untuk memproses approval lembur (menunggu Manager IT)',
                ], 403);
            }

            if ($action === 'approve') {
                $lembur->update([
                    'status' => TrLembur::STATUS_APPROVED,
                    'mgr_email' => $user->email,
                    'tgl_approve_mgr' => now(),
                ]);
            } else {
                $lembur->update([
                    'status' => TrLembur::STATUS_REJECTED,
                    'mgr_email' => $user->email,
                    'tgl_approve_mgr' => now(),
                    'reject_reason' => $request->reject_reason,
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen lembur tidak sedang menunggu approval',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $action === 'approve'
                ? 'Request lembur berhasil diapprove'
                : 'Request lembur berhasil ditolak',
            'data' => $lembur,
        ]);
    }

    /**
     * Get list lembur berdasarkan jabatan user
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

        $query = TrLembur::query();

        // Filter berdasarkan jabatan
        if ($jabatan === 'Supervisor' && $department === 'IT') {
            $query->where('status', TrLembur::STATUS_PENDING_SPV);
        } elseif ($jabatan === 'Manager' && $department === 'IT') {
            $query->where('status', TrLembur::STATUS_PENDING_MGR);
        } else {
            // Staff hanya bisa melihat request mereka sendiri
            $query->where('requestor_email', $user->email);
        }

        $lembur = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'config' => [
                'page_title' => 'Daftar Approval Lembur',
                'mapping' => [
                    'title' => 'requestor_email',
                    'subtitle' => 'keterangan',
                    'date' => 'tanggal',
                    'status' => 'status',
                ],
            ],
            'data' => $lembur,
        ]);
    }

    /**
     * Create new lembur request (untuk Staff)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        $user = $request->user();

        $lembur = TrLembur::create([
            'requestor_email' => $user->email,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'status' => TrLembur::STATUS_PENDING_SPV,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request lembur berhasil dibuat',
            'data' => $lembur,
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

        $lembur = TrLembur::findOrFail($id);

        // Cek apakah status sesuai
        if ($lembur->status !== TrLembur::STATUS_PENDING_SPV) {
            return response()->json([
                'success' => false,
                'message' => 'Status request tidak valid untuk approval Supervisor',
            ], 400);
        }

        if ($request->action === 'approve') {
            $lembur->update([
                'status' => TrLembur::STATUS_PENDING_MGR,
                'spv_email' => $user->email,
                'tgl_approve_spv' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request lembur berhasil diapprove oleh Supervisor',
                'data' => $lembur,
            ]);
        } else {
            $lembur->update([
                'status' => TrLembur::STATUS_REJECTED,
                'spv_email' => $user->email,
                'tgl_approve_spv' => now(),
                'reject_reason' => $request->reject_reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request lembur ditolak oleh Supervisor',
                'data' => $lembur,
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

        $lembur = TrLembur::findOrFail($id);

        // Cek apakah status sesuai
        if ($lembur->status !== TrLembur::STATUS_PENDING_MGR) {
            return response()->json([
                'success' => false,
                'message' => 'Status request tidak valid untuk approval Manager',
            ], 400);
        }

        if ($request->action === 'approve') {
            $lembur->update([
                'status' => TrLembur::STATUS_APPROVED,
                'mgr_email' => $user->email,
                'tgl_approve_mgr' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request lembur berhasil diapprove oleh Manager',
                'data' => $lembur,
            ]);
        } else {
            $lembur->update([
                'status' => TrLembur::STATUS_REJECTED,
                'mgr_email' => $user->email,
                'tgl_approve_mgr' => now(),
                'reject_reason' => $request->reject_reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request lembur ditolak oleh Manager',
                'data' => $lembur,
            ]);
        }
    }

    /**
     * Get detail lembur
     */
    public function show($id)
    {
        $lembur = TrLembur::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $lembur,
        ]);
    }
}

