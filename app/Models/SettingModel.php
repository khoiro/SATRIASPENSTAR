<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table      = 'settings';
    protected $primaryKey = 'key';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['key', 'value'];

    protected $useTimestamps = false;

    /**
     * Mengambil nilai setting berdasarkan key.
     * @param string $key
     * @return string|null
     */
    public function getSetting(string $key): ?string
    {
        $data = $this->select('value')
                     ->where('key', $key)
                     ->first();
        
        return $data['value'] ?? null;
    }

    /**
     * Menyimpan atau memperbarui nilai setting.
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setSetting(string $key, string $value): bool
    {
        $data = [
            'key' => $key,
            'value' => $value
        ];
        
        // Gunakan upsert (insert atau update)
        return $this->replace($data);
    }
}