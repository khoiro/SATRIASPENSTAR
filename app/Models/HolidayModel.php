<?php 

namespace App\Models;

use CodeIgniter\Model;

class HolidayModel extends Model
{
    protected $table = 'liburs'; // Nama tabel yang sudah kita buat
    protected $primaryKey = 'id';
    
    protected $allowedFields = ['tanggal_mulai', 'tanggal_akhir', 'keterangan'];

    // Jika Anda ingin menggunakan fitur timestamps otomatis CI4:
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // Sesuaikan dengan tipe kolom di DB (DATETIME/TIMESTAMP)
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

?>