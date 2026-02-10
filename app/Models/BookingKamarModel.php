<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\BookingKamar;

class BookingKamarModel extends Model
{
    protected $table      = 'booking_kamar';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'kamar_id',
        'siswa_id',
    ];

    protected $returnType = BookingKamar::class;
}
