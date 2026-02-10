<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class BookingKamar extends Entity
{
    protected $attributes = [
        'id'        => null,
        'kamar_id'  => null,
        'siswa_id'  => null,
        'created_at'=> null
    ];

    protected $dates = [
        'created_at'
    ];

    protected $casts = [
        'id'       => 'integer',
        'kamar_id' => 'integer',
        'siswa_id' => 'integer',
    ];
}
