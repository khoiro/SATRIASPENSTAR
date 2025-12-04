<?php

namespace App\Controllers;

use App\Models\ReportModel;
use CodeIgniter\I18n\Time;

class ReportController extends BaseController
{
    protected $reportModel;

    public function __construct()
    {
        // Pastikan hanya siswa/user yang bisa mengakses
        // Jika ini untuk admin, sesuaikan pengecekan role
        if (service('login')->role !== 'siswa') {
            // throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            // Atau redirect ke halaman login/dashboard
        }

        $this->reportModel = new ReportModel();
    }

    /**
     * Memuat view report dan mengatur periode default
     */
    public function index()
    {
        $now = Time::now();
        
        // Default: Awal bulan ini (start_date) hingga hari ini (end_date)
        $startDate = $now->getYear() . '-' . $now->getMonth() . '-01';
        $endDate = $now->toDateString();

        $data = [
            'page' => 'report_absensi', // Untuk menandai menu aktif
            'startDate' => $startDate,
            'endDate' => $endDate,
            // Header untuk Datatables Server-Side
            'columns' => [
                'tanggal' => 'Tanggal',
                'jam_masuk' => 'Masuk',
                'jam_keluar' => 'Keluar',
                'lokasi_lat' => 'Lokasi Masuk',
                // Anda bisa menambahkan kolom lain sesuai kebutuhan
            ]
        ];

        return view('siswa/report/index', $data);
    }

    /**
     * Endpoint untuk Datatables Server-Side
     */
    public function get_absensi()
    {
        if ($this->request->isAJAX()) {
            
            $input = $this->request->getPost();
            // Asumsi service('login')->id mengembalikan ID siswa yang benar
            $userId = service('login')->id; 
            
            $result = $this->reportModel->getDatatables($input, $userId);

            $data = [];
            $no = $this->request->getPost('start');

            foreach ($result['data'] as $row) {
                $no++;
                $data[] = [
                    'no' => $no,
                    'tanggal' => Time::parse($row->tanggal)->toLocalizedString('d MMMM yyyy'),
                    'jam_masuk' => $row->jam_masuk ?? '-',
                    'jam_keluar' => $row->jam_keluar ?? '-',
                    
                    'koordinat_masuk' => $row->lokasi_lat && $row->lokasi_lng 
                                         ? $row->lokasi_lat . ',' . $row->lokasi_lng 
                                         : 'N/A',
                    
                    // 🚀 TAMBAHKAN FOTO DAN FOTO KELUAR
                    'foto_masuk' => $row->foto,
                    'foto_keluar' => $row->foto_keluar,
                ];
            }

            $output = [
                "draw" => $this->request->getPost('draw'),
                "recordsTotal" => $result['recordsTotal'],
                "recordsFiltered" => $result['recordsFiltered'],
                "data" => $data,
            ];

            return $this->response->setJSON($output);
        }
        
        return $this->response->setStatusCode(403);
    }
}