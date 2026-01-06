<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportAdminModel extends Model
{
    protected $table = 'absensi';
    protected $tableSiswa = 'siswa';

    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'nisn', 'tanggal', 'jam_masuk', 'jam_keluar',
        'lokasi_lat', 'lokasi_lng', 'lokasi_lat_keluar', 'lokasi_lng_keluar',
        'foto', 'foto_keluar', 'created_at', 'updated_at'
    ];
    
    // Server-side Datatables properties
   protected $columnOrder = [
        null,
        'absensi.tanggal',
        'absensi.jam_masuk',
        'absensi.jam_keluar',
        'absensi.lokasi_lat',
        null
    ];

    protected $columnSearch = [
        'tanggal', 'jam_masuk'
    ];
    protected $order = [
        'tanggal' => 'DESC'
    ];

    // Method helper untuk Datatables
    private function getQuery($startDate, $endDate, $kelas = null,$nisn = null)
    {
        $builder = $this->db->table($this->table);

        $builder->select([
            'absensi.*',
            'absensi.status AS status_absensi', // 🔥 INI KUNCINYA
            'siswa.nama',
            'siswa.rombel'
        ]);

        $builder->join(
            'siswa',
            'siswa.nisn = absensi.nisn',
            'left'
        );

        // Filter tanggal
        if ($startDate && $endDate) {
            $builder->where($this->table . '.tanggal >=', $startDate);
            $builder->where($this->table . '.tanggal <=', $endDate);
        }

        // 🔥 Filter kelas (rombel)
        if (!empty($kelas)) {
            $builder->where($this->tableSiswa . '.rombel', $kelas);
        }

        // 🔥 Filter nama siswa
        if (!empty($nisn)) {
            $builder->where($this->tableSiswa . '.nisn', $nisn);
        }

        // 🔍 Search Datatables
        $request = service('request');
        $i = 0;

        foreach ($this->columnSearch as $item) {
            if (!empty($request->getPost('search')['value'])) {
                if ($i === 0) {
                    $builder->groupStart();
                    $builder->like($this->table . '.' . $item, $request->getPost('search')['value']);
                } else {
                    $builder->orLike($this->table . '.' . $item, $request->getPost('search')['value']);
                }
                if (count($this->columnSearch) - 1 == $i) {
                    $builder->groupEnd();
                }
            }
            $i++;
        }

        // 🔃 Order
        if ($request->getPost('order')) {
            $builder->orderBy(
                $this->columnOrder[$request->getPost('order')[0]['column']],
                $request->getPost('order')[0]['dir']
            );
        } else {
            $builder->orderBy(key($this->order), $this->order[key($this->order)]);
        }

        return $builder;
    }

    private function getTotalQuery()
    {
        $builder = $this->db->table($this->table);

        // JOIN siswa (tetap join)
        $builder->join(
            $this->tableSiswa,
            $this->tableSiswa . '.nisn = ' . $this->table . '.nisn',
            'left'
        );

        return $builder;
    }



    /**
     * Ambil data untuk Datatables
     */
    public function getDatatables(array $input, $kelas = null, $nisn = null): array
    {
        $startDate = $input['startDate'] ?? null;
        $endDate   = $input['endDate'] ?? null;

        // =============================
        // QUERY DATA
        // =============================
        $builderData = $this->getQuery($startDate, $endDate, $kelas, $nisn);

        if ($input['length'] != -1) {
            $builderData->limit($input['length'], $input['start']);
        }

        $data = $builderData->get()->getResult();

        /* ================= DEBUG QUERY ================= */
        // echo "<pre>";
        // echo "QUERY DATA:\n";
        // echo $this->db->getLastQuery();
        // echo "\n\n";
        // die();
        /* =============================================== */

        // =============================
        // QUERY FILTERED COUNT
        // =============================
        $builderFiltered = $this->getQuery($startDate, $endDate, $kelas, $nisn);
        $recordsFiltered = $builderFiltered->countAllResults();

        // =============================
        // QUERY TOTAL COUNT (TANPA FILTER)
        // =============================
        $builderTotal = $this->getTotalQuery();
        $recordsTotal = $builderTotal->countAllResults();

        return [
            'data'            => $data,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }





}