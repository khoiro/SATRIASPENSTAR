<?php

namespace App\Controllers;

use App\Entities\Article;
use App\Entities\User as EntitiesUser;
use App\Entities\Siswa as EntitiesSiswa;
use App\Models\ArticleModel;
use App\Models\SiswaModel;
use App\Models\UserModel;
use App\Models\SettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Admin extends BaseController
{

	/** @var EntitiesUser  */
	public $login;

	public function __construct()
    {
        $this->checkAccess(['admin']);
    }

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		if (!($this->login = Services::login())) {
			$this->logout();
			$this->response->redirect('/login/')->send();
			exit;
		}
	}

	public function index()
	{
		return view('admin/dashboard', [
			'page' => 'dashboard'
		]);
	}

	public function logout()
	{
		$this->session->destroy();
		return $this->response->redirect('/');
	}


	public function article($page = 'list', $id = null)
	{
		$model = new ArticleModel();
		if ($this->login->role !== 'admin') {
			$model->withUser($this->login->id);
		}
		if ($this->request->getMethod() === 'POST') {
			if ($page === 'delete' && $model->delete($id)) {
				$this->session->setFlashdata('success', 'Data article berhasil dihapus!'); 
				return $this->response->redirect('/admin/article/');
			} else if ($id = $model->processWeb($id)) {
				$message = ($page === 'add' ? 'menambahkan' : 'memperbarui');
				$this->session->setFlashdata('success', 'Data article berhasil ' . $message . '!');
				return $this->response->redirect('/admin/article/');
			}
		}
		switch ($page) {
			case 'list':
				return view('admin/article/list', [
					'data' => find_with_filter(empty($_GET['category']) ? $model : $model->withCategory($_GET['category'])),
					'page' => 'article',
				]);
			case 'add':
				return view('admin/article/edit', [
					'item' => new Article(),
					'subtitle' => 'Tambah Article'
				]);
			case 'edit':
				if (!($item = $model->find($id))) {
					throw new PageNotFoundException();
				}
				return view('admin/article/edit', [
					'item' => $item,
					'subtitle' => 'Edit Article'
				]);
		}
		throw new PageNotFoundException();
	}

	public function manage($page = 'list', $id = null)
	{
		if ($this->login->role !== 'admin') {
			throw new PageNotFoundException();
		}
		$model = new UserModel();
		if ($this->request->getMethod() === 'POST') {
			if ($page === 'delete') {
				// return $this->response->redirect('/admin/manage/');
				 if ($model->processSoftDelete($id)) {
                    $this->session->setFlashdata('success', 'User berhasil dihapus!'); // Flashdata untuk delete
                    return $this->response->redirect('/admin/manage/');
                } else {
                    $this->session->setFlashdata('error', 'Gagal menghapus user.'); // Opsional: pesan error
                    return $this->response->redirect('/admin/manage/');
                }
			} else if ($id = $model->processWeb($id)) {
				// return $this->response->redirect('/admin/manage/');
			 	$message = ($page === 'add' ? 'menambahkan' : 'memperbarui');
                $this->session->setFlashdata('success', 'Data user berhasil ' . $message . '!'); // Flashdata untuk add/edit
                return $this->response->redirect('/admin/manage/');
            } else {
                $this->session->setFlashdata('error', 'Gagal menyimpan user. Silakan coba lagi.'); // Opsional: pesan error
                return $this->response->redirect('/admin/manage/edit/' . $id); // Kembali ke form jika gagal
            }
		}
		switch ($page) {
			case 'list':
				return view('admin/users/list',[
					'page' => 'manage',
				]);
			case 'add':
				return view('admin/users/edit', [
					'item' => new EntitiesUser(),
					'subtitle' => 'Tambah User',
				]);
			case 'edit':
				if (!($item = $model->find($id))) {
					throw new PageNotFoundException();
				}
				return view('admin/users/edit', [
					'item' => $item,
					'subtitle' => 'Edit User',
				]);
		}
		throw new PageNotFoundException();
	}

	public function uploads($directory)
	{
		// to upload general files (summernote)
		$path = WRITEPATH . implode(DIRECTORY_SEPARATOR, ['uploads', $directory, '']);
		$r = $this->request;
		if (!is_dir($path))
			mkdir($path, 0775, true);
		if ($r->getMethod() === 'POST') {
			if (($f = $r->getFile('file')) && $f->isValid()) {
				if ($f->move($path)) {
					return $f->getName();
				}
			}
		}
		return null;
	}

	public function profile()
	{
		if ($this->request->getMethod() === 'POST') {
			if ((new UserModel())->processWeb($this->login->id)) {
				return $this->response->redirect('/admin/profile/');
			}
		}
		return view('admin/profile', [
			'item' => $this->login,
			'page' => 'profile',
		]);
	}

	public function siswa($page = 'list', $id = null)
    {
        $model  = new SiswaModel();
        $model2 = new UserModel();

      

        if ($this->login->role !== 'admin') {
            $model->withUser($this->login->id);
        }

        if ($this->request->getMethod() === 'POST') {
            $data   = $model->find($id);
            $nisn   = $model2->where('nisn', $data->nisn)->first();

            if ($page === 'delete') {
                if ($model->processSoftDelete($id) && $model2->softDeleteByNisn($nisn->nisn)) {
                    $this->session->setFlashdata('success', 'Data siswa berhasil dihapus!'); // Flashdata untuk delete
                    return $this->response->redirect('/admin/siswa/');
                } else {
                    $this->session->setFlashdata('error', 'Gagal menghapus data siswa.'); // Opsional: pesan error
                    return $this->response->redirect('/admin/siswa/');
                }
            } else if ($id = $model->processWeb($id)) {
                $message = ($page === 'add' ? 'menambahkan' : 'memperbarui');
                $this->session->setFlashdata('success', 'Data siswa berhasil ' . $message . '!'); // Flashdata untuk add/edit
                return $this->response->redirect('/admin/siswa/');
            } else {
                $this->session->setFlashdata('error', 'Gagal menyimpan data siswa. Silakan coba lagi.'); // Opsional: pesan error
                return $this->response->redirect('/admin/siswa/edit/' . $id); // Kembali ke form jika gagal
            }
        }

        switch ($page) {
            case 'list':
                return view('admin/siswa/list',[
					'page' => 'siswa',
				]);
            case 'add':
                return view('admin/siswa/edit', [
                    'item' => new EntitiesSiswa(),
                    'subtitle' => 'Tambah Siswa'
                ]);
            case 'edit':
                if (!($item = $model->find($id))) {
                    throw new PageNotFoundException();
                }
                return view('admin/siswa/edit', [
                    'item' => $item,
                    'subtitle' => 'Edit Siswa'
                ]);
        }
        throw new PageNotFoundException();
    }


	public function datatablesiswa()
    {
        $request = service('request');
        $model = new SiswaModel();

        // Anda mungkin ingin menambahkan logika filter/pencarian DataTables di sini
        // Berdasarkan parameter DataTables seperti start, length, search, order
        // Untuk contoh ini, kita hanya mengambil semua data status 1
        $data = $model->where('status', 1)->findAll();

        $response = [];
        $no = 1;

        foreach ($data as $item) {
            // Bangun tombol Hapus secara manual agar bisa menambahkan atribut data-id dan class
            $deleteButton = '
                <button type="button" class="btn btn-danger btn-sm btn-delete-siswa" data-id="' . esc($item->id ?? 0) . '">
                    <i class="fa fa-trash"></i> Hapus
                </button>';

            $editButton = '
                <a href="/admin/siswa/edit/' . esc($item->id ?? 0) . '" class="btn btn-warning btn-sm">
                    <i class="fa fa-edit"></i> Edit
                </a>';

            $viewButton = '
                <button type="button" class="btn btn-success btn-sm btn-view-siswa" data-id="' . esc($item->id ?? 0) . '" title="Lihat Detail Siswa">
                    <i class="fa fa-eye"></i>
                </button>';

            $response[] = [
                $no++,
                esc($item->nisn ?? ''),
                esc($item->nama ?? ''),
                esc($item->alamat ?? ''),
                esc(ucfirst($item->rombel ?? '')),
                // Gabungkan tombol edit dan delete
                $editButton . $deleteButton . $viewButton
            ];
        }

        return $this->response->setJSON([
            'data' => $response
        ]);
    }

	public function datatableuser()
    {
        $request = service('request');
        $model = new UserModel();
        $data = $model->where('status', 1)->findAll();
        $response = [];
        $no = 1;

        foreach ($data as $item) {
            $deleteButton = '
                <button type="button" class="btn btn-danger btn-sm btn-delete-user" data-id="' . esc($item->id ?? 0) . '">
                    <i class="fa fa-trash"></i> Hapus
                </button>';
            $editButton = '
                <a href="/admin/manage/edit/' . esc($item->id ?? 0) . '" class="btn btn-warning btn-sm mr-1">
                    <i class="fa fa-edit"></i> Edit
                </a>';

            $response[] = [
                $no++,
                esc($item->name ?? ''),
                esc($item->email ?? ''),
                esc($item->role ?? ''),
                esc(ucfirst($item->nisn ?? '')),
                $editButton . $deleteButton
            ];
        }
        return $this->response->setJSON([
            'data' => $response
        ]);
    }

	public function datatablearticle()
    {
        $request = service('request');
        $articleModel = new ArticleModel();

        // Ambil semua data artikel dengan nama pengguna
        // Ini adalah poin kuncinya: SEMUA data diambil
        $data = $articleModel->getArticlesWithUserNames();

        $response = [];
        $no = 1;

        foreach ($data as $item) {
            $deleteButton = '
                <button type="button" class="btn btn-danger btn-sm btn-delete-article" data-id="' . esc($item->id ?? 0) . '">
                    <i class="fa fa-trash"></i> Hapus
                </button>';
            $editButton = '
                <a href="/admin/article/edit/' . esc($item->id ?? 0) . '" class="btn btn-warning btn-sm mr-1">
                    <i class="fa fa-edit"></i> Edit
                </a>';
            $response[] = [
                $no++,
                esc($item->title ?? ''),
                esc($item->user_name ?? 'N/A'), // Menggunakan alias 'user_name'
                esc($item->category ?? ''),
                // Pastikan ini menangani format datetime dengan benar
                esc(ucfirst($item->updated_at ?? '')),
                $editButton . $deleteButton
            ];
        }

        // Karena ini client-side processing, kita hanya perlu mengembalikan array 'data'
        return $this->response->setJSON([
            'data' => $response
        ]);
    }

    // --- (Opsional) Method untuk mengambil daftar kategori unik secara dinamis ---
    // Ini tetap berguna agar dropdown filter Anda terisi otomatis
    public function getCategories()
    {
        $articleModel = new ArticleModel();
        $categories = $articleModel->distinct()->select('category')->orderBy('category', 'ASC')->findAll();
        return $this->response->setJSON($categories);
    }

	public function getnisn()
	{
		$nisn = $this->request->getVar('nisn'); // Ambil NISN dari request GET

		$model = new SiswaModel();
		$siswa = $model->where('nisn', $nisn)->first(); // Cari siswa berdasarkan NISN

		if ($siswa) {
			return $this->response->setJSON(['name' => $siswa->nama]); // Ganti 'nama_lengkap' dengan nama kolom yang benar di DB Anda
		} else {
			return $this->response->setJSON(['name' => null]); // Atau kembalikan respons 404
		}
	}

    public function findsiswa($id = null)
    {
        $model  = new SiswaModel();
        $user   = new UserModel();

        if (!$id) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'ID tidak disediakan'
            ]);
        }

        $data     = $model->find($id);
        $dataUser = $user->where('nisn', $data->nisn)->first();

        if ($data) {
            return $this->response->setJSON([
                'status' => true,
                'data' => $data,
                'dataUser' => $dataUser  ?? null,
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }


    public function importsiswa()
    {
        $file = $this->request->getFile('file_excel');

        if ($file && $file->isValid()) {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $siswaModel = new SiswaModel();

            foreach ($sheetData as $index => $row) {
                if ($index === 0) continue; // Lewati baris header

                $siswaModel->save([
                    'nisn'      => $row[0],
                    'nis'       => $row[1],
                    'nama'      => $row[2],
                    'tgl_lahir' => $row[3],
                    'alamat'    => $row[4],
                    'telp_siswa'=> $row[5],
                    'telp_ortu' => $row[6],
                    'kelas'     => $row[7],
                    'rombel'    => $row[8],
                    'status'    => 1, // aktif
                ]);
            }

            return redirect()->to('/admin/siswa')->with('success', 'Data berhasil diimpor.');
        }

        return redirect()->back()->with('error', 'File tidak valid.');
    }

    public function cekemail()
    {
        $email = $this->request->getGet('email');

        $userModel = new UserModel();
        $existingUser = $userModel->where('email', $email)->first();

        return $this->response->setJSON(['exists' => $existingUser ? true : false]);
    }

    public function location()
    {
        if (Services::login()->role !== 'admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $model = new SettingModel();
        $lokasi = $model->getSetting('lokasi_absensi');

        // Jika POST (AJAX)
        if ($this->request->getMethod() === 'POST') {

            $validation = $this->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius' => 'required|integer|greater_than[0]',
            ]);

            if (!$validation) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validasi gagal. Pastikan semua data terisi dengan benar.'
                ]);
            }

            $newLokasi = [
                'lat' => $this->request->getPost('latitude'),
                'lng' => $this->request->getPost('longitude'),
                'radius' => $this->request->getPost('radius'),
            ];

            $model->setSetting('lokasi_absensi', json_encode($newLokasi));

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Titik koordinat absensi berhasil diperbarui!'
            ]);
        }

        // Jika GET biasa (tampilkan halaman)
        $dataLokasi = $lokasi ? json_decode($lokasi, true) : [
            'lat' => -7.44710975382454,
            'lng' => 112.52221433900381,
            'radius' => 100,
        ];

        return view('admin/location/edit', [
            'page' => 'location',
            'item' => $dataLokasi
        ]);
    }





}
