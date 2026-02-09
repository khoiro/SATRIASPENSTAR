<!DOCTYPE html>
<html lang="id">
<?= view('shared/head') ?>

<body>
<div class="wrapper">
    <?= view('siswa/navbar') ?>

    <div class="content-wrapper p-4">
        <div class="container" style="max-width: 720px;">
            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="d-flex mb-3">
                        <h1 class="h4 mb-0 mr-auto">
                            <i class="fas fa-bed"></i> Booking Kamar
                        </h1>
                        <a href="/siswa" class="btn btn-outline-secondary ml-2">
                            Back
                        </a>
                    </div>

                    <!-- ALERT JIKA SUDAH BOOKING -->
                    <?php if ($sudahBooking): ?>
                        <div class="alert alert-success">

                            <div class="mb-2">
                                Anda sudah booking kamar:
                                <strong><?= esc($sudahBooking['nomor_kamar'] ?? $sudahBooking['kamar_id']) ?></strong>
                            </div>

                            <?php if (!empty($sudahBooking['penghuni'])): ?>
                                <div>
                                    <strong>Penghuni kamar:</strong>
                                    <ul class="mb-0 mt-1">
                                        <?php foreach ($sudahBooking['penghuni'] as $p): ?>
                                            <li>
                                                <?= esc($p['nama']) ?>
                                                <span class="text-warning">(<?= esc($p['rombel']) ?>)</span>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">
                                    Belum ada penghuni lain di kamar ini.
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endif ?>


                    <!-- LIST KAMAR -->
                    <?php foreach ($kamar as $k): ?>
                        <div class="border rounded p-3 mb-3">

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 class="mb-0"><?= esc($k['nomor_kamar']) ?></h5>

                                <?php if ($k['terisi'] >= $k['kapasitas']): ?>
                                    <span class="badge bg-danger">Penuh</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Tersedia</span>
                                <?php endif ?>
                            </div>

                            <div class="text-muted mb-2">
                                Kapasitas: <?= $k['kapasitas'] ?> |
                                Terisi: <?= $k['terisi'] ?>
                            </div>

                            <!-- DAFTAR SISWA DALAM KAMAR -->
                            <?php if (!empty($k['penghuni'])): ?>
                                <div class="mb-2">
                                    <small class="fw-bold">Penghuni:</small>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach ($k['penghuni'] as $p): ?>
                                            <li>
                                                <?= esc($p['nama']) ?>
                                                <span class="text-muted">(<?= esc($p['rombel']) ?>)</span>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="text-muted mb-2">
                                    <em>Belum ada penghuni</em>
                                </div>
                            <?php endif ?>

                            <!-- TOMBOL BOOKING -->
                            <?php if ($k['terisi'] >= $k['kapasitas']): ?>
                                <span class="badge bg-secondary">Tidak tersedia</span>

                            <?php elseif ($sudahBooking): ?>
                                <span class="badge bg-secondary">Sudah Booking</span>

                            <?php else: ?>
                                <a href="javascript:void(0)"
                                class="btn btn-primary btn-sm btn-booking"
                                data-url="<?= site_url('siswa/bookingkamar/book/'.$k['id']) ?>"
                                data-kamar="<?= esc($k['nomor_kamar']) ?>">
                                    Booking Kamar
                                </a>
                            <?php endif ?>

                        </div>
                    <?php endforeach ?>


                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // ALERT SUCCESS (jika ada)
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    <?php endif; ?>

    // KONFIRMASI BOOKING (SELALU AKTIF)
    $(document).on('click', '.btn-booking', function () {
        const url   = $(this).data('url');
        const kamar = $(this).data('kamar');

        Swal.fire({
            title: 'Konfirmasi Booking',
            text: 'Yakin ingin booking kamar ' + kamar + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Booking',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

});
</script>


</body>
</html>
