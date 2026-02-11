<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\BookingKamar;

class BookingKamarModel extends Model
{
    protected $table      = 'booking_kamar';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'kamar_id',
        'siswa_id',
    ];

    protected $returnType = BookingKamar::class;

    public function getPenghuniByKamar($kamarId)
    {
        return $this->db->table('booking_kamar bk')
            ->select('s.nama AS nama_siswa,s.rombel')
            ->join('siswa s', 's.id = bk.siswa_id')
            ->where('bk.kamar_id', $kamarId)
            ->get()
            ->getResultArray();
    }

    public function getPenghuniByKamarPrint($kamarId, $kelas = null)
    {
        $builder = $this->db->table('booking_kamar bk')
            ->select('s.nama AS nama_siswa, s.rombel')
            ->join('siswa s', 's.id = bk.siswa_id')
            ->where('bk.kamar_id', $kamarId);

        if ($kelas) {
            $builder->where('s.rombel', $kelas);
        }

        return $builder->get()->getResultArray();
    }


}
