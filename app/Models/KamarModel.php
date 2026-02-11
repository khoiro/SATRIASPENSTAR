<?php

namespace App\Models;

use App\Entities\Kamar;
use CodeIgniter\Model;

class KamarModel extends Model
{
    protected $table = 'kamar';
    protected $allowedFields = ['kegiatan_id','jenjang','nomor_kamar','kapasitas','status'];
    protected $returnType = \App\Entities\Kamar::class;

    public function getKamarWithStatus($jenjang = null)
    {
        $builder = $this->db->table('kamar k')
            ->select('k.id, k.nomor_kamar, k.kapasitas, COUNT(b.id) AS terisi')
            ->join('booking_kamar b', 'b.kamar_id = k.id', 'left')
            ->groupBy('k.id');

        // Jika jenjang diisi → filter
        if (!empty($jenjang)) {
            $builder->where('k.jenjang', $jenjang);
        }

        return $builder->get()->getResultArray();
    }


    public function processSoftDelete($id)
    {
        if ($item = $this->find($id)) {
            /** @var Kamar $item */
            $item->fill($_POST);
            $item->status = 0;
            if ($item->hasChanged()) {
                $this->save($item);
            }
            return $id;
        }
        return false;
    }

    public function processWeb($id = null)
    {
        // INSERT
        if ($id === null) {
            $item = new Kamar($_POST);
            $id = $this->insert($item);
            return $id;
        }

        // UPDATE
        if ($item = $this->find($id)) {
            /** @var Kamar $item */
            $item->fill($_POST);

            if ($item->hasChanged()) {
                $this->save($item);
            }

            return $id;
        }

        return false;
    }

}

