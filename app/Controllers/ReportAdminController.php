<?php

namespace App\Controllers;

use App\Models\ReportAdminModel;
use App\Models\AbsensiModel;
use CodeIgniter\I18n\Time;

class ReportAdminController extends BaseController
{
    protected $reportAdminModel;
    protected $AbsensiModel;

    public function __construct()
    {
        // Pastikan hanya siswa/user yang bisa mengakses
        // Jika ini untuk admin, sesuaikan pengecekan role
        if (service('login')->role !== 'admin') {
            // throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            // Atau redirect ke halaman login/dashboard
        }

        $this->reportAdminModel = new ReportAdminModel();
        $this->AbsensiModel = new AbsensiModel();
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
                    'status'     => $row->status,
                    'jam_masuk'  => $row->jam_masuk ?? '-',
                    'jam_keluar' => $row->jam_keluar ?? '-',
                    'koordinat_masuk' =>
                        $row->lokasi_lat && $row->lokasi_lng
                            ? $row->lokasi_lat . ',' . $row->lokasi_lng
                            : 'N/A',
                    'foto_masuk'  => $row->foto,
                    'foto_keluar' => $row->foto_keluar,
                    'foto_izin_sakit' => $row->foto_izin_sakit,
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

    public function statusAbsensi()
    {
        $data['kelasList'] = [
            'KELAS 7A','KELAS 7B','KELAS 7C','KELAS 7D','KELAS 7E','KELAS 7F','KELAS 7G','KELAS 7H',
            'KELAS 8A','KELAS 8B','KELAS 8C','KELAS 8D','KELAS 8E','KELAS 8F','KELAS 8G','KELAS 8H',
            'KELAS 9A','KELAS 9B','KELAS 9C','KELAS 9D','KELAS 9E','KELAS 9F','KELAS 9G','KELAS 9H',
        ];


        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $kelas = $this->request->getGet('kelas');

        $rekap = $this->AbsensiModel->getRekapAbsensiBulanan($bulan, $tahun,$kelas);

        return view('admin/report/reportstatusabsensi', [
            'rekap' => $rekap,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kelas' => $data['kelasList'],
            'page' => 'reportstatusabsensi', 
        ]);
    }

    public function ajaxRekap()
    {
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $kelas = $this->request->getGet('kelas'); // 🔥 ambil kelas

        $rekap = $this->AbsensiModel
            ->getRekapAbsensiBulanan($bulan, $tahun, $kelas);

        return $this->response->setJSON($rekap);
    }

    public function reportbookkamar()
    {
        $jenjangList = ['7','8','9'];

        $kelasList = [
            'KELAS 7A','KELAS 7B','KELAS 7C','KELAS 7D','KELAS 7E','KELAS 7F','KELAS 7G','KELAS 7H',
            'KELAS 8A','KELAS 8B','KELAS 8C','KELAS 8D','KELAS 8E','KELAS 8F','KELAS 8G','KELAS 8H',
            'KELAS 9A','KELAS 9B','KELAS 9C','KELAS 9D','KELAS 9E','KELAS 9F','KELAS 9G','KELAS 9H',
        ];

        $jenjang = $this->request->getGet('jenjang');
        $kelas   = $this->request->getGet('kelas');

        $kamarModel   = new \App\Models\KamarModel();
        $bookingModel = new \App\Models\BookingKamarModel();
        $siswaModel   = new \App\Models\SiswaModel();

        // === DATA KAMAR ===
        $dataKamar = [];

        $kamars = $kamarModel->getKamarWithStatus($jenjang);

        foreach ($kamars as $k) {
            $penghuni = $bookingModel->getPenghuniByKamar($k['id']);

            $dataKamar[] = [
                'nama_kamar' => 'Kamar ' . $k['nomor_kamar'],
                'kapasitas'  => $k['kapasitas'],
                'penghuni'   => $penghuni
            ];
        }
      

        // === SISWA BELUM BOOKING ===
        $siswaBelumBooking = $siswaModel->getSiswaBelumBooking($jenjang, $kelas);

        return view('admin/report/reportbookkamar', [
            'page' => 'reportbookkamar',
            'jenjangList' => $jenjangList,
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'dataKamar' => $dataKamar,
            'siswaBelumBooking' => $siswaBelumBooking
        ]);
    }

    public function printbookkamar()
    {
        $jenjang = $this->request->getGet('jenjang');
        $kelas   = $this->request->getGet('kelas');

        $kamarModel   = new \App\Models\KamarModel();
        $bookingModel = new \App\Models\BookingKamarModel();

        $dataKamar = [];

        $kamars = $kamarModel->getKamarWithStatus($jenjang);

        foreach ($kamars as $k) {

            // skip kamar kosong
            if ($k['terisi'] == 0) {
                continue;
            }

            // ===============================
            // JIKA KELAS DIPILIH → CEK SAJA
            // ===============================
            if ($kelas) {
                $adaKelas = $bookingModel
                    ->getPenghuniByKamarPrint($k['id'], $kelas);

                // kalau kamar ini tidak ada siswa kelas tsb → skip
                if (!$adaKelas) {
                    continue;
                }
            }

            // ===============================
            // AMBIL SEMUA PENGHUNI (TANPA FILTER KELAS)
            // ===============================
            $penghuni = $bookingModel->getPenghuniByKamarPrint(
                $k['id'],
                null
            );

            $dataKamar[] = [
                'nama_kamar' => 'Kamar ' . $k['nomor_kamar'],
                'kapasitas'  => $k['kapasitas'],
                'penghuni'   => $penghuni
            ];
        }

        return view('admin/report/printbookkamar', [
            'jenjang'   => $jenjang,
            'kelas'     => $kelas,
            'dataKamar' => $dataKamar
        ]);
    }

    public function printbookkamar2()
    {
        $jenjang = $this->request->getGet('jenjang');
        $kelas   = $this->request->getGet('kelas');

        $db = \Config\Database::connect();

        $builder = $db->table('booking_kamar bk')
            ->select('
                s.nama AS nama_siswa,
                s.rombel,
                k.nomor_kamar,
                k.id AS kamar_id
            ')
            ->join('siswa s', 's.id = bk.siswa_id')
            ->join('kamar k', 'k.id = bk.kamar_id')
            ->where('k.status', 1);

        if ($jenjang) {
            $builder->where('k.jenjang', $jenjang);
        }

        if ($kelas) {
            $builder->where('s.rombel', $kelas);
        }

        $rows = $builder
            ->orderBy('k.nomor_kamar')
            ->orderBy('s.nama')
            ->get()
            ->getResultArray();

        // ambil penghuni per kamar (cache biar hemat query)
        $penghuniKamar = [];

        foreach ($rows as $r) {
            if (!isset($penghuniKamar[$r['kamar_id']])) {
                $penghuniKamar[$r['kamar_id']] = $db->table('booking_kamar bk')
                    ->select('s.nama, s.rombel')
                    ->join('siswa s', 's.id = bk.siswa_id')
                    ->where('bk.kamar_id', $r['kamar_id'])
                    ->get()
                    ->getResultArray();
            }
        }

        return view('admin/report/printbookkamar2', [
            'rows' => $rows,
            'penghuniKamar' => $penghuniKamar,
            'jenjang' => $jenjang,
            'kelas' => $kelas
        ]);
    }








}