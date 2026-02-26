<?= view('shared/head') ?>

<style>
.bus-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: .2s;
    background: #fff;
}

.bus-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
}

.bus-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}

.bus-row-normal {
    width: 420px;
    display: flex;
    justify-content: space-between;
}

.bus-row {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    gap: 20px;
}

.seat-side {
    display: flex;
    gap: 8px;
}

.aisle {
    width: 30px;
}

.back-row {
    justify-content: center;
    gap: 6px;
}

.seat {
    width: 48px;
    height: 40px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.seat-empty {
    background: #d1fae5;
    border: 1px solid #10b981;
    color: #065f46;
    cursor: pointer;
}

.seat-empty:hover {
    background: #10b981;
    color: #fff;
}

.seat-booked {
    background: #fecaca;
    border: 1px solid #ef4444;
    color: #7f1d1d;
    cursor: not-allowed;
}

.seat-own {
    background: #bfdbfe;
    border: 1px solid #2563eb;
    color: #1e3a8a;
}

/* 🔒 LOCKED */
.seat-locked {
    background: #e5e7eb;
    border: 1px solid #9ca3af;
    color: #374151;
    cursor: not-allowed;
}

.seat-pair {
    display: flex;
    gap: 4px;
}

.bus-row-back {
    display: flex;
    gap: 4px;
    justify-content: center;
}

.driver-area {
    background: #f3f4f6;
    padding: 8px;
    text-align: center;
    font-weight: bold;
    border-radius: 6px;
    margin-bottom: 15px;
}
</style>

<body>
<div class="wrapper">
<?= view('siswa/navbar'); ?>

<div class="content-wrapper p-4">
<div class="container-fluid">

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="card shadow">
<div class="card-header bg-primary text-white">
    <i class="fas fa-bus"></i> Booking Kursi Bus
</div>

<div class="card-body">

<?php if (!empty($sudahBooking)): ?>
    <div class="alert alert-info">
        Anda sudah memilih:
        <strong>
            <?= esc($sudahBooking['nama_bus']) ?> -
            Kursi <?= esc($sudahBooking['nomor_kursi']) ?>
        </strong>
    </div>
<?php endif; ?>

<div class="row">

<?php if (!empty($busList)): ?>
<?php foreach ($busList as $b): ?>

<div class="col-md-6 mb-4">
<div class="bus-card p-3">

<h5 class="mb-3">
    🚌 <?= esc($b['nama_bus']) ?>
    <span class="badge bg-success float-end">
        <?= $b['terisi_final'] ?>/<?= $b['kapasitas'] ?>
    </span>
</h5>

<div class="driver-area">
    👨‍✈️ SUPIR
</div>

<div class="bus-body">

<?php
$grouped = [];
foreach ($b['seats'] as $seat) {
    $grouped[$seat['baris']][] = $seat;
}
ksort($grouped);
?>

<?php foreach ($grouped as $baris => $seats): ?>

    <?php if ($baris <= 11): ?>
        <div class="bus-row-normal">
            <div class="seat-pair">
                <?php foreach ($seats as $s): if (in_array($s['posisi'], ['L1','L2'])): ?>
                    <?php $isLocked = !empty($s['is_blocked']); ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $s,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => $isLocked
                    ]) ?>
                <?php endif; endforeach; ?>
            </div>
            <div class="seat-pair">
                <?php foreach ($seats as $s): if (in_array($s['posisi'], ['R1','R2'])): ?>
                    <?php $isLocked = !empty($s['is_blocked']); ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $s,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => $isLocked
                    ]) ?>
                <?php endif; endforeach; ?>
            </div>
        </div>

    <?php elseif ($baris == 12): ?>
        <div class="bus-row-normal" style="justify-content: flex-end;">
            <div class="seat-pair">
                <?php foreach ($seats as $seat): if (in_array($seat['nomor_kursi'], [45,46])): ?>
                    <?php $isLocked = !empty($seat['is_blocked']); ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $seat,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => $isLocked
                    ]) ?>
                <?php endif; endforeach; ?>
            </div>
        </div>

    <?php elseif ($baris == 13): ?>
        <div class="bus-row-normal">
            <div class="seat-pair">
                <?php foreach ($seats as $seat): if (in_array($seat['nomor_kursi'], [47,48])): ?>
                    <?php $isLocked = !empty($seat['is_blocked']); ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $seat,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => $isLocked
                    ]) ?>
                <?php endif; endforeach; ?>
            </div>
            <div class="seat-pair">
                <?php foreach ($seats as $seat): if (in_array($seat['nomor_kursi'], [49,50])): ?>
                    <?php $isLocked = !empty($seat['is_blocked']); ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $seat,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => $isLocked
                    ]) ?>
                <?php endif; endforeach; ?>
            </div>
        </div>

    <?php elseif ($baris == 14): ?>
        <div class="bus-row-back">
            <?php foreach ($seats as $seat): ?>
                <?php $isLocked = !empty($seat['is_blocked']); ?>
                <?= view('booking_bus/_seat', [
                    'seat' => $seat,
                    'sudahBooking' => $sudahBooking,
                    'isLocked' => $isLocked
                ]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endforeach; ?>

</div>
</div>
</div>

<?php endforeach; ?>
<?php endif; ?>

</div>
</div>
</div>
</div>
</div>

<script>
function pilihKursi(nomorKursi,busId, seatId) {
    Swal.fire({
        title: 'Konfirmasi Booking',
        text: "Apakah Anda yakin ingin memilih Kursi Nomor " + nomorKursi + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Booking!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= base_url('siswa/booking/simpan') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({
                    bus_id: busId,
                    seat_id: seatId,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            });
        }
    })
}
</script>

</body>
</html>