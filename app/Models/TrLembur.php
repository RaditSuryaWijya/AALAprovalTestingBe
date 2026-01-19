<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrLembur extends Model
{
    use HasFactory;

    /**
     * Koneksi database untuk tr_lembur (db_app_lembur)
     */
    protected $connection = 'db_app_lembur';

    /**
     * Nama tabel
     */
    protected $table = 'tr_lembur';

    /**
     * Kolom yang dapat diisi
     */
    protected $fillable = [
        'requestor_email',
        'tanggal',
        'keterangan',
        'status',
        'spv_email',
        'tgl_approve_spv',
        'mgr_email',
        'tgl_approve_mgr',
        'reject_reason',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'tanggal' => 'date',
        'tgl_approve_spv' => 'datetime',
        'tgl_approve_mgr' => 'datetime',
    ];

    /**
     * Status yang valid
     */
    const STATUS_PENDING_SPV = 'PENDING_SPV';
    const STATUS_PENDING_MGR = 'PENDING_MGR';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
}

