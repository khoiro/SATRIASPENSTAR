<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BusModel;
use App\Models\BusSeatModel;
use App\Models\BookingBusModel;
use App\Models\SiswaModel;
use Config\Services;

class BookingBus extends BaseController
{
    protected $busModel;
    protected $seatModel;
    protected $bookingModel;
    protected $siswaModel;
    protected $db;

    public function __construct()
    {
        $this->busModel     = new BusModel();
        $this->seatModel    = new BusSeatModel();
        $this->bookingModel = new BookingBusModel();
        $this->siswaModel   = new SiswaModel();
        $this->db           = \Config\Database::connect();
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $userId = Services::login()->id;

        // ===============================
        // AMBIL DATA SISWA DARI USER LOGIN
        // ===============================
        $siswa = $this->db->table('user u')
            ->select('s.id AS siswa_id, s.kelas,s.rombel, s.jenis,s.telp_siswa')
            ->join('siswa s', 's.nisn = u.nisn')
            ->where('u.id', $userId)
            ->get()
            ->getRow();

        if (!$siswa) {
            return redirect()->back()
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $siswaId = $siswa->siswa_id;
        $jenjang = $siswa->kelas;
        $rombel  = $siswa->rombel;

        // ===============================
        // CEK SUDAH BOOKING
        // ===============================
        $sudahBooking = $this->bookingModel
            ->select('booking_bus.*, bus.nama_bus, bus_seat.nomor_kursi')
            ->join('bus_seat', 'bus_seat.id = booking_bus.seat_id')
            ->join('bus', 'bus.id = bus_seat.bus_id')
            ->where('booking_bus.siswa_id', $siswaId)
            ->first();

        // ===============================
        // AMBIL BUS SESUAI JENJANG DAN ROMBEL
        // ===============================
        $busList = $this->busModel
                ->select('bus.*, COUNT(booking_bus.id) as terisi')
                ->join('bus_kelas', 'bus_kelas.bus_id = bus.id')
                ->join('bus_seat', 'bus_seat.bus_id = bus.id', 'left')
                ->join('booking_bus', 'booking_bus.seat_id = bus_seat.id', 'left')
                ->where('bus.jenjang', $jenjang)
                ->where('bus.status', 1)
                ->where('bus_kelas.rombel', $rombel)
                ->groupBy('bus.id')
                ->findAll();
        
        foreach ($busList as &$b) {

            // Ambil semua kursi bus
            $seats = $this->seatModel
                ->where('bus_id', $b['id'])
                ->where('status', '1')
                // ->orderBy('nomor_kursi', 'ASC')
                ->orderBy('baris', 'ASC')
                ->orderBy('kolom', 'ASC')
                ->findAll();
            
            // HARDCODE BLOKIR BERDASARKAN NOMOR KURSI
            // ======================
            // $lockedSeats = ['3', '4', '21', '22'];
            $lockedSeats = [
                                '3'  => 'Untuk Guru Pendamping1',
                                '4'  => 'Untuk Guru Pendamping2',
                                '21' => 'Untuk Guru Pendamping3',
                                '22' => 'Kursi Cadangan',
                            ]; 
            // ini adalah NOMOR KURSI (field nomor_kursi)

            if (in_array($b['id'], [1, 7, 17])) {
                   $lockedSeats = [
                                    '1'  => 'Untuk Kepala Sekolah',
                                    '2'  => 'Untuk Komite',
                                    '3'  => 'Untuk Komite',
                                    '4'  => 'Guru Pendamping1',
                                    '21' => 'Guru Pendamping2',
                                    '22' => 'Guru Pendamping3',
                                    '48' => 'Kursi Cadangan',
                                ];
            }

            $lockedCount = 0;

            foreach ($seats as &$seat) {

                // default
                $seat['is_blocked'] = false;
                $seat['is_booked']  = false;
                $seat['booked_by']  = null;

                // 🔒 LOCKED
                // if (in_array($seat['nomor_kursi'], $lockedSeats)) {
                //     $seat['is_blocked'] = true;
                //     $lockedCount++;
                // }
                if (array_key_exists($seat['nomor_kursi'], $lockedSeats)) {
                    $seat['is_blocked'] = true;
                    $seat['blocked_reason'] = $lockedSeats[$seat['nomor_kursi']];
                    $lockedCount++;
                }

                // 🔴 BOOKED
                $booked = $this->bookingModel
                    ->select('booking_bus.*, siswa.nama, siswa.kelas, siswa.rombel')
                    ->join('siswa', 'siswa.id = booking_bus.siswa_id')
                    ->where('seat_id', $seat['id'])
                    ->first();

                if ($booked) {
                    $seat['is_booked'] = true;
                    $seat['booked_by'] = $booked['nama'];
                    $seat['booked_kelas'] = $booked['kelas'];
                    $seat['booked_rombel'] = $booked['rombel'];
                }
            }

            $b['terisi_final'] = $b['terisi'] + $lockedCount;
            $b['seats'] = $seats;
        }
        unset($b);

        // ===============================
        // CEK NOMOR TELEPON
        // ===============================
        $wajibIsiTelp = false;

        if (empty($siswa->telp_siswa)) {
            $wajibIsiTelp = true;
        }

    

        return view('booking_bus/index', [
            'siswa'        => $siswa,
            'busList'      => $busList,
            'sudahBooking' => $sudahBooking,
            'wajibIsiTelp'   => $wajibIsiTelp
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL BUS (TAMPILKAN KURSI)
    |--------------------------------------------------------------------------
    */
    public function detail($busId)
    {
        $seats = $this->seatModel
            ->select('bus_seat.*, booking_bus.id as booked')
            ->join('booking_bus', 'booking_bus.seat_id = bus_seat.id', 'left')
            ->where('bus_seat.bus_id', $busId)
            ->orderBy('nomor_kursi', 'ASC')
            ->findAll();

        return view('siswa/booking_bus/detail', [
            'seats' => $seats,
            'busId' => $busId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOK KURSI
    |--------------------------------------------------------------------------
    */
    public function book()
    {
        $request = $this->request->getJSON(true);

        $seatId    = $request['seat_id'] ?? null;
        $adminPass = $request['admin_pass'] ?? null;

        $userId = \Config\Services::login()->id;

        $siswa = $this->siswaModel
            ->where('user_id', $userId)
            ->first();

        if (!$siswa) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan.'
            ]);
        }

        $seat = $this->seatModel
            ->select('bus_seat.*, bus.id as bus_id')
            ->join('bus', 'bus.id = bus_seat.bus_id')
            ->where('bus_seat.id', $seatId)
            ->first();

        if (!$seat) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kursi tidak ditemukan.'
            ]);
        }

        // ===============================
        // 🔥 CEK ZONA PEREMPUAN (1–24)
        // ===============================
        $nomorKursi = (int)$seat['nomor_kursi'];

        if ($nomorKursi >= 1 && $nomorKursi <= 24) {

            if ($siswa['jenis'] == 'L') {

                // 🔐 Validasi password admin
                $adminPasswordSystem = 'ADMIN123'; // ganti dengan config/env

                if ($adminPass !== $adminPasswordSystem) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password admin salah. Kursi ini khusus perempuan.'
                    ]);
                }
            }
        }

        // Cek sudah booking
        if ($this->bookingModel->where('siswa_id', $siswa['id'])->first()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda sudah memilih kursi.'
            ]);
        }

        // Cek kursi terisi
        if ($this->bookingModel->where('seat_id', $seatId)->first()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kursi sudah dipilih.'
            ]);
        }

        $this->bookingModel->insert([
            'seat_id'  => $seatId,
            'siswa_id' => $siswa['id'],
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Kursi berhasil dibooking.'
        ]);
    }

    public function simpan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        }

        $userId = Services::login()->id;

        // ===============================
        // AMBIL DATA SISWA + STATUS BAYAR + JENIS
        // ===============================
        $siswa = $this->db->table('user u')
            ->select('s.id AS siswa_id, s.kelas, s.rombel, s.status_bayar, s.jenis')
            ->join('siswa s', 's.nisn = u.nisn')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        if (!$siswa) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan.'
            ]);
        }

        if ($siswa['status_bayar'] != 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda belum lunas. Silakan lakukan pembayaran terlebih dahulu.'
            ]);
        }

        $data      = $this->request->getJSON(true);
        $seatId    = $data['seat_id'] ?? null;
        $busId     = $data['bus_id'] ?? null;
        $adminPass = $data['admin_pass'] ?? null;

        if (!$seatId || !$busId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap.'
            ]);
        }

        // 🔹 Ambil data kursi
        $seat = $this->db->table('bus_seat')
            ->select('nomor_kursi')
            ->where('id', $seatId)
            ->get()
            ->getRowArray();

        if (!$seat) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kursi tidak ditemukan.'
            ]);
        }

        $nomorKursi = (int) $seat['nomor_kursi'];

        $zonaPerempuan = $nomorKursi >= 1 && $nomorKursi <= 24;
        $zonaAdminMix  = $nomorKursi >= 25 && $nomorKursi <= 28; // WAJIB ADMIN
        $zonaLaki      = $nomorKursi >= 29 && $nomorKursi <= 50;

        $adminPasswordSystem = env('ADMIN_BOOKING_PASSWORD');

        // ===============================
        // ZONA KHUSUS PEREMPUAN
        // ===============================
        if ($zonaPerempuan && $siswa['jenis'] === 'L') {
            if (!$adminPass || $adminPass !== $adminPasswordSystem) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Kursi ini khusus siswa perempuan. Password admin diperlukan.'
                ]);
            }
        }

        // ===============================
        // ZONA KHUSUS LAKI
        // ===============================
        if ($zonaLaki && $siswa['jenis'] === 'P') {
            if (!$adminPass || $adminPass !== $adminPasswordSystem) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Kursi ini khusus siswa laki-laki. Password admin diperlukan.'
                ]);
            }
        }

        // ===============================
        // ZONA 25-28 WAJIB ADMIN
        // ===============================
        if ($zonaAdminMix) {
            if (!$adminPass || $adminPass !== $adminPasswordSystem) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Kursi 25-28 hanya bisa dibooking dengan password admin.'
                ]);
            }
        }

        // 🔹 Cek sudah booking kursi
        if ($this->bookingModel->sudahBooking($siswa['siswa_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda sudah memilih kursi.'
            ]);
        }

        // 🔹 Cek kursi sudah terisi
        if ($this->bookingModel->kursiTerisi($seatId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kursi sudah dipilih.'
            ]);
        }

        // 🔹 Simpan booking
        $this->bookingModel->insert([
            'bus_id'     => $busId,
            'seat_id'    => $seatId,
            'siswa_id'   => $siswa['siswa_id'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Kursi berhasil dibooking.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BATAL BOOKING
    |--------------------------------------------------------------------------
    */
    public function batal()
    {
        $userId = Services::login()->id;

        $siswa = $this->siswaModel
            ->where('user_id', $userId)
            ->first();

        if (!$siswa) {
            return redirect()->back();
        }

        $this->bookingModel
            ->where('siswa_id', $siswa['id'])
            ->delete();

        return redirect()->back()->with('success', 'Booking berhasil dibatalkan.');
    }

    public function updateTelp()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error']);
        }

        $userId = Services::login()->id;

        $telp = $this->request->getJSON()->telp ?? '';

        if (empty($telp)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nomor HP tidak boleh kosong'
            ]);
        }

        // ===============================
        // AMBIL NISN DARI USER LOGIN
        // ===============================
        $user = $this->db->table('user')
            ->select('nisn')
            ->where('id', $userId)
            ->get()
            ->getRow();

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        // ===============================
        // UPDATE KE TABEL SISWA
        // ===============================
        $this->db->table('siswa')
            ->where('nisn', $user->nisn)
            ->update([
                'telp_siswa' => $telp
            ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Nomor HP berhasil disimpan'
        ]);
    }
}
