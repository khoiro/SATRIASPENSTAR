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

                        <!-- TOTAL HARI KERJA -->
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-secondary text-white">
                                <p class="mb-1 small fw-semibold">Total Hari Masuk</p>
                                <h4 id="statHariKerja"><?= $total_hari_kerja ?> hari</h4>
                            </div>
                        </div>

                        <!-- HADIR -->
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-success text-white">
                                <p class="mb-1 small fw-semibold">Hadir</p>
                                <h4 id="statHadir"><?= $hadir ?></h4>
                                <small id="persenHadir">0%</small>
                            </div>
                        </div>

                        <!-- IZIN -->
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-warning text-dark">
                                <p class="mb-1 small fw-semibold">Izin</p>
                                <h4 id="statIzin"><?= $izin ?></h4>
                                <small id="persenIzin">0%</small>
                            </div>
                        </div>

                        <!-- SAKIT -->
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-info text-white">
                                <p class="mb-1 small fw-semibold">Sakit</p>
                                <h4 id="statSakit"><?= $sakit ?></h4>
                                <small id="persenSakit">0%</small>
                            </div>
                        </div>

                        <!-- ALPHA -->
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-danger text-white">
                                <p class="mb-1 small fw-semibold">Tidak Hadir</p>
                                <h4 id="statAlpha"><?= $alpha ?></h4>
                                <small id="persenAlpha">0%</small>
                            </div>
                        </div>

                        <!-- TERLAMBAT -->
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 shadow-sm bg-dark text-white">
                                <p class="mb-1 small fw-semibold">Terlambat</p>
                                <h4 id="statTerlambat"><?= $total_terlambat ?></h4>
                                <small id="persenTerlambat">0%</small>
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
                                            <span id="legendHadir">Hadir: -</span>
                                        </div>

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="indicator bg-danger me-3"></div>
                                            <span id="legendTidakHadir">Tidak Hadir: -</span>
                                        </div>

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="indicator bg-warning me-3"></div>
                                            <span id="legendTerlambat">Terlambat: -</span>
                                        </div>


                                        <div class="d-flex align-items-center">
                                            <div class="indicator bg-secondary me-3"></div>
                                            <span id="legendTotalHari">Total Hari: -</span>
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

    function updateChart(data) {
        const chartData = {
            labels: ['Hadir', 'Izin', 'Sakit', 'Tidak Hadir'],
            datasets: [{
                data: [
                    data.hadir,
                    data.izin,
                    data.sakit,
                    data.alpha
                ],
                backgroundColor: [
                    '#198754',
                    '#ffc107',
                    '#0dcaf0',
                    '#dc3545'
                ],
                borderColor: '#fff',
                borderWidth: 3
            }]
        };

        if (kehadiranChart) {
            kehadiranChart.data = chartData;
            kehadiranChart.update();
        } else {
            const ctx = document.getElementById('chartKehadiran').getContext('2d');
            kehadiranChart = new Chart(ctx, {
                type: 'doughnut',
                data: chartData,
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }


    async function loadKehadiran(start, end) {
        showLoading();

        try {
            const res = await fetch(`${dataUrl}?start=${start}&end=${end}`);
            const d = await res.json();

            const total = d.total_hari_kerja || 1;

            document.getElementById('statHariKerja').innerText = `${total} hari`;
            document.getElementById('statHadir').innerText = d.hadir;
            document.getElementById('statIzin').innerText = d.izin;
            document.getElementById('statSakit').innerText = d.sakit;
            document.getElementById('statAlpha').innerText = d.alpha;
            document.getElementById('statTerlambat').innerText = d.terlambat;

            // ===== UPDATE LEGEND =====
            document.getElementById('legendHadir').innerText =
                `Hadir: ${d.hadir} hari`;

            document.getElementById('legendTidakHadir').innerText =
                `Tidak Hadir: ${d.alpha} hari`;

            document.getElementById('legendTerlambat').innerText =
                `Terlambat: ${d.terlambat} kali`;

            document.getElementById('legendTotalHari').innerText =
                `Total Hari: ${total} hari`;


            document.getElementById('persenHadir').innerText = ((d.hadir / total) * 100).toFixed(2) + '%';
            document.getElementById('persenIzin').innerText = ((d.izin / total) * 100).toFixed(2) + '%';
            document.getElementById('persenSakit').innerText = ((d.sakit / total) * 100).toFixed(2) + '%';
            document.getElementById('persenAlpha').innerText = ((d.alpha / total) * 100).toFixed(2) + '%';
            document.getElementById('persenTerlambat').innerText = ((d.terlambat / total) * 100).toFixed(2) + '%';

            updateChart(d);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal memuat data',
                text: 'Silakan coba lagi'
            });
            console.error(err);
        } finally {
            hideLoading();
        }
    }



    document.addEventListener("DOMContentLoaded", () => {
        loadKehadiran('<?= $start ?>', '<?= $end ?>');

        const inputStartEl = document.getElementById('inputStart');
        const inputEndEl = document.getElementById('inputEnd');

        document.getElementById("filterForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const start = inputStartEl.value || '<?= date('Y-m-01') ?>';
            const end = inputEndEl.value || '<?= date('Y-m-t') ?>';

            loadKehadiran(start, end);
        });
    });


    function showLoading() {
        Swal.fire({
            title: 'Memuat data...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    function hideLoading() {
        Swal.close();
    }


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
