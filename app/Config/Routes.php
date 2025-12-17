<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->add('/user/', 'User::index');
$routes->add('/user/(:any)', 'User::$1');
$routes->post('check-nisn-dob', 'Home::checkNisnDob'); 
$routes->get('home/cekemail', 'Home::cekemail'); 
$routes->add('/(:any)', 'Home::$1');

$routes->group('admin', function($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('logout', 'Admin::logout');
    $routes->get('profile', 'Admin::profile');
    $routes->get('datatablesiswa', 'Admin::datatablesiswa');
    $routes->get('datatableuser', 'Admin::datatableuser');
    $routes->get('datatablearticle', 'Admin::datatablearticle');
    $routes->get('getnisn', 'Admin::getnisn');
    $routes->get('findsiswa/(:num)', 'Admin::findsiswa/$1');
    $routes->get('cekemail', 'Admin::cekemail');
    $routes->post('importsiswa', 'Admin::importsiswa');
    $routes->post('holiday/datatable', 'HolidayController::datatable');
    $routes->get('report', 'ReportAdminController::index');
    $routes->post('report/get_absensi', 'ReportAdminController::get_absensi');
    $routes->post('report/getSiswaByKelas', 'ReportAdminController::getSiswaByKelas');
    $routes->get('reportstatusabsensi', 'ReportAdminController::statusAbsensi');
    $routes->get('reportajaxstatusabsensi', 'ReportAdminController::ajaxRekap');


    // Rute untuk Pengaturan Lokasi Absensi (GET & POST)
    // Ini akan memanggil method 'location' di Admin Controller
    $routes->match(['get', 'post'], 'location', 'Admin::location');

    // 👇 RUTE BARU UNTUK PENGATURAN JADWAL LIBUR
    // Menggunakan nama controller 'HolidayController'
    $routes->group('holiday', function($routes) {
        // Tampilkan halaman index/kalender pengaturan libur (Read)
        $routes->get('/', 'HolidayController::index');
        // Proses penyimpanan libur baru (Create)
        $routes->post('store', 'HolidayController::store');
        // Proses penghapusan libur (Delete)
        $routes->get('delete/(:num)', 'HolidayController::delete/$1');
        $routes->get('events', 'HolidayController::getEvents');
    });
    // 👆 AKHIR RUTE JADWAL LIBUR

    // Rute untuk artikel - semua aksi ditangani oleh satu method
    $routes->match(['get', 'post'], 'article/(:any)/(:num)', 'Admin::article/$1/$2');
    $routes->match(['get', 'post'], 'article/(:any)', 'Admin::article/$1');
    $routes->match(['get', 'post'], 'article', 'Admin::article');

    // Rute untuk artikel - semua aksi ditangani oleh satu method
    $routes->match(['get', 'post'], 'manage/(:any)/(:num)', 'Admin::manage/$1/$2');
    $routes->match(['get', 'post'], 'manage/(:any)', 'Admin::manage/$1');
    $routes->match(['get', 'post'], 'manage', 'Admin::manage');

    // Rute untuk artikel - semua aksi ditangani oleh satu method
    $routes->match(['get', 'post'], 'siswa/(:any)/(:num)', 'Admin::siswa/$1/$2');
    $routes->match(['get', 'post'], 'siswa/(:any)', 'Admin::siswa/$1');
    $routes->match(['get', 'post'], 'siswa', 'Admin::siswa');
    
});

$routes->group('siswa', function($routes) {
    $routes->get('/', 'Siswa::index');
    $routes->get('profile', 'Siswa::profile');
    $routes->get('logout', 'Siswa::logout');
    $routes->post('profile', 'Siswa::profile');
    $routes->post('absensi', 'Siswa::absensi');
    $routes->get('report', 'ReportController::index');
    $routes->post('report/get_absensi', 'ReportController::get_absensi');
    // =========================================
    // RUTE BARU: HITUNG KEHADIRAN UNTUK SISWA
    // =========================================
    // Halaman menampilkan hitung kehadiran
    $routes->get('kehadiran', 'Siswa::kehadiran');
    $routes->get('kehadiran/data-ajax', 'Siswa::getKehadiranDataAjax');
    $routes->post('izin-sakit', 'Siswa::absensiIzinSakit');
});
