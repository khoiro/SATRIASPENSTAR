<?= view('shared/head') ?>
<body>

<div class="wrapper">
<?= view('siswa/navbar') ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">

            <!-- CARD UTAMA -->
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h5 class="mb-0 fw-semibold text-center">📊 Laporan Kehadiran Siswa</h5>
                </div>

                <div class="card-body p-4">

                    <!-- FORM FILTER -->
                    <form id="filterForm" class="row g-3 mb-4 px-lg-2">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">Dari Tanggal</label>
                            <input type="date" id="inputStart" name="start" class="form-control shadow-sm"
                                value="<?= $start ?>">
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">Sampai Tanggal</label>
                            <input type="date" id="inputEnd" name="end" class="form-control shadow-sm"
                                value="<?= $end ?>">
                        </div>

                        <div class="col-lg-4 d-flex align-items-end">
                            <button id="filterButton" class="btn btn-primary w-100 shadow-sm fw-semibold py-2">
                                Terapkan Filter
                            </button>
                        </div>
                    </form>

                    <!-- STATISTICS -->
                    <div class="row g-3 mb-4" id="statsContainer">

                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-info text-white h-100">
                                <p class="mb-1 small fw-semibold">Total Hari Kerja</p>
                                <h3 class="mb-0 fw-bold" id="statTotalHariKerja">
                                    <?= $total_hari_kerja ?> hari
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-success text-white h-100">
                                <p class="mb-1 small fw-semibold">Total Kehadiran</p>
                                <h3 class="mb-0 fw-bold" id="statTotalHadir">
                                    <?= $total_hadir ?> hari
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-warning text-dark h-100">
                                <p class="mb-1 small fw-semibold">Total Keterlambatan</p>
                                <h3 class="mb-0 fw-bold" id="statTotalTerlambat">
                                    <?= $total_terlambat ?> kali
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-danger text-white h-100">
                                <p class="mb-1 small fw-semibold">Persentase Keterlambatan</p>
                                <h3 class="mb-0 fw-bold" id="statPersentaseTerlambat">
                                    <?= number_format(($total_terlambat / ($total_hari_kerja ?: 1)) * 100, 2) ?>%
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-primary text-white h-100">
                                <p class="mb-1 small fw-semibold">Persentase Kehadiran</p>
                                <h3 class="mb-0 fw-bold" id="statPersentase">
                                    <?= $persentase ?>%
                                </h3>
                            </div>
                        </div>

                    </div>

                    <!-- GRAFIK -->
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-0 py-3 rounded-top-4">
                            <h6 class="fw-bold text-center mb-0">Grafik Kehadiran</h6>
                        </div>

                        <div class="card-body">

                            <div class="row align-items-center">

                                <!-- Chart -->
                                <div class="col-12 col-lg-6 d-flex justify-content-center mb-3 mb-lg-0">
                                    <div style="max-width: 320px;">
                                        <canvas id="chartKehadiran"></canvas>
                                    </div>
                                </div>

                                <!-- Legend -->
                                <div class="col-12 col-lg-6">
                                    <div class="p-2">

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="indicator bg-primary me-3"></div>
                                            <span id="legendHadir" class="fw-semibold">
                                                Hadir: <?= $total_hadir ?> hari
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="indicator bg-danger me-3"></div>
                                            <span id="legendTidakHadir" class="fw-semibold">
                                                Tidak Hadir: <?= ($total_hari_kerja - $total_hadir) ?> hari
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="indicator bg-warning me-3"></div>
                                            <span id="legendTerlambat" class="fw-semibold">
                                                Terlambat: <?= $total_terlambat ?> kali
                                            </span>
                                        </div>


                                        <div class="d-flex align-items-center">
                                            <div class="indicator bg-secondary me-3"></div>
                                            <span id="legendTotalHari" class="fw-semibold">
                                                Total Hari: <?= $total_hari_kerja ?> hari
                                            </span>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let kehadiranChart = null;
    const dataUrl = '<?= site_url('siswa/kehadiran/data-ajax') ?>';

    function updateChart(hadir, tidakHadir, terlambat) {
        // pastikan angka (parseInt) untuk menghindari string issues
        hadir = Number(hadir) || 0;
        tidakHadir = Number(tidakHadir) || 0;
        terlambat = Number(terlambat) || 0;

        const data = {
            labels: ['Hadir', 'Tidak Hadir', 'Terlambat'],
            datasets: [{
                data: [hadir, tidakHadir, terlambat],
                backgroundColor: ['#0d6efd', '#dc3545', '#ffc107'],
                borderWidth: 3,
                borderColor: ['#fff','#fff','#fff']
            }]
        };

        if (kehadiranChart) {
            kehadiranChart.data = data;
            kehadiranChart.update();
        } else {
            const ctx = document.getElementById('chartKehadiran').getContext('2d');
            kehadiranChart = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    plugins: { legend: { display: false }},
                    cutout: '70%',
                    animation: { animateScale: true }
                }
            });
        }
    }

    async function loadKehadiran(start, end) {
        const button = document.getElementById('filterButton');
        button.disabled = true;
        // simpan teks asli sehingga bisa dikembalikan lebih aman
        const originalText = button.textContent;
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...`;

        try {
            const res = await fetch(`${dataUrl}?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`);
            if (!res.ok) throw new Error('Network response was not ok');

            const data = await res.json();

            // update statistik
            document.getElementById('statTotalHariKerja').textContent = `${data.total_hari_kerja} hari`;
            document.getElementById('statTotalHadir').textContent = `${data.total_hadir} hari`;
            document.getElementById('statPersentase').textContent = `${data.persentase}%`;

            document.getElementById('legendHadir').textContent = `Hadir: ${data.total_hadir} hari`;
            document.getElementById('legendTidakHadir').textContent = `Tidak Hadir: ${data.total_tidak_hadir} hari`;
            document.getElementById('legendTotalHari').textContent = `Total Hari: ${data.total_hari_kerja} hari`;

            document.getElementById('statTotalTerlambat').textContent = `${data.total_terlambat} kali`;
            document.getElementById('legendTerlambat').textContent = `Terlambat: ${data.total_terlambat} kali`;
            const persenTerlambat = data.total_hari_kerja > 0 ? ((data.total_terlambat / data.total_hari_kerja) * 100).toFixed(2) : 0;
            document.getElementById('statPersentaseTerlambat').textContent = `${persenTerlambat}%`;


            // update chart
            updateChart(data.total_hadir, data.total_tidak_hadir, data.total_terlambat);

        } catch (err) {
            console.error(err);
            alert("Gagal memuat data. Cek console untuk detail.");
        } finally {
            button.disabled = false;
            button.textContent = originalText || "Terapkan Filter";
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        // Gunakan nilai server-side sebagai nilai awal (bukan objek data yang tak terdefinisi)
        updateChart(
            <?= (int) $total_hadir ?>,
            <?= (int) ($total_hari_kerja - $total_hadir) ?>,
            <?= (isset($total_terlambat) ? (int)$total_terlambat : 0) ?>
        );

        // pastikan ambil elemen input secara eksplisit
        const inputStartEl = document.getElementById('inputStart');
        const inputEndEl = document.getElementById('inputEnd');

        document.getElementById("filterForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const start = inputStartEl.value || '<?= date('Y-m-01') ?>';
            const end = inputEndEl.value || '<?= date('Y-m-t') ?>';

            loadKehadiran(start, end);
        });
    });
</script>


<style>
    .indicator {
        width: 22px;
        height: 22px;
        border-radius: 5px;
    }
</style>

</body>
</html>
