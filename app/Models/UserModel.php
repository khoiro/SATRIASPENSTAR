<?php

namespace App\Models;

use App\Entities\User;
use CodeIgniter\Model;
use Config\Services;

class UserModel extends Model
{
    public static $roles = [
        'siswa',
        'user',
        'admin',
    ];

    protected $table         = 'user';
    protected $allowedFields = [
        'name', 'email', 'password', 'avatar', 'role','status','nisn'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\User';
    
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // 'datetime', 'date', atau 'int' (unix timestamp)

    // Nama kolom created_at dan updated_at (sesuaikan jika berbeda)
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Jika useSoftDeletes true

    /** @return User|null */
    public function atEmail($email)
    {
        $this->builder()->where('email', $email);
        return $this->find()[0] ?? null;
    }

    public function login(User $data)
    {
        $s = Services::session();
        $s->set('login', $data->id);
    }

    /** @return int|null */
    public function register($data, $thenLogin = true)
    {
        $data = array_intersect_key($data, array_flip(
            ['name', 'email', 'password','nisn','status']
        ));
        $data['role'] = 'siswa';
        $data['status'] = '1';
        if (!empty($data['password']))
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        if ($this->save($data)) {
            if ($thenLogin) {
                Services::session()->set('login', $this->insertID);
                Services::session()->set('name', $data['name'] ?? '');
                Services::session()->set('email', $data['email'] ?? '');
                Services::session()->set('nisn', $data['nisn'] ?? '');
            }
            return $this->insertID;
        }
        return null;
    }

    public function processWeb($id)
    {
        if ($id === null) {
            $item = (new User($_POST));
            post_file($item, 'avatar');
            $item->password = password_hash($item->password, PASSWORD_BCRYPT);
            $item->status = 1;
            $id = $this->insert($item);
            return $id;
        } else if ($item = $this->find($id)) {
            /** @var User $item */
            $item->fill($_POST);
            post_file($item, 'avatar');
            if ($item->hasChanged('password')) {
                if (!$item->password) {
                    $item->discardPassword();
                } else {
                    $item->password = password_hash($item->password, PASSWORD_BCRYPT);
                }
            }
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

        
    public function softDeleteByNisn($nisn)
    {
        $siswa = $this->where('nisn', $nisn)->first();

        if ($siswa) {
            $siswa->status = 0;
            return $this->update($siswa->id, $siswa); // update berdasarkan id, karena CI4 membutuhkan primary key
        }

        return false;
    }



}
