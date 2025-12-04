<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'nisn', 'tanggal', 'jam_masuk', 'jam_keluar',
        'lokasi_lat', 'lokasi_lng', 'lokasi_lat_keluar', 'lokasi_lng_keluar',
        'foto', 'foto_keluar', 'created_at', 'updated_at'
    ];
    
    // Server-side Datatables properties
    protected $columnOrder = [
        null, 'tanggal', 'jam_masuk', 'jam_keluar', 'lokasi_lat', null
    ];
    protected $columnSearch = [
        'tanggal', 'jam_masuk'
    ];
    protected $order = [
        'tanggal' => 'DESC'
    ];

    // Method helper untuk Datatables
    private function getQuery($userId, $startDate, $endDate)
    {
        $builder = $this->db->table($this->table);
        $builder->where('user_id', $userId);
        
        // Filter Periode Tanggal
        if ($startDate && $endDate) {
            $builder->where('tanggal >=', $startDate);
            $builder->where('tanggal <=', $endDate);
        }

        // Search dan Orderby Datatables
        $i = 0;
        $request = service('request');

        foreach ($this->columnSearch as $item) {
            if ($request->getPost('search')['value']) {
                if ($i === 0) {
                    $builder->groupStart();
                    $builder->like($item, $request->getPost('search')['value']);
                } else {
                    $builder->orLike($item, $request->getPost('search')['value']);
                }
                if (count($this->columnSearch) - 1 == $i) {
                    $builder->groupEnd();
                }
            }
            $i++;
        }

        if ($request->getPost('order')) {
            $builder->orderBy($this->columnOrder[$request->getPost('order')['0']['column']], $request->getPost('order')['0']['dir']);
        } else if ($this->order) {
            $builder->orderBy(key($this->order), $this->order[key($this->order)]);
        }

        return $builder;
    }

    /**
     * Ambil data untuk Datatables
     */
    public function getDatatables(array $input, int $userId): array
    {
        $startDate = $input['startDate'] ?? null;
        $endDate = $input['endDate'] ?? null;

        $query = $this->getQuery($userId, $startDate, $endDate);
        
        // Hitung total filtered records (setelah filter tanggal dan search)
        $recordsFiltered = $query->countAllResults(false);

        // Limit dan Offset
        if ($input['length'] != -1) {
            $query->limit($input['length'], $input['start']);
        }
        
        $data = $query->get()->getResult();

        // Hitung total records (setelah filter tanggal, tanpa search)
        $queryTotal = $this->getQuery($userId, $startDate, $endDate);
        $queryTotal->select('id');
        $recordsTotal = $queryTotal->countAllResults();

        return [
            'data' => $data,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }
}