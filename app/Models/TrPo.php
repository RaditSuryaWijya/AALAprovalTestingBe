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
    protected $connection = 'db_app_po';

    /**
     * Nama tabel
     */
    protected $table = 'tr_po';

    /**
     * Kolom yang dapat diisi
     */
    protected $fillable = [
        'creator_email',
        'nama_barang',
        'total_harga',
        'status',
        'approver_email',
        'tgl_approve',
        'reject_reason',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'total_harga' => 'decimal:2',
        'tgl_approve' => 'datetime',
    ];

    /**
     * Status yang valid
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
}

