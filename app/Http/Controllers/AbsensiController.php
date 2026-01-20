<?php

namespace App\Http\Controllers;

use App\Models\TrAbsensi;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * Endpoint generic: approve
     */
    public function approve(Request $request, $id)
    {
        return $this->decision($request, $id, 'approve');
    }

    /**
     * Endpoint generic: reject
     */
    public function reject(Request $request, $id)
    {
        return $this->decision($request, $id, 'reject');
    }

    /**
     * Core Logic untuk Multi-Level Approval (Atasan -> Koor -> Kadept -> HRD)
     */
    protected function decision(Request $request, $id, string $action)
    {
        if (!in_array($action, ['approve', 'reject'], true)) {
            return response()->json(['success' => false, 'message' => 'Action tidak valid'], 400);
        }

        if ($action === 'reject') {
            $request->validate(['reject_reason' => 'required|string']);
        }

        $user = $request->user();
        $jabatan = $user->jabatan;
        $absensi = TrAbsensi::findOrFail($id);

        // --- STEP 1: MENUNGGU -> ATASAN ---
        if ($absensi->status === TrAbsensi::STATUS_MENUNGGU) {
            if ($jabatan !== 'Atasan') {
                return response()->json(['success' => false, 'message' => 'Menunggu approval Atasan'], 403);
            }
            $nextStatus = ($action === 'approve') ? TrAbsensi::STATUS_SETUJU_ATASAN : TrAbsensi::STATUS_REJECTED;
        } 
        // --- STEP 2: DISETUJUI ATASAN -> KOOR ---
        elseif ($absensi->status === TrAbsensi::STATUS_SETUJU_ATASAN) {
            if ($jabatan !== 'Koor') {
                return response()->json(['success' => false, 'message' => 'Menunggu approval Koor'], 403);
            }
            $nextStatus = ($action === 'approve') ? TrAbsensi::STATUS_SETUJU_KOOR : TrAbsensi::STATUS_REJECTED;
        }
        // --- STEP 3: DISETUJUI KOOR -> KADEPT ---
        elseif ($absensi->status === TrAbsensi::STATUS_SETUJU_KOOR) {
            if ($jabatan !== 'Kadept') {
                return response()->json(['success' => false, 'message' => 'Menunggu approval Kadept'], 403);
            }
            $nextStatus = ($action === 'approve') ? TrAbsensi::STATUS_SETUJU_KADEPT : TrAbsensi::STATUS_REJECTED;
        }
        // --- STEP 4: DISETUJUI KADEPT -> HRD ---
        elseif ($absensi->status === TrAbsensi::STATUS_SETUJU_KADEPT) {
            if ($jabatan !== 'HRD') {
                return response()->json(['success' => false, 'message' => 'Menunggu approval HRD'], 403);
            }
            $nextStatus = ($action === 'approve') ? TrAbsensi::STATUS_SETUJU_HRD : TrAbsensi::STATUS_REJECTED;
        }
        else {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak sedang menunggu approval'], 400);
        }

        // Eksekusi Update
        $absensi->update([
            'status' => $nextStatus,
            'spv_email' => $user->email,
            'tgl_approve_spv' => now(),
            'reject_reason' => ($action === 'reject') ? $request->reject_reason : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Request absensi berhasil di-{$action}",
            'data' => $absensi,
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $jabatan = $user->jabatan;
        $query = TrAbsensi::query();

        if ($jabatan === 'Atasan') {
            $query->where('status', TrAbsensi::STATUS_MENUNGGU);
        } elseif ($jabatan === 'Koor') {
            $query->where('status', TrAbsensi::STATUS_SETUJU_ATASAN);
        } elseif ($jabatan === 'Kadept') {
            $query->where('status', TrAbsensi::STATUS_SETUJU_KOOR);
        } elseif ($jabatan === 'HRD') {
            $query->where('status', TrAbsensi::STATUS_SETUJU_KADEPT);
        } else {
            $query->where('requestor_email', $user->email);
        }

        return response()->json([
            'success' => true,
            'config' => [
                'page_title' => 'Daftar Pengajuan Absensi',
                'mapping' => [
                    'title' => 'requestor_email',
                    'subtitle' => 'keterangan',
                    'date' => 'tanggal_absen',
                    'status' => 'status',
                ],
            ],
            'data' => $query->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_absen' => 'required|date',
            'jenis_absensi' => 'required|in:MASUK,PULANG,FULL_DAY',
            'jam_yang_diajukan' => 'required',
            'keterangan' => 'required|string',
        ]);

        $absensi = TrAbsensi::create([
            'requestor_email' => $request->user()->email,
            'tanggal_absen' => $request->tanggal_absen,
            'jenis_absensi' => $request->jenis_absensi,
            'jam_yang_diajukan' => $request->jam_yang_diajukan,
            'keterangan' => $request->keterangan,
            'status' => TrAbsensi::STATUS_MENUNGGU,
        ]);

        return response()->json(['success' => true, 'message' => 'Berhasil dibuat', 'data' => $absensi], 201);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => TrAbsensi::findOrFail($id)]);
    }
}