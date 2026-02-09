<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingKamarModel extends Model
{
    protected $table = 'booking_kamar';
    protected $allowedFields = ['kamar_id','siswa_id'];
}
