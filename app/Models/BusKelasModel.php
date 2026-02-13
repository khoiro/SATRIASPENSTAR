<?php

namespace App\Models;

use CodeIgniter\Model;

class BusKelasModel extends Model
{
    protected $table            = 'bus_kelas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'bus_id',
        'rombel',
        'created_at'
    ];

    protected $useTimestamps = false; 
    
    public function processWeb($data, $id = null)
    {
        // Cek apakah sudah ada kombinasi bus_id + rombel
        $builder = $this->where('bus_id', $data['bus_id'])
                        ->where('rombel', $data['rombel']);

        if ($id !== null) {
            $builder->where('id !=', $id);
        }

        if ($builder->first()) {
            return 'duplicate';
        }

        if ($id === null) {
            return $this->insert($data);
        }

        return $this->update($id, $data);
    }

    public function processHardDelete($id)
    {
        return $this->delete($id, true); 
    }

}
