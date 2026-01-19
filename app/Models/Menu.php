<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * Koneksi database untuk menus (db_central_auth)
     */
    protected $connection = 'db_central_auth';

    /**
     * Nama tabel
     */
    protected $table = 'menus';

    /**
     * Kolom yang dapat diisi
     */
    protected $fillable = [
        'label',
        'icon',
        'menu_link',
        'jabatan',
        'department',
        'order',
        'is_active',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope untuk menu aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan jabatan
     */
    public function scopeForJabatan($query, $jabatan)
    {
        return $query->where(function ($q) use ($jabatan) {
            $q->where('jabatan', $jabatan)
              ->orWhereNull('jabatan');
        });
    }

    /**
     * Scope untuk filter berdasarkan department
     */
    public function scopeForDepartment($query, $department, $isIT = false)
    {
        if ($isIT) {
            // Jika IT, ambil menu dengan department IT atau null
            return $query->where(function ($q) {
                $q->where('department', 'IT')
                  ->orWhereNull('department');
            });
        } else {
            // Jika non-IT, ambil menu dengan department null atau sesuai
            return $query->where(function ($q) use ($department) {
                $q->whereNull('department')
                  ->orWhere('department', $department);
            });
        }
    }

    /**
     * Scope untuk urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
