<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>

<body>
<div class="wrapper">

    <?= view('admin/navbar'); ?> <!-- navbar + sidebar -->

    <div class="content-wrapper p-4">
        <section class="content">
            <div class="container-fluid">

                <!-- JUDUL -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h3 class="font-weight-bold">
                            📊 Grafik Rekap Absensi Bulanan
                        </h3>
                    </div>
                </div>

                <!-- FILTER BULAN -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select id="bulan" class="form-control">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == date('m')) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="tahun" class="form-control">
                            <?php for ($y = date('Y') - 3; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= ($y == date('Y')) ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="kelas" class="form-control">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($kelas as $k): ?>
                                <option value="<?= $k ?>"><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <!-- LOADING -->
                <div id="loadingChart"
                    class="text-center"
                    style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.7); z-index:10;">
                    <div class="d-flex h-100 justify-content-center align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- CARD GRAFIK -->
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-body">

                                <canvas id="grafikAbsensi" height="120"></canvas>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('grafikAbsensi');

    let chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Tepat Waktu', 'Terlambat', 'Alpha', 'Libur'],
            datasets: [{
                data: [
                    <?= $rekap['tepat_waktu'] ?>,
                    <?= $rekap['terlambat'] ?>,
                    <?= $rekap['alpha'] ?>,
                    <?= $rekap['libur'] ?>
                ],
                backgroundColor: ['#28a745','#ffc107','#dc3545','#6c757d']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    $('#bulan, #tahun').change(function () {

        $('#loadingChart').fadeIn(200);

        $.ajax({
            url: '<?= base_url('admin/reportajaxstatusabsensi') ?>',
            type: 'GET',
            data: {
                bulan: $('#bulan').val(),
                tahun: $('#tahun').val()
            },
            success: function (res) {
                chart.data.datasets[0].data = [
                    res.tepat_waktu,
                    res.terlambat,
                    res.alpha,
                    res.libur
                ];
                chart.update();
            },
            complete: function () {
                $('#loadingChart').fadeOut(200);
            },
            error: function () {
                alert('Gagal memuat data absensi');
            }
        });
    });
</script>


</body>
</html>
