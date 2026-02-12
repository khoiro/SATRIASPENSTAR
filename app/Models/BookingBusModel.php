<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingBusModel extends Model
{
    protected $table            = 'booking_bus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'bus_id',
        'seat_id',
        'siswa_id',
        'created_at'
    ];

    protected $useTimestamps = false;

    // ===============================
    // Cek siswa sudah booking
    // ===============================
    public function sudahBooking($siswaId)
    {
        return $this->where('siswa_id', $siswaId)->first();
    }

    // ===============================
    // Cek kursi sudah terisi
    // ===============================
    public function kursiTerisi($seatId)
    {
        return $this->where('seat_id', $seatId)->first();
    }

    // ===============================
    // Ambil penghuni per bus
    // ===============================
    public function getPenghuniByBus($busId)
    {
        return $this->select('siswa.nama, siswa.rombel, bus_seat.nomor_kursi')
                    ->join('bus_seat', 'bus_seat.id = booking_bus.seat_id')
                    ->join('siswa', 'siswa.id = booking_bus.siswa_id')
                    ->where('bus_seat.bus_id', $busId)
                    ->orderBy('bus_seat.nomor_kursi', 'ASC')
                    ->findAll();
    }
}
