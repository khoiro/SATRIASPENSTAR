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

// Status booking dari controller
$isBooked   = $seat['is_booked'] ?? false;
$bookedBy   = $seat['booked_by'] ?? null;
$bookedKls  = $seat['booked_kelas'] ?? null;
$bookedRombel = $seat['booked_rombel'] ?? null;

// Default: kosong
$class = 'seat-empty';
$disabled = '';
$attrClick = "onclick=\"pilihKursi('$nomorKursi','$busId','$seatId')\"";
$title = "Kursi $nomorKursi";


// 1️⃣ BLOKIR MANUAL
if (!empty($isLocked)) {
    $class = 'seat-booked';
    $disabled = 'disabled';
    $attrClick = '';
    $title = "Kursi $nomorKursi (Diblokir)";
}

// 2️⃣ SUDAH DIBOOKING ORANG LAIN
elseif ($isBooked) {
    $class = 'seat-booked';
    $disabled = 'disabled';
    $attrClick = '';

    if ($bookedBy) {
        $title = "Sudah dibooking oleh $bookedBy ($bookedKls $bookedRombel)";
    } else {
        $title = "Kursi sudah dibooking";
    }
}

// 3️⃣ MILIK SENDIRI
if (!empty($sudahBooking) && $sudahBooking['seat_id'] == $seatId) {
    $class = 'seat-own';
    $disabled = '';
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
