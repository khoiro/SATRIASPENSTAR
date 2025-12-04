<?php

namespace App\Controllers;

use App\Models\HolidayModel; // Menggunakan Model CodeIgniter
use CodeIgniter\Controller; // Pastikan menggunakan Controller CI4

class HolidayController extends BaseController // Asumsi BaseController ada
{
    protected $holidayModel;

    public function __construct()
    {
        // Inisialisasi Model di constructor
        $this->holidayModel = new HolidayModel();
        
        // Opsional: Cek role admin di sini
        // if (service('login')->role !== 'admin') { 
        //     return redirect()->to('/')->with('error', 'Akses ditolak.');
        // }
    }

    /**
     * Tampilkan daftar hari libur dan kalender untuk pengaturan.
     * Rute: /admin/holiday (GET)
     */
    public function index()
    {
        // 1. Ambil semua data hari libur dari database menggunakan Model CI4
        $holidays = $this->holidayModel->findAll();
        
        $data = [
            'page' => 'holiday', // Untuk menandai menu aktif di sidebar
            'holidays' => $holidays,
        ];
        
        // 2. Tampilkan View, menggunakan sintaks CI4 untuk view
        return view('admin/holidays/index', $data); // Asumsi path view: app/Views/holidays/index.php
    }

    /**
     * Simpan hari libur baru ke database.
     * Rute: /admin/holiday/store (POST)
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        }

        // Aturan validasi
        $rules = [
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_akhir' => 'required|valid_date|check_date[tanggal_mulai]',
            'keterangan' => 'required|string|max_length[255]',
        ];

        // Pesan error custom
        $messages = [
            'tanggal_mulai' => [
                'required' => 'Tanggal mulai wajib diisi.',
                'valid_date' => 'Format tanggal mulai salah.',
            ],
            'tanggal_akhir' => [
                'required' => 'Tanggal akhir wajib diisi.',
                'valid_date' => 'Format tanggal akhir salah.',
                'check_date' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.'
            ],
            'keterangan' => [
                'required' => 'Keterangan wajib diisi.',
                'max_length' => 'Keterangan terlalu panjang.'
            ]
        ];

        // Validasi gagal → kirim JSON error
        if (!$this->validate($rules, $messages)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ]);
        }

        // Simpan data
        $data = [
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_akhir' => $this->request->getPost('tanggal_akhir'),
            'keterangan' => $this->request->getPost('keterangan'),
        ];

        $this->holidayModel->save($data);

        // Response sukses
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Jadwal libur berhasil disimpan'
        ]);
    }


    
    /**
     * Hapus hari libur dari database.
     * Rute: /admin/holiday/delete/(:num) (POST)
     */
    public function delete($id = null)
    {
        if ($id) {
            $this->holidayModel->delete($id);
            return redirect()->to(site_url('admin/holiday'))->with('success', 'Jadwal libur berhasil dihapus.');
        }
        return redirect()->to(site_url('admin/holiday'))->with('error', 'ID libur tidak valid.');
    }

    public function datatable()
    {
        $request = service('request');
        $post = $request->getPost();

        $columns = ['id', 'tanggal_mulai', 'tanggal_akhir', 'keterangan'];

        // Ambil parameter DataTables
        $draw = $post['draw'];
        $start = $post['start'];
        $length = $post['length'];
        $searchValue = $post['search']['value'];

        // Query dasar
        $builder = $this->holidayModel->builder();

        // Filtering
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('keterangan', $searchValue)
                ->orLike('tanggal_mulai', $searchValue)
                ->orLike('tanggal_akhir', $searchValue)
                ->groupEnd();
        }

        // Total records
        $totalRecords = $this->holidayModel->countAll();

        // Total filtered
        $totalFiltered = $builder->countAllResults(false);

        // Ordering
        if (isset($post['order'])) {
            $orderColumn = $columns[$post['order'][0]['column']];
            $orderDir = $post['order'][0]['dir'];
            $builder->orderBy($orderColumn, $orderDir);
        }

        // Limit
        $builder->limit($length, $start);

        // Eksekusi
        $query = $builder->get();
        $data = [];
        $no = $start + 1;

        foreach ($query->getResultArray() as $row) {
            $data[] = [
                $no++,
                date('d-m-Y', strtotime($row['tanggal_mulai'])),
                date('d-m-Y', strtotime($row['tanggal_akhir'])),
                $row['keterangan'],
                '<a href="' . site_url('admin/holiday/delete/' . $row['id']) . '" 
                    class="btn btn-sm btn-danger btn-delete-holiday"
                    data-id="'.$row['id'].'"
                    data-confirm="Yakin ingin menghapus?">
                    <i class="fas fa-trash"></i> Hapus
                </a>'
            ];
        }

        // Response JSON DataTables
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function getEvents()
    {
        $holidays = $this->holidayModel->findAll();

        $events = array_map(function($h) {
            return [
                'title' => $h['keterangan'],
                'start' => $h['tanggal_mulai'],
                // FullCalendar butuh end+1 hari
                'end' => date('Y-m-d', strtotime($h['tanggal_akhir'] . ' +1 day')),
                'allDay' => true,
                'color' => '#dc3545'
            ];
        }, $holidays);

        return $this->response->setJSON($events);
    }


}