<?php

namespace App\Controllers;

use App\Models\AbsensiModel;

class Presensi extends BaseController
{
    public function index()
    {
        $model = new AbsensiModel();
        $data = [
            'user' => [
                'nama' => 'MUKHAMAD KHOIRONI',
                'foto' => base_url('uploads/foto.jpg'),
                'lokasi' => ['lat' => -7.1234, 'lng' => 112.4567]
            ],
            'presensi' => [
                'tanggal' => date('Y-m-d'),
                'masuk' => date('H:i:s'),
                'keluar' => '-',
            ]
        ];
        return view('absensi/index', $data);
    }
}
