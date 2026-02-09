<?php

namespace App\Controllers;

use App\Models\KamarModel;
use App\Models\SiswaModel;
use App\Models\BookingKamarModel;
use CodeIgniter\Controller;
use Config\Services;

class BookingKamar extends BaseController
{
    protected $kamarModel;
    protected $bookingModel;

    public function __construct()
    {
        $this->kamarModel   = new KamarModel();
        $this->bookingModel = new BookingKamarModel();
        $this->siswaModel 	= new SiswaModel();
    }

    /**
     * Halaman utama booking kamar (untuk siswa)
     */
    public function index()
    {
        // ambil siswa login
        $siswaId = Services::login()->id;
        $siswa   = $this->siswaModel->find($siswaId);
        $jenjang = $siswa->kelas;

        // ===============================
        // CEK APAKAH SISWA SUDAH BOOKING
        // ===============================
        $sudahBooking = $this->bookingModel
            ->select('booking_kamar.*, kamar.nomor_kamar')
            ->join('kamar', 'kamar.id = booking_kamar.kamar_id')
            ->where('booking_kamar.siswa_id', $siswaId)
            ->where('kamar.status', '1')
            ->first();

        if ($sudahBooking) {
            $sudahBooking['penghuni'] = $this->bookingModel
                ->select('siswa.nama, siswa.rombel')
                ->join('siswa', 'siswa.id = booking_kamar.siswa_id')
                ->where('booking_kamar.kamar_id', $sudahBooking['kamar_id'])
                ->findAll();
        }


        // ===============================
        // AMBIL DATA KAMAR SESUAI JENJANG
        // ===============================
        $kamar = $this->kamarModel
            ->where('jenjang', $jenjang)
            ->where('status', '1')
            ->findAll();

        // ===============================
        // AMBIL DATA PENGHUNI PER KAMAR
        // ===============================
        foreach ($kamar as &$k) {

            // ambil penghuni kamar
            $penghuni = $this->bookingModel
                ->select('siswa.nama, siswa.rombel')
                ->join('siswa', 'siswa.id = booking_kamar.siswa_id')
                ->where('booking_kamar.kamar_id', $k['id'])
                ->findAll();

            $k['penghuni'] = $penghuni;
            $k['terisi']   = count($penghuni);
        }
        unset($k); // good practice

        return view('booking_kamar/index', [
            'kamar'        => $kamar,
            'sudahBooking' => $sudahBooking
        ]);
    }


    /**
     * Proses booking kamar
     */
    public function book($kamarId)
    {
        $siswaId = Services::login()->id;

        // Cek siswa sudah booking
        if ($this->bookingModel->where('siswa_id', $siswaId)->countAllResults() > 0) {
            return redirect()->back()->with('error', 'Anda sudah memilih kamar.');
        }

        // Cek kapasitas kamar
        $jumlah = $this->bookingModel
            ->where('kamar_id', $kamarId)
            ->countAllResults();

        if ($jumlah >= 3) {
            return redirect()->back()->with('error', 'Kamar sudah penuh.');
        }

        // Simpan booking
        $this->bookingModel->insert([
            'kamar_id' => $kamarId,
            'siswa_id' => $siswaId
        ]);

        return redirect()->to('/siswa/bookingkamar')->with('success', 'Kamar berhasil dibooking.');
    }
}
