<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Validasi: tanggal_akhir harus >= tanggal_mulai
     */
    public function check_date(string $str, string $fields, array $data)
    {
        $tanggal_mulai = $data[$fields] ?? null;

        if (!$tanggal_mulai) {
            return false;
        }

        return strtotime($str) >= strtotime($tanggal_mulai);
    }
}
