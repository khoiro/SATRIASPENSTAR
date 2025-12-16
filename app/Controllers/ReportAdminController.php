<?php

namespace App\Controllers;

use App\Models\ReportAdminModel;
use CodeIgniter\I18n\Time;

class ReportAdminController extends BaseController
{
    protected $reportAdminModel;

    public function __construct()
    {
        // Pastikan hanya siswa/user yang bisa mengakses
        // Jika ini untuk admin, sesuaikan pengecekan role
        if (service('login')->role !== 'admin') {
            // throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            // Atau redirect ke halaman login/dashboard
        }

        $this->reportAdminModel = new ReportAdminModel();
    }

    public function index()
    {
        $now = Time::now();
        
        // Default: Awal bulan ini (start_date) hingga hari ini (end_date)
        $startDate = $now->getYear() . '-' . $now->getMonth() . '-01';
        $endDate = $now->toDateString();

        // Data kelas
        $kelasList = [
            'KELAS 7A','KELAS 7B','KELAS 7C','KELAS 7D','KELAS 7E','KELAS 7F','KELAS 7G','KELAS 7H',
            'KELAS 8A','KELAS 8B','KELAS 8C','KELAS 8D','KELAS 8E','KELAS 8F','KELAS 8G','KELAS 8H',
            'KELAS 9A','KELAS 9B','KELAS 9C','KELAS 9D','KELAS 9E','KELAS 9F','KELAS 9G','KELAS 9H',
        ];

        $data = [
            'page' => 'report_absensi', // Untuk menandai menu aktif
            'startDate' => $startDate,
            'endDate' => $endDate,
            'kelasList' => $kelasList,
            'kelas'     => '', // default (semua kelas)
            // Header untuk Datatables Server-Side
            'columns' => [
                'tanggal' => 'Tanggal',
                'jam_masuk' => 'Masuk',
                'jam_keluar' => 'Keluar',
                'lokasi_lat' => 'Lokasi Masuk',
                // Anda bisa menambahkan kolom lain sesuai kebutuhan
            ]
        ];

        return view('admin/report/index', $data);
    }

    public function get_absensi()
    {
        if ($this->request->isAJAX()) {

            $input  = $this->request->getPost();
            // 🔥 ambil kelas dari request
            $kelas = $input['kelas'] ?? null;
            $nisn  = $input['nisn'] ?? null;

            $result = $this->reportAdminModel->getDatatables($input, $kelas,$nisn);

            $data = [];
            $no   = $this->request->getPost('start');

            foreach ($result['data'] as $row) {
                $no++;
                $data[] = [
                    'no'         => $no,
                    'nama'       => $row->nama,
                    'tanggal'    => Time::parse($row->tanggal)->toLocalizedString('d MMMM yyyy'),
                    'jam_masuk'  => $row->jam_masuk ?? '-',
                    'jam_keluar' => $row->jam_keluar ?? '-',
                    'koordinat_masuk' =>
                        $row->lokasi_lat && $row->lokasi_lng
                            ? $row->lokasi_lat . ',' . $row->lokasi_lng
                            : 'N/A',
                    'foto_masuk'  => $row->foto,
                    'foto_keluar' => $row->foto_keluar,
                ];
            }

            return $this->response->setJSON([
                "draw"            => $this->request->getPost('draw'),
                "recordsTotal"    => $result['recordsTotal'],
                "recordsFiltered" => $result['recordsFiltered'],
                "data"            => $data,
            ]);
        }

        return $this->response->setStatusCode(403);
    }

     /**
     * DROPDOWN SISWA BERDASARKAN KELAS
     */
    public function getSiswaByKelas()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        $kelas = $this->request->getPost('kelas');

        $db = \Config\Database::connect();

        $data = $db->table('siswa')
            ->select(['nisn', 'nama'])
            ->where('rombel', $kelas)
            ->where('status', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }



}