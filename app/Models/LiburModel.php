<?php

namespace App\Models;

use CodeIgniter\Model;

class LiburModel extends Model
{
    protected $table = 'liburs';
    protected $primaryKey = 'id';

    public function getLiburBetween($start, $end)
    {
        return $this
            ->where('tanggal_mulai >=', $start)
            ->where('tanggal_akhir <=', $end)
            ->findAll();
    }
}
