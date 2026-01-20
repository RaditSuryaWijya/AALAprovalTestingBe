<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrPo extends Model
{
    use HasFactory;

    /**
     * Koneksi database untuk tr_po (db_app_po)
     */
    protected $connection = 'db_app_absensi';

    /**
     * Nama tabel
     */
    protected $table = 'tr_absensi';

    /**
     * Kolom yang dapat diisi
     */
    protected $fillable = [
        'requestor_email',
        'tanggal_absen',
        'jenis_absensi',
        'jam_yang_diajukan',
        'keterangan',
        'status',
        'spv_email',
        'tgl_approve',
        'reject_reason',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'tanggal_absen'   => 'date',
        'tgl_approve_spv' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Status yang valid
     */
    const STATUS_MENUNGGU         = 'MENUNGGU';
    const STATUS_SETUJU_ATASAN    = 'DISETUJUI ATASAN';
    const STATUS_SETUJU_KOOR      = 'DISETUJUI KOOR';
    const STATUS_SETUJU_KADEPT    = 'DISETUJUI KADEPT';
    const STATUS_SETUJU_HRD       = 'DISETUJUI HRD';
    const STATUS_REJECTED         = 'REJECTED';
}

