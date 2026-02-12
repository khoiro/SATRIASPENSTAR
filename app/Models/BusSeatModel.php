<?php

namespace App\Models;

use CodeIgniter\Model;

class BusSeatModel extends Model
{
    protected $table            = 'bus_seat';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'bus_id',
        'nomor_kursi',
        'baris',
        'kolom',
        'posisi',
        'created_at'
    ];

    protected $useTimestamps = false;

    // ===============================
    // Ambil kursi berdasarkan bus
    // ===============================
    public function getSeatByBus($busId)
    {
        return $this->where('bus_id', $busId)
                    ->orderBy('baris', 'ASC')
                    ->orderBy('kolom', 'ASC')
                    ->findAll();
    }
}
