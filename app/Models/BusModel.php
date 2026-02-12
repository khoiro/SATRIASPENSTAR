<?php

namespace App\Models;

use CodeIgniter\Model;

class BusModel extends Model
{
    protected $table            = 'bus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nama_bus',
        'jenjang',
        'kapasitas',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ===============================
    // Ambil bus + jumlah kursi terisi
    // ===============================
    public function getBusWithSeatCount($jenjang = null)
    {
        $builder = $this->db->table('bus b');
        $builder->select('
            b.*,
            COUNT(bb.id) as terisi
        ');
        $builder->join('bus_seat bs', 'bs.bus_id = b.id', 'left');
        $builder->join('booking_bus bb', 'bb.seat_id = bs.id', 'left');

        if ($jenjang) {
            $builder->where('b.jenjang', $jenjang);
        }

        $builder->groupBy('b.id');

        return $builder->get()->getResultArray();
    }

    public function processWeb($data, $id = null)
    {
        if ($id === null) {
            if ($this->insert($data)) {
                return $this->getInsertID();
            }
            return false;
        }

        if ($this->find($id)) {
            return $this->update($id, $data);
        }

        return false;
    }

    public function processSoftDelete($id)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, [
            'status' => 0
        ]);
    }



}
