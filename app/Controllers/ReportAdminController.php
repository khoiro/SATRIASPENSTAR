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
            'jenjang' => $jenjang,
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
                s.telp_siswa,
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

    public function reportbookseat()
    {
        $jenjangList = ['7','8','9'];

        $kelasList = [
            'KELAS 7A','KELAS 7B','KELAS 7C','KELAS 7D','KELAS 7E','KELAS 7F','KELAS 7G','KELAS 7H',
            'KELAS 8A','KELAS 8B','KELAS 8C','KELAS 8D','KELAS 8E','KELAS 8F','KELAS 8G','KELAS 8H',
            'KELAS 9A','KELAS 9B','KELAS 9C','KELAS 9D','KELAS 9E','KELAS 9F','KELAS 9G','KELAS 9H',
        ];

        $jenjang = $this->request->getGet('jenjang');
        $kelas   = $this->request->getGet('kelas');

        $busModel     = new \App\Models\BusModel();
        $seatModel    = new \App\Models\BusSeatModel();
        $bookingModel = new \App\Models\BookingBusModel();
        $siswaModel   = new \App\Models\SiswaModel();

        $busList = [];

        if ($jenjang && $kelas) {

            // =========================
            // AMBIL BUS SESUAI KELAS
            // =========================
            $busData = $busModel
                    ->select('bus.*')
                    ->join('bus_kelas', 'bus_kelas.bus_id = bus.id')
                    ->where('bus.jenjang', $jenjang)
                    ->where('bus_kelas.rombel', $kelas)
                    ->where('bus.status', 1)
                    ->findAll();

            foreach ($busData as $bus) {
                
                    // HARDCODE BLOKIR BERDASARKAN NOMOR KURSI
                    // ======================
                    $lockedSeats = ['3', '4', '21', '22']; 
                    // ini adalah NOMOR KURSI (field nomor_kursi)

                    if (in_array($bus['id'], [1, 7, 13]))  {
                         $lockedSeats = ['1','2', '3', '4', '21', '22','48']; 
                    }

                    $seats = $seatModel
                        ->where('bus_id', $bus['id'])
                        ->orderBy('baris','ASC')
                        ->orderBy('kolom','ASC')
                        ->findAll();

                    $bookings = $bookingModel
                        ->select('booking_bus.*, siswa.nama, siswa.kelas, siswa.rombel')
                        ->join('siswa', 'siswa.id = booking_bus.siswa_id')
                        ->where('booking_bus.bus_id', $bus['id'])
                        ->findAll();

                    $bookingMap = [];
                    foreach ($bookings as $b) {
                        $bookingMap[$b['seat_id']] = $b;
                    }

                    foreach ($seats as &$seat) {

                        $seat['is_booked']  = false;
                        $seat['is_blocked'] = false;
                        $seat['booked_by']  = null;

                        // 🔒 CEK BLOKIR BERDASARKAN NOMOR KURSI
                        if (in_array($seat['nomor_kursi'], $lockedSeats)) {
                            $seat['is_blocked'] = true;
                        }

                        // 🔴 CEK BOOKING
                        if (isset($bookingMap[$seat['id']])) {
                            $seat['is_booked'] = true;
                            $seat['booked_by'] = $bookingMap[$seat['id']]['nama'];
                        }
                    }

                    $bus['seats'] = $seats;
                    $busList[] = $bus;
            }
        }

        // =========================
        // SISWA BELUM BOOKING
        // =========================
        $siswaBelumBooking = [];

        if ($jenjang && $kelas) {
            $siswaBelumBooking = $siswaModel
                ->select('siswa.*')
                ->join('booking_bus','booking_bus.siswa_id = siswa.id','left')
                ->where('siswa.kelas', $jenjang)
                ->where('siswa.rombel', $kelas)
                ->where('booking_bus.id IS NULL')
                ->findAll();
        }

        // =========================
        // AMBIL SEMUA BUS UNTUK DROPDOWN CETAK
        // =========================
        $allBus = $busModel
            ->where('status', 1)
            ->findAll();

        return view('admin/report/reportbookseat',[
            'page' => 'reportbookseat',
            'jenjangList' => $jenjangList,
            'kelasList'   => $kelasList,
            'jenjang'     => $jenjang,
            'kelas'       => $kelas,
            'busList'     => $busList,
            'allBus'      => $allBus,
            'siswaBelumBooking' => $siswaBelumBooking
        ]);
    }

    public function printbus()
    {
         $busId = $this->request->getGet('bus_id');
         
        /* ===============================
        DEFAULT LOCKED SEAT (SEMUA BUS)
        =============================== */
        $lockedSeats = [
            '3'  => 'Pendamping 1',
            '4'  => 'Pendamping 2',
            '21' => 'Pendamping 3',
            '22' => 'Cadangan',
        ];

        /* ===============================
        KHUSUS BUS ID = 1
        =============================== */
        if (in_array($busId, [1, 7, 13])) {
            $lockedSeats = [
                '1'  => 'Kepala Sekolah',
                '2'  => 'Komite 1',
                '3'  => 'Komite 2',
                '4' => 'Pendamping 1',
                '21' => 'Pendamping 2',
                '22' => 'Pendamping 3'
            ];
        }

        $busModel     = new \App\Models\BusModel();
        $seatModel    = new \App\Models\BusSeatModel();
        $bookingModel = new \App\Models\BookingBusModel();

        $bus = $busModel->find($busId);

        $seats = $seatModel
            ->where('bus_id', $busId)
            ->orderBy('baris','ASC')
            ->orderBy('kolom','ASC')
            ->findAll();

        $bookings = $bookingModel
            ->select('booking_bus.*, siswa.nama,siswa.rombel,siswa.jenis,siswa.telp_siswa')
            ->join('siswa','siswa.id = booking_bus.siswa_id')
            ->where('booking_bus.bus_id',$busId)
            ->findAll();

        $bookingMap = [];
        foreach ($bookings as $b) {

            $nama   = $b['nama'];
            $rombel = $b['rombel'] ?? '';
            $telp = $b['telp_siswa'] ?? '';

            // Format: Nama (Rombel)
            $bookingMap[$b['seat_id']] = $nama . ' (' . $rombel . ') '. $telp ;
        }

        foreach ($seats as &$seat) {

            $seat['nama'] = null;
            $seat['is_blocked'] = false;
            $seat['blocked_reason'] = null;

            // 🔒 CEK BLOCKED BERDASARKAN NOMOR KURSI
            if (array_key_exists($seat['nomor_kursi'], $lockedSeats)) {
                $seat['is_blocked'] = true;
                $seat['blocked_reason'] = $lockedSeats[$seat['nomor_kursi']];
            }

            // 🔴 CEK BOOKING (booking tetap bisa tampil)
            if (isset($bookingMap[$seat['id']])) {
                $seat['nama'] = $bookingMap[$seat['id']];
            }
        }

        // ===============================
        // REKAP DATA
        // ===============================
        $rekapRombel = [];
        $rekapGender = [
            'L' => 0,
            'P' => 0
        ];

        foreach ($bookings as $b) {

            // Hitung rombel
            $rombel = $b['rombel'] ?? '-';
            if (!isset($rekapRombel[$rombel])) {
                $rekapRombel[$rombel] = 0;
            }
            $rekapRombel[$rombel]++;

            // Hitung gender
            if ($b['jenis'] == 'L') {
                $rekapGender['L']++;
            } elseif ($b['jenis'] == 'P') {
                $rekapGender['P']++;
            }
        }

        $totalSiswa = array_sum($rekapRombel);

        return view('admin/report/printbus',[
            'bus'   => $bus,
            'seats' => $seats,
            'rekapRombel' => $rekapRombel,
            'rekapGender' => $rekapGender,
            'totalSiswa'  => $totalSiswa
        ]);
    }









}