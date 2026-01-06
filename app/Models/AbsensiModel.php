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

    public function getRekapAbsensiBulanan($bulan, $tahun, $kelas = null)
    {
        $db = \Config\Database::connect();

        $awalBulan  = "$tahun-$bulan-01";
        $akhirBulan = date('Y-m-t', strtotime($awalBulan));

        // =============================
        // 1. TOTAL HARI DALAM BULAN
        // =============================
        $totalHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        // =============================
        // 2. HITUNG LIBUR DARI TABEL liburs
        // =============================
        $liburDb = $db->query("
            SELECT
                COALESCE(
                    SUM(
                        DATEDIFF(
                            LEAST(tanggal_akhir, ?),
                            GREATEST(tanggal_mulai, ?)
                        ) + 1
                    ), 0
                ) AS total_libur
            FROM liburs
            WHERE tanggal_mulai <= ?
            AND tanggal_akhir >= ?
        ", [$akhirBulan, $awalBulan, $akhirBulan, $awalBulan])->getRow();

        $liburTanggalMerah = (int) $liburDb->total_libur;

        // =============================
        // 3. HITUNG JUMLAH HARI MINGGU
        // =============================
        $jumlahMinggu = 0;
        $tanggal = new \DateTime($awalBulan);

        while ($tanggal->format('Y-m-d') <= $akhirBulan) {
            if ($tanggal->format('w') == 0) { // 0 = Minggu
                $jumlahMinggu++;
            }
            $tanggal->modify('+1 day');
        }

        // =============================
        // 4. MINGGU YANG JATUH DI TANGGAL MERAH (ANTI DOBEL)
        // =============================
        $mingguLibur = $db->query("
            SELECT COUNT(*) AS total
            FROM (
                SELECT DATE_ADD(?, INTERVAL seq DAY) AS tanggal
                FROM (
                    SELECT @row := @row + 1 AS seq
                    FROM information_schema.columns, (SELECT @row := -1) r
                    LIMIT ?
                ) t
            ) kalender
            JOIN liburs l
                ON kalender.tanggal BETWEEN l.tanggal_mulai AND l.tanggal_akhir
            WHERE DAYOFWEEK(kalender.tanggal) = 1
        ", [$awalBulan, $totalHari])->getRow();

        $mingguTanggalMerah = (int) $mingguLibur->total;

        // =============================
        // 5. TOTAL LIBUR EFEKTIF
        // =============================
        $totalLibur = $liburTanggalMerah + $jumlahMinggu - $mingguTanggalMerah;

        $hariKerja = max(0, $totalHari - $totalLibur);

        // =============================
        // 6. FILTER KELAS
        // =============================
        $whereKelas = '';
        if ($kelas) {
            $whereKelas = "AND siswa.rombel = " . $db->escape($kelas);
        }

        // =============================
        // 7. REKAP ABSENSI
        // =============================
        $rekap = $db->query("
            SELECT
                SUM(absensi.status = 'HADIR') AS hadir,
                SUM(absensi.status = 'IZIN')  AS izin,
                SUM(absensi.status = 'SAKIT') AS sakit,
                SUM(
                    absensi.status = 'HADIR'
                    AND absensi.jam_masuk > '06:45:00'
                ) AS terlambat
            FROM absensi
            JOIN siswa ON siswa.nisn = absensi.nisn
            WHERE absensi.tanggal BETWEEN ? AND ?
            $whereKelas
        ", [$awalBulan, $akhirBulan])->getRow();

        $hadir = (int) $rekap->hadir;

        // =============================
        // 8. TEPAT WAKTU & ALPHA
        // =============================
        $tepatWaktu = max(0, $hadir - (int) $rekap->terlambat);
        $alpha = max(0,$hariKerja - ($hadir + (int)$rekap->izin + (int)$rekap->sakit));


        // =============================
        // 9. RETURN
        // =============================
        return [
            'hari_kerja'  => $hariKerja,
            'libur'       => $totalLibur,
            'hadir'       => $hadir,
            'izin'        => (int) $rekap->izin,
            'sakit'       => (int) $rekap->sakit,
            'terlambat'   => (int) $rekap->terlambat,
            'tepat_waktu' => $tepatWaktu,
            'alpha'       => $alpha
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

    public function getRekapStatus($userId, $start, $end)
    {
        return $this->select("
                SUM(status = 'HADIR') AS hadir,
                SUM(status = 'IZIN')  AS izin,
                SUM(status = 'SAKIT') AS sakit
            ")
            ->where('user_id', $userId)
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->get()
            ->getRowArray();
    }

    public function getTerlambat($userId, $start, $end)
    {
        return $this->where('user_id', $userId)
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->where('status', 'HADIR')
            ->where('jam_masuk >', '06:45:00')
            ->countAllResults();
    }







}
