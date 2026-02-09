<?php

namespace App\Models;

use CodeIgniter\Model;

class KamarModel extends Model
{
    protected $table = 'kamar';
    protected $allowedFields = ['kegiatan_id','jenjang','nomor_kamar','kapasitas'];

    public function getKamarWithStatus($jenjang)
    {
        return $this->db->table('kamar k')
            ->select('k.id, k.nomor_kamar, k.kapasitas, COUNT(b.id) AS terisi')
            ->join('booking_kamar b', 'b.kamar_id = k.id', 'left')
            ->where('k.jenjang', $jenjang)
            ->groupBy('k.id')
            ->get()
            ->getResultArray();
    }
}
