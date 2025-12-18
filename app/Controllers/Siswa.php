<?php

namespace App\Controllers;

use App\Entities\Article;
use App\Entities\User as EntitiesUser;
use App\Models\ArticleModel;
use App\Models\UserModel;
use App\Models\AbsensiModel;
use App\Models\SettingModel;
use App\Models\LiburModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;
use CodeIgniter\I18n\Time;

class Siswa extends BaseController
{

	/** @var EntitiesUser  */
	public $login;

	public function __construct()
    {
        $this->checkAccess(['siswa']);
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
		$now     = Time::now();
		$tanggal = $now->toDateString();
		$userId  = Services::login()->id;

		$absensiModel = new AbsensiModel();
		$settingModel = new SettingModel();

		// 🔍 Cek status absensi hari ini
		$result = $absensiModel->cekStatusAbsensi($userId, $tanggal);

		// ✅ AMBIL DATA ABSENSI (INI KUNCINYA)
		$dataAbsensi = $result['data'] ?? [];

		// 📍 Default lokasi
		$defaultLokasi = [
			'lat'    => -7.44710975382454,
			'lon'    => 112.52221433900381,
			'radius' => 100,
		];

		$lokasiJson    = $settingModel->getSetting('lokasi_absensi');
		$lokasiAbsensi = $lokasiJson
			? array_merge($defaultLokasi, json_decode($lokasiJson, true))
			: $defaultLokasi;

		// Samakan key longitude
		if (isset($lokasiAbsensi['lng'])) {
			$lokasiAbsensi['lon'] = $lokasiAbsensi['lng'];
			unset($lokasiAbsensi['lng']);
		}

		return view('siswa/dashboard', [
			'page'             => 'dashboard',
			'tanggal_hari_ini' => $tanggal,

			// ✅ JAM AMAN UNTUK HADIR / MASUK / SELESAI
			'jam_masuk'        => $dataAbsensi['jam_masuk'] ?? null,
			'jam_keluar'       => $dataAbsensi['jam_keluar'] ?? null,

			// ✅ STATUS PASTI ADA
			'status_absensi'   => $result['status'] ?? 'BELUM',

			'lokasi_absensi'   => $lokasiAbsensi,
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
				return $this->response->redirect('/user/article/');
			} else if ($id = $model->processWeb($id)) {
				return $this->response->redirect('/user/article/');
			}
		}
		switch ($page) {
			case 'list':
				return view('user/article/list', [
					'data' => find_with_filter(empty($_GET['category']) ? $model : $model->withCategory($_GET['category'])),
					'page' => 'article',
				]);
			case 'add':
				return view('user/article/edit', [
					'item' => new Article()
				]);
			case 'edit':
				if (!($item = $model->find($id))) {
					throw new PageNotFoundException();
				}
				return view('user/article/edit', [
					'item' => $item
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
			if ($page === 'delete' && $model->delete($id)) {
				return $this->response->redirect('/user/manage/');
			} else if ($id = $model->processWeb($id)) {
				return $this->response->redirect('/user/manage/');
			}
		}
		switch ($page) {
			case 'list':
				return view('user/users/list', [
					'data' => find_with_filter($model),
					'page' => 'users',
				]);
			case 'add':
				return view('user/users/edit', [
					'item' => new EntitiesUser()
				]);
			case 'edit':
				if (!($item = $model->find($id))) {
					throw new PageNotFoundException();
				}
				return view('user/users/edit', [
					'item' => $item
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
				$this->session->setFlashdata('success', 'Data Username berhasil diupdate!'); // Flashdata untuk delete
				return $this->response->redirect('/siswa/profile/');
			}
		}
		return view('siswa/profile', [
			'item' => $this->login,
			'page' => 'profile',
		]);
	}

	public function absensi()
    {
		$now 		= Time::now();
        $model 		= new AbsensiModel();
		$model2 	= new UserModel();

        $userId 	= Services::login()->id;
		$dataNisn   = $model2->where('id', $userId)->first();
		$tanggal	= $now->toDateString('Y-m-d');
		$jam 		= $now->format('H:i:s');
        $latitude 	= $this->request->getPost('latitude');
        $longitude 	= $this->request->getPost('longitude');
        $status 	= $this->request->getPost('status');
        $imageData = $this->request->getPost('image');

        // Validasi image
        if (strpos($imageData, 'data:image') !== false) {
            $imageParts = explode(";base64,", $imageData);
            $imageBase64 = base64_decode($imageParts[1]);
            $imageName = uniqid('absensi_') . '.png';
            $imagePath = WRITEPATH . 'uploads/absensi/' . $imageName;
            file_put_contents($imagePath, $imageBase64);
        } else {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Format gambar tidak valid']);
        }

        // Cek apakah user sudah absen hari ini
		$absenHariIni = $model->where('user_id', $userId)
							->where('tanggal', $tanggal)
							->first();

		if (!$absenHariIni) {
			// Jika belum absen hari ini → INSERT (jam_masuk)
			$data = [
				'user_id'     => $userId,
				'nisn'        => $dataNisn->nisn,
				'tanggal'     => $tanggal,
				'status'      => $status,
				'jam_masuk'   => $jam,
				'lokasi_lat'  => $latitude,
				'lokasi_lng'  => $longitude,
				'foto'        =>  'uploads/absensi/' . $imageName,
			];

			$model->insert($data);

			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Presensi masuk berhasil disimpan',
				'jam_masuk' => $jam,
				'jam_keluar' => null,
			]);
		} else {
			// Jika sudah ada jam_masuk → UPDATE jam_keluar
			$updateData = [
				'jam_keluar'  => $jam,
				'foto_keluar' => 'uploads/absensi/' . $imageName, // jika ingin simpan foto saat keluar juga
				'lokasi_lat_keluar' => $latitude,
				'lokasi_lng_keluar' => $longitude,
			];


			$model->update($absenHariIni['id'], $updateData);

			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Presensi keluar berhasil disimpan',
				'jam_masuk' => $absenHariIni['jam_masuk'],
				'jam_keluar' => $jam,
			]);
		}
    }

	// public function kehadiran()
	// {
	// 	$userId = Services::login()->id;

	// 	$start  = $this->request->getGet('start');
	// 	$end    = $this->request->getGet('end');

	// 	// default: bulan ini
	// 	if (!$start || !$end) {
	// 		$start = date('Y-m-01');
	// 		$end   = date('Y-m-t');
	// 	}

	// 	$absensiModel = new AbsensiModel();
	// 	$liburModel   = new LiburModel();

	// 	// --- Hitung Total Hari Kerja ---
	// 	$periode = new \DatePeriod(
	// 		new \DateTime($start),
	// 		new \DateInterval('P1D'),
	// 		(new \DateTime($end))->modify('+1 day')
	// 	);

	// 	$hari_kerja = [];
	// 	foreach ($periode as $tanggal) {
	// 		$hari = $tanggal->format('Y-m-d');

	// 		// Skip hanya hari Minggu (7)
	// 		if ($tanggal->format('N') == 7) continue;

	// 		// Skip Hari Libur
	// 		$libur = $liburModel
	// 			->where('tanggal_mulai <=', $hari)
	// 			->where('tanggal_akhir >=', $hari)
	// 			->first();
	// 		if ($libur) continue;

	// 		$hari_kerja[] = $hari;
	// 	}

	// 	$totalHariKerja = count($hari_kerja);
	// 	$totalHadir = $absensiModel->getKehadiran($userId, $start, $end);
	// 	$totalTerlambat = $absensiModel->getKeterlambatan($userId, $start, $end);


	// 	$persentase = $totalHariKerja > 0 ? round(($totalHadir / $totalHariKerja) * 100, 2) : 0;

	// 	return view('siswa/report_kehadiran', [
	// 		'page' => 'kehadiran',
	// 		'start' => $start,
	// 		'end' => $end,
	// 		'total_hari_kerja' => $totalHariKerja,
	// 		'total_hadir' => $totalHadir,
	// 		'persentase' => $persentase,
	// 		'userId' => $userId,
	// 		'total_terlambat' => $totalTerlambat,

	// 	]);
	// }

	// Tambahkan function ini di Controller Siswa Anda

	// public function getKehadiranDataAjax()
	// {
	// 	$userId = Services::login()->id;

	// 	$start = $this->request->getGet('start');
	// 	$end = $this->request->getGet('end');

	// 	// Default: bulan ini (digunakan jika dipanggil tanpa parameter)
	// 	if (!$start || !$end) {
	// 		$start = date('Y-m-01');
	// 		$end = date('Y-m-t');
	// 	}

	// 	$absensiModel = new AbsensiModel();
	// 	$liburModel = new LiburModel();

	// 	// --- Hitung Total Hari Kerja ---
	// 	$periode = new \DatePeriod(
	// 		new \DateTime($start),
	// 		new \DateInterval('P1D'),
	// 		(new \DateTime($end))->modify('+1 day')
	// 	);

	// 	$hari_kerja = [];
	// 	foreach ($periode as $tanggal) {
	// 		$hari = $tanggal->format('Y-m-d');

	// 		// Skip hanya hari Minggu (7)
	// 		if ($tanggal->format('N') == 7) continue;

	// 		// Skip Hari Libur
	// 		$libur = $liburModel
	// 			->where('tanggal_mulai <=', $hari)
	// 			->where('tanggal_akhir >=', $hari)
	// 			->first();
	// 		if ($libur) continue;

	// 		$hari_kerja[] = $hari;
	// 	}


	// 	$totalHariKerja = count($hari_kerja);
		
	// 	// Asumsi getKehadiran($userId, $start, $end) sudah ada di AbsensiModel dan mengembalikan jumlah hari hadir
	// 	$totalHadir = $absensiModel->getKehadiran($userId, $start, $end);

	// 	$totalTidakHadir = $totalHariKerja - $totalHadir;
	// 	$persentase = $totalHariKerja > 0 ? round(($totalHadir / $totalHariKerja) * 100, 2) : 0;
	// 	$totalTerlambat = $absensiModel->getKeterlambatan($userId, $start, $end);


	// 	// Mengembalikan data sebagai JSON
	// 	return $this->response->setJSON([
	// 		'total_hari_kerja' => $totalHariKerja,
	// 		'total_hadir' => $totalHadir,
	// 		'total_tidak_hadir' => $totalTidakHadir,
	// 		'persentase' => $persentase,
	// 		'total_terlambat' => $totalTerlambat,
	// 		'start' => $start,
	// 		'end' => $end,
	// 	]);
	// }

	public function kehadiran()
	{
		$userId = Services::login()->id;

		$start = $this->request->getGet('start');
		$end   = $this->request->getGet('end');

		// Default: bulan ini
		if (!$start || !$end) {
			$start = date('Y-m-01');
			$end   = date('Y-m-t');
		}

		$absensiModel = new AbsensiModel();
		$liburModel   = new LiburModel();

		// ===============================
		// HITUNG HARI KERJA
		// ===============================
		$periode = new \DatePeriod(
			new \DateTime($start),
			new \DateInterval('P1D'),
			(new \DateTime($end))->modify('+1 day')
		);

		$totalHariKerja = 0;
		foreach ($periode as $tgl) {
			if ($tgl->format('N') == 7) continue; // Minggu

			$libur = $liburModel
				->where('tanggal_mulai <=', $tgl->format('Y-m-d'))
				->where('tanggal_akhir >=', $tgl->format('Y-m-d'))
				->first();

			if (!$libur) {
				$totalHariKerja++;
			}
		}

		// ===============================
		// REKAP STATUS ABSENSI
		// ===============================
		$rekap = $absensiModel->getRekapStatus($userId, $start, $end);

		$hadir = (int) ($rekap['hadir'] ?? 0);
		$izin  = (int) ($rekap['izin'] ?? 0);
		$sakit = (int) ($rekap['sakit'] ?? 0);

		// ===============================
		// ALPHA
		// ===============================
		$alpha = max(0, $totalHariKerja - ($hadir + $izin + $sakit));

		// ===============================
		// TERLAMBAT
		// ===============================
		$terlambat = $absensiModel->getTerlambat($userId, $start, $end);

		// ===============================
		// PERSENTASE HADIR
		// ===============================
		$persentase = $totalHariKerja > 0
			? round(($hadir / $totalHariKerja) * 100, 2)
			: 0;

		return view('siswa/report_kehadiran', [
			'page'               => 'kehadiran',
			'start'              => $start,
			'end'                => $end,
			'total_hari_kerja'   => $totalHariKerja,
			'hadir'              => $hadir,
			'izin'               => $izin,
			'sakit'              => $sakit,
			'alpha'              => $alpha,
			'persentase'         => $persentase,
			'total_terlambat'    => $terlambat,
			'userId'             => $userId
		]);
	}


	public function getKehadiranDataAjax()
	{
		$userId = Services::login()->id;

		$start = $this->request->getGet('start');
		$end   = $this->request->getGet('end');

		if (!$start || !$end) {
			$start = date('Y-m-01');
			$end   = date('Y-m-t');
		}

		$absensiModel = new AbsensiModel();
		$liburModel   = new LiburModel();

		// ===============================
		// HITUNG HARI KERJA
		// ===============================
		$periode = new \DatePeriod(
			new \DateTime($start),
			new \DateInterval('P1D'),
			(new \DateTime($end))->modify('+1 day')
		);

		$hariKerja = 0;
		foreach ($periode as $tgl) {
			if ($tgl->format('N') == 7) continue; // Minggu

			$libur = $liburModel
				->where('tanggal_mulai <=', $tgl->format('Y-m-d'))
				->where('tanggal_akhir >=', $tgl->format('Y-m-d'))
				->first();

			if (!$libur) {
				$hariKerja++;
			}
		}

		// ===============================
		// HITUNG STATUS ABSENSI
		// ===============================
		$rekap = $absensiModel->getRekapStatus($userId, $start, $end);

		$hadir = (int) ($rekap['hadir'] ?? 0);
		$izin  = (int) ($rekap['izin'] ?? 0);
		$sakit = (int) ($rekap['sakit'] ?? 0);

		// ===============================
		// ALPHA = hari kerja - (hadir + izin + sakit)
		// ===============================
		$alpha = max(0, $hariKerja - ($hadir + $izin + $sakit));

		// ===============================
		// TERLAMBAT
		// ===============================
		$terlambat = $absensiModel->getTerlambat($userId, $start, $end);

		return $this->response->setJSON([
			'total_hari_kerja' => $hariKerja,
			'hadir'            => $hadir,
			'izin'             => $izin,
			'sakit'            => $sakit,
			'alpha'            => $alpha,
			'terlambat'        => $terlambat,
			'start'            => $start,
			'end'              => $end
		]);
	}


	public function absensiIzinSakit()
	{
		$request = $this->request;
		$userId  = Services::login()->id;
		$today  = date('Y-m-d');

		$status      = strtolower($request->getPost('status')); // izin / sakit
		$keterangan  = trim($request->getPost('keterangan'));
		$foto        = $request->getFile('foto');

		// 1️⃣ Validasi status
		if (!in_array($status, ['izin', 'sakit'])) {
			return $this->response->setStatusCode(400)->setJSON([
				'message' => 'Status tidak valid'
			]);
		}

		// 2️⃣ Validasi keterangan
		if (!$keterangan) {
			return $this->response->setStatusCode(400)->setJSON([
				'message' => 'Keterangan wajib diisi'
			]);
		}

		// 3️⃣ Validasi foto
		if (!$foto || !$foto->isValid()) {
			return $this->response->setStatusCode(400)->setJSON([
				'message' => 'Foto bukti wajib diupload'
			]);
		}

		if (!in_array($foto->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
			return $this->response->setStatusCode(400)->setJSON([
				'message' => 'Format foto harus JPG atau PNG'
			]);
		}

		// 4️⃣ Cek apakah sudah absen hari ini
		$absensiModel = new AbsensiModel();
		$existing = $absensiModel
			->where('user_id', $userId)
			->where('tanggal', $today)
			->first();

		if ($existing) {
			return $this->response->setStatusCode(409)->setJSON([
				'message' => 'Anda sudah melakukan absensi hari ini'
			]);
		}

		// 5️⃣ Upload foto
		$uploadPath = WRITEPATH . 'uploads/absensi/';
		if (!is_dir($uploadPath)) {
			mkdir($uploadPath, 0775, true);
		}

		$fileName = $foto->getRandomName();
		$foto->move($uploadPath, $fileName);

		// 6️⃣ Ambil NISN
		$userModel = new \App\Models\UserModel();
		$user = $userModel->find($userId);

		// 7️⃣ Simpan ke database
		$absensiModel->insert([
			'user_id'    => $userId,
			'nisn'       => $user->nisn ?? null,
			'tanggal'    => $today,
			'status'     => $status,
			'keterangan' => $keterangan,
			'foto_izin_sakit'       => 'uploads/absensi/' . $fileName,
			'jam_masuk'  => null,
			'jam_keluar' => null,
		]);

		// 8️⃣ Response sukses
		return $this->response->setJSON([
			'status'  => 'success',
			'message' => ucfirst($status) . ' berhasil dikirim'
		]);
	}



}