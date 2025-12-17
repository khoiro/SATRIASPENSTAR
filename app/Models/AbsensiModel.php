<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'nisn', 'tanggal','status','keterangan', 'jam_masuk','jam_keluar',
        'lokasi_lat', 'lokasi_lng','lokasi_lat_keluar','lokasi_lng_keluar', 'foto','foto_keluar','foto_izin_sakit', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function cekStatusAbsensi(int $userId, ?string $tanggal = null): array
    {
        $tanggal ??= date('Y-m-d');

        $absensi = $this->where([
                'user_id' => $userId,
                'tanggal' => $tanggal
            ])->first();

        // Default status
        $status = 'BELUM';

        if ($absensi) {
            if (in_array($absensi['status'], ['IZIN', 'SAKIT'])) {
                $status = $absensi['status'];
            } elseif ($absensi['jam_masuk'] && !$absensi['jam_keluar']) {
                $status = 'MASUK';
            } elseif ($absensi['jam_masuk'] && $absensi['jam_keluar']) {
                $status = 'SELESAI';
            }
        }

        return [
            'status' => $status,                      // BELUM | MASUK | SELESAI | IZIN | SAKIT
            'masuk'  => $status === 'MASUK' || $status === 'SELESAI',
            'keluar' => $status === 'SELESAI',
            'izin'   => $status === 'IZIN',
            'sakit'  => $status === 'SAKIT',
            'data'   => $absensi
        ];
    }


    public function getKehadiran($userId, $start, $end)
    {
        return $this->where('user_id', $userId)
                    ->where('tanggal >=', $start)
                    ->where('tanggal <=', $end)
                    ->countAllResults();
    }

    public function getKeterlambatan($userId, $start, $end)
    {
        return $this->where('user_id', $userId)
                    ->where('tanggal >=', $start)
                    ->where('tanggal <=', $end)
                    ->where('jam_masuk >', '06:45:00')
                    ->countAllResults();
    }

    public function getRekapAbsensiBulanan($bulan, $tahun)
    {
        $db = \Config\Database::connect();

        // 1️⃣ Total hari dalam bulan
        $totalHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        // 2️⃣ Hitung total hari libur (dari tabel liburs)
        $libur = $db->query("
            SELECT COUNT(*) AS total
            FROM (
                SELECT tanggal_mulai + INTERVAL seq DAY AS tanggal
                FROM liburs
                JOIN (
                    SELECT 0 seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                    UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
                    UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
                    UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                    UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
                    UNION ALL SELECT 30
                ) seq
                WHERE tanggal_mulai + INTERVAL seq DAY <= tanggal_akhir
            ) t
            WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
        ", [$bulan, $tahun])->getRow()->total;

        $hariAktif = max(0, $totalHari - $libur);

        // 3️⃣ Hitung tepat waktu & terlambat
        $absen = $db->query("
            SELECT
                SUM(CASE WHEN jam_masuk <= '06:45:00' THEN 1 ELSE 0 END) AS tepat_waktu,
                SUM(CASE WHEN jam_masuk >  '06:45:00' THEN 1 ELSE 0 END) AS terlambat
            FROM absensi
            WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
        ", [$bulan, $tahun])->getRow();

        // 4️⃣ Total siswa unik
        $totalSiswa = $db->query("
            SELECT COUNT(DISTINCT nisn) AS total
            FROM absensi
        ")->getRow()->total;

        // 5️⃣ Hitung alpha
        $totalKehadiran = $totalSiswa * $hariAktif;
        $hadir = ($absen->tepat_waktu + $absen->terlambat);
        $alpha = max(0, $totalKehadiran - $hadir);

        return [
            'tepat_waktu' => (int) $absen->tepat_waktu,
            'terlambat'   => (int) $absen->terlambat,
            'alpha'       => (int) $alpha,
            'libur'       => (int) $libur
        ];
    }

    public function getRekapAbsensiBulananAjax($bulan, $tahun)
    {
        $start = "$tahun-$bulan-01";
        $end   = date("Y-m-t", strtotime($start));

        // TOTAL HARI DALAM BULAN
        $totalHari = (int) date('t', strtotime($start));

        // HITUNG LIBUR
        $libur = $this->db->table('liburs')
            ->where('tanggal_mulai <=', $end)
            ->where('tanggal_akhir >=', $start)
            ->get()
            ->getResult();

        $jumlahLibur = 0;
        foreach ($libur as $l) {
            $mulai = max($l->tanggal_mulai, $start);
            $akhir = min($l->tanggal_akhir, $end);
            $jumlahLibur += (strtotime($akhir) - strtotime($mulai)) / 86400 + 1;
        }

        // HADIR
        $hadir = $this->where('tanggal >=', $start)
                    ->where('tanggal <=', $end)
                    ->countAllResults();

        // TERLAMBAT
        $terlambat = $this->where('tanggal >=', $start)
                        ->where('tanggal <=', $end)
                        ->where('jam_masuk >', '06:45:00')
                        ->countAllResults();

        // TEPAT WAKTU
        $tepatWaktu = $hadir - $terlambat;

        // ALPHA
        $alpha = ($totalHari - $jumlahLibur) - $hadir;
        if ($alpha < 0) $alpha = 0;

        return [
            'tepat_waktu' => $tepatWaktu,
            'terlambat'   => $terlambat,
            'alpha'       => $alpha,
            'libur'       => $jumlahLibur
        ];
    }

    public function absensiIzinSakit()
    {
        $userId = Services::login()->id;
        $now    = Time::now();
        $tanggal = $now->toDateString('Y-m-d');

        $status = $this->request->getPost('status'); // izin / sakit
        $keterangan = $this->request->getPost('keterangan');
        $file = $this->request->getFile('foto');

        if (!in_array($status, ['izin', 'sakit'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['message' => 'Status tidak valid']);
        }

        if (!$keterangan) {
            return $this->response->setStatusCode(400)
                ->setJSON(['message' => 'Keterangan wajib diisi']);
        }

        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)
                ->setJSON(['message' => 'Foto bukti wajib diupload']);
        }

        $absensiModel = new AbsensiModel();
        $userModel    = new UserModel();

        // 🔒 Cegah double absensi
        $cek = $absensiModel
            ->where('user_id', $userId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($cek) {
            return $this->response->setStatusCode(400)
                ->setJSON(['message' => 'Anda sudah melakukan absensi hari ini']);
        }

        // 📸 Simpan foto
        $path = WRITEPATH . 'uploads/izin_sakit/';
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        $namaFile = $file->getRandomName();
        $file->move($path, $namaFile);

        $user = $userModel->find($userId);

        // 💾 Insert absensi izin/sakit
        $absensiModel->insert([
            'user_id' => $userId,
            'nisn'    => $user->nisn,
            'tanggal' => $tanggal,
            'status'  => $status,
            'keterangan' => $keterangan,
            'foto_izin_sakit' => 'uploads/izin_sakit/' . $namaFile,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => ucfirst($status) . ' berhasil dikirim'
        ]);
    }





}
