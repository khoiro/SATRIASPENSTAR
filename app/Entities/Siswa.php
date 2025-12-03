<?php

namespace App\Entities;

use App\Models\UserModel;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property string $nisn
 * @property string $nis
 * @property string $nama
 * @property string $tgl_lahir
 * @property string $alamat
 * @property string $telp_siswa
 * @property string $telp_ortu
 * @property string $kelas
 * @property string $rombel
 * @property string $status
 * @property User $user
 * @property int $user_id
 * @property Time $created_at
 * @property Time $updated_at
 * @property Time $deleted_at
 */
class Siswa extends Entity
{
    protected $casts = [
        'id' => 'integer',
    ];

    public function getUser()
    {
        return $this->user_id ? (new UserModel())->find($this->user_id) : null;
    }

    public function setUser(User $x)
    {
        $this->user_id = $x->id;
    }

    public function getExcerpt($length = 60)
    {
        return get_excerpt($this->content, $length);
    }
}
