<?php
/**
 * Variabel:
 * @var array $seat
 * @var array|null $sudahBooking
 * @var bool  $isLocked
 */

$nomorKursi = esc($seat['nomor_kursi']);
$seatId     = $seat['id'];
$busId      = $seat['bus_id'] ?? null;

$nomorInt = (int)$seat['nomor_kursi'];

// Status booking dari controller
$isBooked   = $seat['is_booked'] ?? false;
$bookedBy   = $seat['booked_by'] ?? null;
$bookedKls  = $seat['booked_kelas'] ?? null;
$bookedRombel = $seat['booked_rombel'] ?? null;

// Default
$class = 'seat-empty';
$disabled = '';
$attrClick = "onclick=\"pilihKursi('$nomorKursi','$busId','$seatId')\"";
$title = "Kursi $nomorKursi";


// ==========================
// 1️⃣ BLOKIR MANUAL
// ==========================
if (!empty($isLocked)) {

    $class = 'seat-locked';

    $reason = $seat['blocked_reason'] ?? 'Kursi diblokir';

    $attrClick = "onclick=\"lihatBlokir('"
        . esc($reason)
        . "','"
        . $nomorKursi
        . "')\"";

    $title = "Klik untuk melihat keterangan";
}

// ==========================
// 2️⃣ SUDAH DIBOOKING
// ==========================
elseif ($isBooked) {

    $class = 'seat-booked';

    if ($bookedBy) {
        $attrClick = "onclick=\"lihatBooking('"
            . esc($bookedBy) . "','"
            . esc($bookedKls) . "','"
            . esc($bookedRombel) . "','"
            . $nomorKursi
            . "')\"";

        $title = "Klik untuk melihat detail booking";
    } else {
        $attrClick = '';
        $title = "Kursi sudah dibooking";
    }
}

// ==========================
// 3️⃣ ZONA ADMIN (25–28)
// ==========================
elseif ($nomorInt >= 25 && $nomorInt <= 28) {

    $class = 'seat-admin';
    $title = "Zona Admin (25-28)";
}

// ==========================
// 4️⃣ ZONA LAKI (29–50)
// ==========================
elseif ($nomorInt >= 29 && $nomorInt <= 50) {

    $class = 'seat-laki';
    $title = "Zona Laki-laki (29-50)";
}

// ==========================
// 4️⃣ MILIK SENDIRI
// ==========================
if (!empty($sudahBooking) && $sudahBooking['seat_id'] == $seatId) {
    $class = 'seat-own';
    $attrClick = "onclick=\"Swal.fire('Info', 'Ini adalah kursi Anda.', 'info')\"";
    $title = "Kursi Anda";
}
?>

<button type="button"
        class="seat <?= $class ?>"
        <?= $disabled ?>
        <?= $attrClick ?>
        title="<?= esc($title) ?>">
    <?= $nomorKursi ?>
</button>