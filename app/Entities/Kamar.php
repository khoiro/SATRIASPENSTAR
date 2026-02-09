<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property int    $id
 * @property string $nomor_kamar
 * @property int    $kapasitas
 * @property string $jenjang
 * @property Time   $created_at
 * @property Time   $updated_at
 * @property Time   $deleted_at
 */
class Kamar extends Entity
{
    protected $casts = [
        'id'        => 'integer',
        'kapasitas' => 'integer',
    ];

    /**
     * Helper: cek apakah kamar penuh
     */
    public function isFull(int $terisi): bool
    {
        return $terisi >= $this->kapasitas;
    }
}
