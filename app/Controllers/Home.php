<?php

namespace App\Controllers;

use App\Libraries\Recaptha;
use App\Models\ArticleModel;
use App\Models\UserModel;
use App\Models\SiswaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;

class Home extends BaseController
{
	public function index()
	{
		return view('home/index', [
			'news' => find_with_filter((new ArticleModel())->withCategory('news'), 2),
			'info' => find_with_filter((new ArticleModel())->withCategory('info'), 2),
			'page' => 'home',
		]);
	}

	public function login()
	{
		// Jika sudah login, redirect ke dashboard sesuai role
		if (session('isLoggedIn')) {
			return $this->redirectByRole();
		}

		// Handle redirect parameter
		if ($r = $this->request->getGet('r')) {
			return redirect()->to('/login')->setCookie('r', $r, 0);
		}

		// Handle POST request
		if ($this->request->getMethod() === 'POST') {
			$post = $this->request->getPost();
			
			if (!isset($post['email'], $post['password'])) {
				return $this->handleFailedLogin(lang('Login Salah !!! Isi Email atau Username dan Password Anda'));
			}

			$userModel = new UserModel();
			$user = $userModel->atEmail($post['email']);
			
			if (!$user || !password_verify($post['password'], $user->password)) {
				return $this->handleFailedLogin(lang('Login Salah !!! Cek Email dan Password Anda'));
			}

			// Tambahkan pengecekan status
			if ($user->status != 1) {
				return $this->handleFailedLogin(lang('Akun Anda tidak aktif. Silakan hubungi administrator.'));
			}

			// Handle successful login
			$userModel->login($user);
			
			// Set session data
			$sessionData = [
				'id'         => $user->id,
				'email'      => $user->email,
				'isLoggedIn' => true,
				'role'       => $user->role,
				'nama'       => $user->nama // tambahkan data lain yang diperlukan
			];
			session()->set($sessionData);

			// Handle redirect after login
			$redirectUrl = $this->request->getCookie('r') ?? $this->getDefaultRedirectByRole($user->role);
			$this->response->deleteCookie('r');
			
			return redirect()->to(base_url($redirectUrl));
		}

		// Tampilkan view login
		return view('home/login');
	}

	/**
	 * Redirect berdasarkan role user
	 */
	protected function redirectByRole()
	{
		$role = session('role');
		return redirect()->to($this->getDefaultRedirectByRole($role));
	}

	/**
	 * Get default redirect URL berdasarkan role
	 */
	protected function getDefaultRedirectByRole($role)
	{
		switch ($role) {
			case 'admin':
				return 'admin';
			case 'guru':
				return 'guru';
			case 'siswa':
				return 'siswa';
			default:
				return 'user';
		}
	}

	/**
	 * Handle failed login attempt
	 */
	private function handleFailedLogin($message)
	{
		session()->setFlashdata('error', $message);
		return redirect()->to('/login')->withInput();
	}

	public function register()
	{
		$recaptha = new Recaptha();
		if ($this->request->getMethod() === 'GET') {
			return view('home/register', [
				'errors' => $this->session->errors,
				'recapthaSite' => $recaptha->recapthaSite,
			]);
		} else {
			if ($this->validate([
				// 'name' => 'required|min_length[3]|max_length[255]',
				// 'email' => 'required|valid_email|is_unique[user.email]',
				'email' => 'required',
				'password' => 'required|min_length[8]',
				'g-recaptcha-response' => ENVIRONMENT === 'production' && $recaptha->recapthaSecret ? 'required' : 'permit_empty',
			])) {
				if (ENVIRONMENT !== 'production' || !$recaptha->recapthaSecret || (new Recaptha())->verify($_POST['g-recaptcha-response'])) {
					$id = (new UserModel())->register($this->request->getPost());
					(new UserModel())->find($id)->sendVerifyEmail();
					if ($r = $this->request->getCookie('r')) {
						$this->response->deleteCookie('r');
					}
					return $this->response->redirect(base_url($r ?: 'siswa'));
				}
			}
			return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
		}
	}

	public function article($id = null)
	{
		if ($id === 'about') $id = 1;
		else if ($id === 'faq') $id = 2;
		else if ($id === 'contact') $id = 3;

		$model = new ArticleModel();
		if ($id === null) {
			return view('home/article/list', [
				'data' => $model->findAll(),
			]);
		} else if ($item = $model->find($id)) {
			return view('home/article/view', [
				'item' => $item,
				'page' => $item->category,
			]);
		} else {
			throw new PageNotFoundException();
		}
	}

	public function category($name = null)
	{
		$model = new ArticleModel();
		return view('home/article/list', [
			'data' => $model->withCategory($name)->findAll(),
			'page' => $name,
		]);
	}

	public function search()
	{
		$model = new ArticleModel();
		if ($q = $this->request->getGet('q')) {
			$model->withSearch($q);
		}
		return view('home/article/list', [
			'data' => find_with_filter($model),
			'page' => '',
			'search' => $q,
		]);
	}

	public function uploads($directory, $file)
	{
		$path = WRITEPATH . implode(DIRECTORY_SEPARATOR, ['uploads', $directory, $file]);
		if ($file && is_file($path)) {
			check_etag($path);
			header('Content-Type: ' . mime_content_type($path));
			header('Content-Length: ' . filesize($path));
			readfile($path);
			exit;
		}
		throw new FileNotFoundException();
	}

	public function checkNisnDob()
	{
		$siswaModel = new SiswaModel();
		$userModel = new UserModel(); // pastikan Anda punya model ini untuk tabel users

		if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
			$nisn = $this->request->getPost('nisn');
			$tgllahir = $this->request->getPost('tgllahir');

			if (empty($nisn) || empty($tgllahir)) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'NISN dan Tanggal Lahir harus diisi.',
					'csrfHash' => csrf_hash()
				]);
			}

			// Cek apakah NISN & Tanggal Lahir cocok di tabel siswa
			$siswa = $siswaModel->checkNisnAndDob($nisn, $tgllahir);

			if ($siswa) {
				// Cek apakah NISN sudah terdaftar di tabel user
				$user = $userModel->where('nisn', $nisn)->first();

				if ($user && $user->status == '1') {
					// NISN sudah terdaftar sebagai user
					return $this->response->setJSON([
						'success' => false,
						'message' => 'NISN ini sudah digunakan untuk registrasi sebelumnya.',
						'csrfHash' => csrf_hash()
					]);
				}

				// NISN cocok dan belum terdaftar
				return $this->response->setJSON([
					'success' => true,
					'message' => 'Data ditemukan.',
					'data' => $siswa,
					'csrfHash' => csrf_hash()
				]);
			} else {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'NISN atau Tanggal Lahir tidak cocok dengan data kami.',
					'csrfHash' => csrf_hash()
				]);
			}
		}

		// Bukan metode POST atau bukan AJAX
		return $this->response->setJSON([
			'success' => false,
			'message' => 'Metode tidak valid.',
			'csrfHash' => csrf_hash()
		]);
	}

	public function cekemail()
    {
        $email = $this->request->getGet('email');

        $userModel = new UserModel();
        $existingUser = $userModel->where('email', $email)->first();

        return $this->response->setJSON(['exists' => $existingUser ? true : false]);
    }


}
