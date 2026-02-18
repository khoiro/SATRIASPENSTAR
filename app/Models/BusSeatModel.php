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
        'status',
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

    public function processWeb($data, $id = null)
    {
        $payload = [
            'bus_id'      => $data['bus_id'],
            'nomor_kursi' => $data['nomor_kursi'],
            'baris'       => $data['baris'],
            'kolom'       => $data['kolom'],
            'posisi'      => $data['posisi'],
            'status'      => $data['status'],
        ];

        if ($id === null) {
            return $this->insert($payload);
        }

        if ($this->find($id)) {
            return $this->update($id, $payload);
        }

        return false;
    }

    public function processSoftDelete($id)
    {
        if ($this->find($id)) {
            return $this->update($id, [
                'status' => 0
            ]);
        }

        return false;
    }



}
