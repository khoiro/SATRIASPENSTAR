<?= view('shared/head') ?>
<style>
.seat-box {
    width: 65px;
    height: 55px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    line-height: 1.1;
    overflow: hidden;
}

.seat-empty {
    background: #22c55e;
    color: white;
}

.seat-booked {
    background: #ef4444;
    color: white;
}

.seat-blocked {
    background: #6b7280;
    color: white;
}

.bus-row-admin {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 10px;
}

.legend-box {
    width: 20px;
    height: 20px;
    display: inline-block;
    border-radius: 4px;
    margin-right: 6px;
}
</style>

<body>
<div class="wrapper">
<?= view('admin/navbar') ?>

<div class="content-wrapper p-4">
<div class="container-fluid">

<div class="card shadow border-0">
<div class="card-header bg-primary text-white d-flex align-items-center">
    <i class="fas fa-bus me-2"></i>
    <h5 class="mb-0">Report Booking Seat</h5>
</div>

<div class="card-body">

<!-- ================= FILTER ================= -->
<form method="get" class="mb-4 bg-light p-3 rounded border">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="small fw-bold">Jenjang</label>
            <select class="form-select form-select-sm" name="jenjang" id="jenjang">
                <option value="">-- Semua Jenjang --</option>
                <?php foreach ($jenjangList as $j): ?>
                    <option value="<?= $j ?>" <?= ($j == $jenjang) ? 'selected' : '' ?>>
                        <?= $j ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="small fw-bold">Kelas</label>
            <select class="form-select form-select-sm" name="kelas" id="kelas">
                <option value="">-- Semua Kelas --</option>
            </select>
        </div>

       <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fas fa-search me-1"></i> Filter
            </button>
        </div>

        <div class="col-md-3">
            <label class="small fw-bold">Cetak Bus</label>
            <select class="form-select form-select-sm" id="bus_print">
                <option value="">-- Pilih Bus --</option>
                <?php foreach ($allBus as $b): ?>
                    <option value="<?= $b['id'] ?>">
                        <?= esc($b['nama_bus']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-1">
            <button type="button" class="btn btn-success btn-sm w-100" onclick="cetakBus()">
                <i class="fas fa-print me-1"></i> Cetak
            </button>
        </div>
    </div>
</form>

<!-- ================= LEGEND ================= -->
<div class="mb-3">
    <span class="legend-box seat-empty"></span> Tersedia
    <span class="legend-box seat-booked ms-3"></span> Booking
    <span class="legend-box seat-blocked ms-3"></span> Diblokir
</div>

<div class="row">

<!-- ================= BUS ================= -->
<div class="col-md-8">

<?php if (!empty($busList)): ?>
<?php foreach ($busList as $bus): ?>

<div class="card mb-3">
<div class="card-header bg-dark text-white py-2 small fw-bold">
    🚌 BUS: <?= esc($bus['nama_bus']) ?>
</div>

<div class="card-body p-3 shadow-sm">

<?php
$grouped = [];
foreach ($bus['seats'] as $s) {
    $grouped[$s['baris']][] = $s;
}
ksort($grouped);
?>

<?php foreach ($grouped as $baris => $seats): ?>
<div class="bus-row-admin">
<?php foreach ($seats as $seat): ?>

<?php
$class =
    $seat['is_blocked'] ? 'seat-blocked' :
    ($seat['is_booked'] ? 'seat-booked' : 'seat-empty');

$title =
    $seat['is_blocked'] ? 'Diblokir' :
    ($seat['is_booked'] ? esc($seat['booked_by']) : 'Tersedia');
?>

<div class="seat-box <?= $class ?>" title="<?= $title ?>">

    <div class="fw-bold border-bottom border-white border-opacity-25 mb-1">
        <?= $seat['nomor_kursi'] ?>
    </div>

    <?php if ($seat['is_blocked']): ?>
        <small>BOOKED</small>

    <?php elseif ($seat['is_booked']): ?>
        <small class="text-truncate px-1">
            <?= esc($seat['booked_by']) ?>
        </small>

    <?php else: ?>
        <small>-</small>
    <?php endif; ?>

</div>

<?php endforeach; ?>
</div>
<?php endforeach; ?>

</div>
</div>

<?php endforeach; ?>
<?php else: ?>
<div class="alert alert-warning py-2 small">
    Data bus tidak ditemukan atau filter belum diatur.
</div>
<?php endif; ?>

</div>

<!-- ================= SISWA BELUM BOOKING ================= -->
<div class="col-md-4">
<div class="card border-warning shadow-sm">
<div class="card-header bg-warning py-2 d-flex justify-content-between align-items-center">
    <span class="small fw-bold text-dark">Siswa Belum Booking</span>
    <span class="badge bg-dark rounded-pill"><?= count($siswaBelumBooking) ?></span>
</div>

<div class="card-body p-2" style="max-height: 500px; overflow-y: auto; font-size: 13px;">
<?php if (!empty($siswaBelumBooking)): ?>
    <div class="list-group list-group-flush">
        <?php foreach ($siswaBelumBooking as $s): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-1">
            <span><?= esc($s->nama) ?></span>
            <span class="badge bg-secondary small"><?= esc($s->rombel) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="text-center p-3 text-muted small">
        Semua siswa sudah booking.
    </div>
<?php endif; ?>
</div>

</div>
</div>

</div>
</div>
</div>
</div>

<!-- ================= SCRIPT FILTER ================= -->
<script>
function cetakBus() {
    const busId = document.getElementById('bus_print').value;
    if(!busId){
        alert('Pilih bus terlebih dahulu');
        return;
    }
    window.open("<?= base_url('admin/report/printbus') ?>?bus_id=" + busId, "_blank");
}
const kelasList = <?= json_encode($kelasList) ?>;

function loadKelas(jenjang, selectedKelas = '') {
    let opt = '<option value="">-- Semua Kelas --</option>';

    if (!jenjang) {
        $('#kelas').html(opt).prop('disabled', true);
        return;
    }

    kelasList.forEach(k => {
        if (k.match(/\d+/)[0] === jenjang) {
            const selected = (k === selectedKelas) ? 'selected' : '';
            opt += `<option value="${k}" ${selected}>${k}</option>`;
        }
    });

    $('#kelas').html(opt).prop('disabled', false);
}

$('#jenjang').on('change', function(){
    loadKelas(this.value);
});

$(document).ready(function(){
    const selectedJenjang = "<?= $jenjang ?? '' ?>";
    const selectedKelas   = "<?= $kelas ?? '' ?>";

    if(selectedJenjang !== ''){
        loadKelas(selectedJenjang, selectedKelas);
    } else {
        $('#kelas').prop('disabled', true);
    }
});
</script>

</body>