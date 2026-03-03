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
        $userId = Services::login()->id;

        // ===============================
        // AMBIL DATA SISWA DARI USER LOGIN
        // ===============================
        $siswa = $this->db->table('user u')
            ->select('s.id AS siswa_id, s.kelas,s.telp_siswa')
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
            $sudahBooking->penghuni = $this->bookingModel
                ->select('siswa.nama, siswa.rombel,siswa.jenis')
                ->join('siswa', 'siswa.id = booking_kamar.siswa_id')
                ->where('booking_kamar.kamar_id', $sudahBooking->kamar_id)
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

            $penghuni = $this->bookingModel
                ->select('siswa.nama, siswa.rombel,siswa.jenis')
                ->join('siswa', 'siswa.id = booking_kamar.siswa_id')
                ->where('booking_kamar.kamar_id', $k->id)
                ->findAll();

            $k->penghuni = $penghuni;
            $k->terisi   = count($penghuni);
        }
        unset($k);
        
        $wajibIsiTelp = false; // default
        
        if (empty($siswa->telp_siswa)) {
            $wajibIsiTelp = true;
        }

        return view('booking_kamar/index', [
            'kamar'        => $kamar,
            'sudahBooking' => $sudahBooking,
            'wajibIsiTelp'   => $wajibIsiTelp
        ]);
    }



    /**
     * Proses booking kamar
     */
    public function book($kamarId)
    {
        $userId = Services::login()->id;

        // 1️⃣ Ambil NISN dari tabel user
        $user = $this->db->table('user')
            ->select('nisn')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        if (!$user || empty($user['nisn'])) {
            return redirect()->back()
                ->with('error', 'NISN user tidak ditemukan.');
        }

        // 2️⃣ Ambil siswa berdasarkan NISN (TAMBAH FIELD JENIS)
        $siswa = $this->db->table('siswa')
            ->select('id, status_bayar, jenis')
            ->where('nisn', $user['nisn'])
            ->get()
            ->getRowArray();

        if (!$siswa) {
            return redirect()->back()
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        // 🔥 3️⃣ Cek status bayar
        if ($siswa['status_bayar'] != 1) {
            return redirect()->back()
                ->with('error', 'Anda belum lunas. Silakan lakukan pembayaran terlebih dahulu.');
        }

        $siswaId    = $siswa['id'];
        $jenisSiswa = $siswa['jenis']; // L atau P

        // 4️⃣ Cek sudah booking
        if ($this->bookingModel
                ->where('siswa_id', $siswaId)
                ->countAllResults() > 0) {

            return redirect()->back()
                ->with('error', 'Anda sudah memilih kamar.');
        }

        // 🔥 5️⃣ Ambil data kamar (TERMASUK KAPASITAS)
        $kamar = $this->db->table('kamar')
            ->select('kapasitas')
            ->where('id', $kamarId)
            ->get()
            ->getRowArray();

        if (!$kamar) {
            return redirect()->back()
                ->with('error', 'Data kamar tidak ditemukan.');
        }

        // 🔥 6️⃣ Hitung jumlah penghuni kamar
        $jumlahTerisi = $this->bookingModel
            ->where('kamar_id', $kamarId)
            ->countAllResults();

        if ($jumlahTerisi >= $kamar['kapasitas']) {
            return redirect()->back()
                ->with('error', 'Kamar sudah penuh sesuai kapasitas.');
        }

        // =====================================================
        // 🔥 7️⃣ CEK JENIS KELAMIN PENGHUNI KAMAR
        // =====================================================
        $penghuni = $this->bookingModel
            ->select('siswa.jenis')
            ->join('siswa', 'siswa.id = booking_kamar.siswa_id')
            ->where('booking_kamar.kamar_id', $kamarId)
            ->findAll();

        if (!empty($penghuni)) {

            // Ambil jenis penghuni pertama
            $jenisKamar = $penghuni[0]->jenis;

            if ($jenisKamar !== $jenisSiswa) {
                return redirect()->back()
                    ->with('error', 'Kamar ini sudah diisi oleh siswa dengan jenis kelamin berbeda.');
            }
        }

        // 8️⃣ Simpan booking
        $this->bookingModel->insert([
            'kamar_id' => $kamarId,
            'siswa_id' => $siswaId
        ]);

        return redirect()->to('/siswa/bookingkamar')
            ->with('success', 'Kamar berhasil dibooking.');
    }



}
