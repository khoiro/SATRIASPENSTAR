<?= view('shared/head') ?>
<style>
    .divider-vertical {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 66.666%;
        width: 1px;
        background-color: #e5e7eb;
    }

    .card-kamar {
        border: 1px solid #e5e7eb;
        transition: .2s;
    }
    .card-kamar:hover {
        border-color: #0d6efd;
        box-shadow: 0 6px 16px rgba(13,110,253,.15);
    }

    /* === PANEL SISWA BELUM BOOKING === */
    .panel-siswa {
        border: 2px solid #ffc107;
        background: #fffaf0;
        border-radius: 8px;
    }

    .panel-siswa-header {
        background: #ffc107;
        color: #212529;
        padding: 10px 14px;
        font-weight: 700;
        border-radius: 6px 6px 0 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .siswa-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px 12px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
    }

    .siswa-item:hover {
        background: #fffbe6;
        border-color: #ffc107;
    }
</style>

<body>
<div class="wrapper">
<?= view('admin/navbar'); ?>

<div class="content-wrapper p-4">
<div class="container-fluid">

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-bed"></i> Report Book Kamar
    </div>

    <div class="card-body">

        <!-- FILTER -->
        <form class="mb-4" method="get">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label>Jenjang</label>
                    <select class="form-control" id="jenjang" name="jenjang">
                        <option value="">-- Semua Jenjang --</option>
                        <?php foreach ($jenjangList as $j): ?>
                            <option value="<?= $j ?>" <?= ($j == ($_GET['jenjang'] ?? '')) ? 'selected' : '' ?>>
                                <?= $j ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Kelas</label>
                    <select class="form-control" id="kelas" name="kelas">
                        <option value="">-- Semua Kelas --</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-success w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="#"
                        id="btnPrint"
                        target="_blank"
                        class="btn btn-outline-primary w-100">
                        <i class="fas fa-print"></i> Print Layout1
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="#"
                    id="btnPrint2"
                    target="_blank"
                    class="btn btn-outline-secondary w-100">
                        <i class="fas fa-print"></i> Print Layout2
                    </a>
                </div>


            </div>
        </form>


        <!-- CONTENT -->
        <div class="row position-relative mt-4">
            <div class="divider-vertical d-none d-md-block"></div>

            <!-- DATA KAMAR -->
            <div class="col-md-8">
                <h6 class="text-primary fw-bold mb-3">
                    <i class="fas fa-door-open"></i> Data Kamar
                </h6>

                <div class="row">
                    <?php foreach ($dataKamar as $kamar): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card card-kamar h-100">
                            <div class="card-header d-flex justify-content-between">
                                <?= esc($kamar['nama_kamar']) ?>
                                <span class="badge bg-success">
                                    <?= count($kamar['penghuni']) ?>/<?= $kamar['kapasitas'] ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if ($kamar['penghuni']): ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($kamar['penghuni'] as $p): ?>
                                            <li>
                                                <i class="fas fa-user"></i>
                                                <?= esc($p['nama_siswa'] . ' (' . $p['rombel'] . ')') ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <em class="text-muted">Belum ada penghuni</em>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SISWA BELUM BOOKING -->
            <div class="col-md-4">
                <div class="panel-siswa shadow-sm">

                    <div class="panel-siswa-header">
                        <i class="fas fa-user-times"></i>
                        Siswa Belum Booking
                        <span class="badge bg-dark ms-auto">
                            <?= count($siswaBelumBooking) ?>
                        </span>
                    </div>

                    <div class="p-3">
                        <?php if ($siswaBelumBooking): ?>
                            <?php foreach ($siswaBelumBooking as $s): ?>
                                <div class="siswa-item">
                                    <div>
                                        <i class="fas fa-user text-warning"></i>
                                        <?= esc($s['nama_siswa']) ?>
                                    </div>
                                    <span class="badge bg-secondary">
                                        <?= esc($s['rombel']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <em class="text-muted">Semua siswa sudah booking</em>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

</div>
</div>
</div>

<script>
    const kelasList = <?= json_encode($kelasList) ?>;

    function updatePrintLink() {
        const jenjang = $('#jenjang').val();
        const kelas   = $('#kelas').val();

        let url = "<?= site_url('admin/report/printbookkamar') ?>";

        const params = [];
        if (jenjang) params.push('jenjang=' + encodeURIComponent(jenjang));
        if (kelas)   params.push('kelas=' + encodeURIComponent(kelas));

        if (params.length) {
            url += '?' + params.join('&');
        }

        $('#btnPrint').attr('href', url);
    }

    function updatePrintLink2() {
        const jenjang = $('#jenjang').val();
        const kelas   = $('#kelas').val();

        let url = "<?= site_url('admin/report/printbookkamar2') ?>";

        const params = [];
        if (jenjang) params.push('jenjang=' + encodeURIComponent(jenjang));
        if (kelas)   params.push('kelas=' + encodeURIComponent(kelas));

        if (params.length) {
            url += '?' + params.join('&');
        }

        $('#btnPrint2').attr('href', url);
    }

    function loadKelas(jenjang){
        let opt = '<option value="">-- Semua Kelas --</option>';

        if(!jenjang){
            $('#kelas').html(opt).prop('disabled', true);
            updatePrintLink();
            return;
        }

        kelasList.forEach(k => {
            if(k.match(/\d+/)[0] === jenjang){
                opt += `<option value="${k}">${k}</option>`;
            }
        });

        $('#kelas').html(opt).prop('disabled', false);
        updatePrintLink();
        updatePrintLink2();
    }

    // event jenjang
    $('#jenjang').on('change', function(){
        loadKelas(this.value);
    });

    // event kelas
    $('#kelas').on('change', function(){
        updatePrintLink();
        updatePrintLink2();
    });

    // set awal saat page load
    $(document).ready(function(){
        updatePrintLink();
        updatePrintLink2();
    });
</script>



</body>
</html>
