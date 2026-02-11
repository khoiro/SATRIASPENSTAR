<?php

namespace App\Models;

use App\Entities\Siswa;
use CodeIgniter\Model;
use Config\Services;

class SiswaModel extends Model
{
    public static $kelas = [
        '7',
        '8',
        '9',
    ];

    public static $rombel = [
        'KELAS 7A',
        'KELAS 7B',
        'KELAS 7C',
        'KELAS 7D',
        'KELAS 7E',
        'KELAS 7F',
        'KELAS 7G',
        'KELAS 7H',
        'KELAS 8A',
        'KELAS 8B',
        'KELAS 8C',
        'KELAS 8D',
        'KELAS 8E',
        'KELAS 8F',
        'KELAS 8G',
        'KELAS 8H',
        'KELAS 9A',
        'KELAS 9B',
        'KELAS 9C',
        'KELAS 9D',
        'KELAS 9E',
        'KELAS 9F',
        'KELAS 9G',
        'KELAS 9H',
        
    ];

    protected $table         = 'siswa';
    protected $allowedFields = [
        'nisn', 'nis', 'nama', 'tgl_lahir','alamat','telp_siswa','telp_ortu','kelas','rombel','status','user_id'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Siswa';
    protected $useTimestamps = true;

    public function withKelas($kelas)
    {
        $this->builder()->where('kelas', $kelas);
        return $this;
    }

    public function withSearch($q)
    {
        $this->builder()->like('content', $q);
        $this->builder()->orLike('title', $q);
        return $this;
    }

    public function withUser($id)
    {
        $this->builder()->where('user_id', $id);
        return $this;
    }

    public function processWeb($id)
    {
        if ($id === null) {
            $item = (new Siswa($_POST));
            $item->user_id = Services::login()->id;
            $item->status = 1;
            $item->nama = strtoupper($item->nama);
            $item->alamat = strtoupper($item->alamat);
            return $this->insert($item);
        } else if ($item = $this->find($id)) {
            /** @var Siswa $item */
            $item->fill($_POST);
            $item->nama = strtoupper($item->nama);
            $item->alamat = strtoupper($item->alamat);
            if ($item->hasChanged()) {
                $this->save($item);
            }
            return $id;
        }
        return false;
    }

    public function processSoftDelete($id)
    {
        if ($item = $this->find($id)) {
            /** @var Siswa $item */
            $item->fill($_POST);
            $item->status = 0;
            if ($item->hasChanged()) {
                $this->save($item);
            }
            return $id;
        }
        return false;
    }


    public function checkNisnAndDob(string $nisn, string $tanggalLahir)
    {
        return $this->where('nisn', $nisn)
                    ->where('tgl_lahir', $tanggalLahir) // Pastikan nama kolom di DB adalah 'tanggal_lahir'
                    ->first();
    }

    public function getSiswaBelumBooking($jenjang = null, $kelas = null)
    {
        $builder = $this->db->table('siswa s');
        $builder->select('s.nama AS nama_siswa, s.rombel');
        $builder->where('s.status', 1);

        if ($kelas) {
            $builder->where('s.rombel', $kelas);
        } elseif ($jenjang) {
            $builder->where('s.kelas', $jenjang);
        }

        $builder->whereNotIn('s.id', function ($sub) {
            $sub->select('bk.siswa_id')
                ->from('booking_kamar bk');
        });

        return $builder->get()->getResultArray();
    }


}
