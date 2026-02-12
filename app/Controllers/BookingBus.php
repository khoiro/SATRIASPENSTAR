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
            ->select('s.id AS siswa_id, s.kelas,s.rombel')
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
                ->orderBy('nomor_kursi', 'ASC')
                ->findAll();

            foreach ($seats as &$seat) {

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
                } else {
                    $seat['is_booked'] = false;
                    $seat['booked_by'] = null;
                }
            }

            $b['seats'] = $seats;
        }
        unset($b);

    

        return view('booking_bus/index', [
            'siswa'        => $siswa,
            'busList'      => $busList,
            'sudahBooking' => $sudahBooking
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
    public function book($seatId)
    {
        $userId = \Config\Services::login()->id;

        $siswa = $this->siswaModel
            ->where('user_id', $userId)
            ->first();

        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        // Ambil data kursi + bus
        $seat = $this->seatModel
            ->select('bus_seat.*, bus.id as bus_id')
            ->join('bus', 'bus.id = bus_seat.bus_id')
            ->where('bus_seat.id', $seatId)
            ->first();

        if (!$seat) {
            return redirect()->back()->with('error', 'Kursi tidak ditemukan.');
        }

        // ===============================
        // CEK APAKAH BUS SESUAI ROMBEL
        // ===============================
        $allowed = $this->db->table('bus_kelas')
            ->where('bus_id', $seat['bus_id'])
            ->where('rombel', $siswa['rombel'])
            ->countAllResults();

        if (!$allowed) {
            return redirect()->back()->with('error', 'Anda tidak diizinkan memilih bus ini.');
        }

        // Cek sudah booking
        if ($this->bookingModel->where('siswa_id', $siswa['id'])->first()) {
            return redirect()->back()->with('error', 'Anda sudah memilih kursi.');
        }

        // Cek kursi sudah terisi
        if ($this->bookingModel->where('seat_id', $seatId)->first()) {
            return redirect()->back()->with('error', 'Kursi sudah dipilih.');
        }

        $this->bookingModel->insert([
            'seat_id'  => $seatId,
            'siswa_id' => $siswa['id'],
        ]);

        return redirect()->to('/siswa/bookingbus')
            ->with('success', 'Kursi berhasil dibooking.');
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
        // AMBIL DATA SISWA DARI USER LOGIN
        // ===============================
        $siswa = $this->db->table('user u')
            ->select('s.id AS siswa_id, s.kelas,s.rombel')
            ->join('siswa s', 's.nisn = u.nisn')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        if (!$siswa) {
            return redirect()->back()
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        if (!$siswa) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan.'
            ]);
        }

        $data = $this->request->getJSON(true);
        $seatId = $data['seat_id'] ?? null;
        $busId  = $data['bus_id'] ?? null;

        if (!$seatId || !$busId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap.'
            ]);
        }

        // Cek sudah booking
        if ($this->bookingModel->sudahBooking($siswa['siswa_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda sudah memilih kursi.'
            ]);
        }

        // Cek kursi sudah terisi
        if ($this->bookingModel->kursiTerisi($seatId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kursi sudah dipilih.'
            ]);
        }

        // Simpan booking
        $this->bookingModel->insert([
            'bus_id'  => $busId,
            'seat_id'  => $seatId,
            'siswa_id' => $siswa['siswa_id'],
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
}
