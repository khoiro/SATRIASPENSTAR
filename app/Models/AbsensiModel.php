<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'nisn', 'tanggal', 'jam_masuk','jam_keluar',
        'lokasi_lat', 'lokasi_lng','lokasi_lat_keluar','lokasi_lng_keluar', 'foto','foto_keluar', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function cekStatusAbsensi($userId, $tanggal = null)
    {
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }

        $absensi = $this->where('user_id', $userId)
                        ->where('tanggal', $tanggal)
                        ->first();

        return [
            'masuk' => $absensi && $absensi['jam_masuk'] !== null,
            'keluar' => $absensi && $absensi['jam_keluar'] !== null,
            'data' => $absensi
        ];
    }

}
