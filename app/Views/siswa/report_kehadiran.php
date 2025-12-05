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

<script>
let kehadiranChart = null;
const dataUrl = '<?= site_url('siswa/kehadiran/data-ajax') ?>';

function updateChart(hadir, tidakHadir) {
    const data = {
        labels: ['Hadir', 'Tidak Hadir'],
        datasets: [{
            data: [hadir, tidakHadir],
            backgroundColor: ['#0d6efd', '#dc3545'],
            borderWidth: 3,
            borderColor: '#fff'
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
    button.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Memuat...`;

    try {
        const res = await fetch(`${dataUrl}?start=${start}&end=${end}`);
        const data = await res.json();

        document.getElementById('statTotalHariKerja').textContent = `${data.total_hari_kerja} hari`;
        document.getElementById('statTotalHadir').textContent = `${data.total_hadir} hari`;
        document.getElementById('statPersentase').textContent = `${data.persentase}%`;

        document.getElementById('legendHadir').textContent = `Hadir: ${data.total_hadir} hari`;
        document.getElementById('legendTidakHadir').textContent = `Tidak Hadir: ${data.total_tidak_hadir} hari`;
        document.getElementById('legendTotalHari').textContent = `Total Hari: ${data.total_hari_kerja} hari`;

        updateChart(data.total_hadir, data.total_tidak_hadir);

    } catch (err) {
        alert("Gagal memuat data.");
    }

    button.disabled = false;
    button.textContent = "Terapkan Filter";
}

document.addEventListener("DOMContentLoaded", () => {
    updateChart(
        <?= $total_hadir ?>, 
        <?= $total_hari_kerja - $total_hadir ?>
    );

    document.getElementById("filterForm").addEventListener("submit", function(e) {
        e.preventDefault();
        loadKehadiran(inputStart.value, inputEnd.value);
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
