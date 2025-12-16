<?= view('shared/head') ?>
<body>
<div class="wrapper">
    <?= view('admin/navbar'); ?>
    <div class="content-wrapper p-4">
        <div class="container-fluid mt-4">
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-history me-2"></i>
                    <h5 class="mb-0">&nbsp;Riwayat Absensi Siswa</h5>
                </div>
                <div class="card-body">
                    
                    <form id="filterForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="startDate">Tanggal Awal</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" value="<?= $startDate ?>">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="endDate">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" value="<?= $endDate ?>">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="kelas">Kelas</label>
                                <select class="form-control" id="kelas" name="kelas">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($kelasList as $k): ?>
                                        <option value="<?= $k ?>"><?= $k ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nama_siswa">Nama Siswa</label>
                                <select class="form-control" id="nama_siswa" name="nama_siswa" disabled>
                                    <option value="">-- Semua Siswa --</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100" id="btnFilter">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>


                    <div class="table-responsive">
                        <table id="absensiTable" class="table table-bordered table-hover w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Lokasi Masuk</th>
                                    <th>Foto Masuk</th> 
                                    <th>Foto Keluar</th> 
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const BASE_URL_FOTO = "<?= base_url('') ?>";
        // Inisialisasi Datatables Server-Side
        const absensiTable = $('#absensiTable').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "dom": 'Bfrtip', // 🔥 tombol muncul di atas tabel
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success',
                    title: 'Riwayat Absensi Siswa',
                    exportOptions: {
                        columns: [0,1,2,3,4,5] // ❗tanpa foto
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Riwayat Absensi Siswa',
                    exportOptions: {
                        columns: [0,1,2,3,4,5] // ❗tanpa foto
                    },
                    customize: function (doc) {
                        doc.styles.tableHeader.fontSize = 10;
                        doc.defaultStyle.fontSize = 9;
                        doc.content[1].table.widths = [
                            '5%', '20%', '15%', '15%', '15%', '30%'
                        ];
                    }
                }
            ], 
            "ajax": {
                "url": "<?= site_url('admin/report/get_absensi') ?>", // PASTIKAN RUTE INI BENAR
                "type": "POST",
                "data": function (d) {
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                    d.kelas     = $('#kelas').val(); // 🔥 filter kelas
                    d.nisn = $('#nama_siswa').val();
                },
                "error": function (xhr, error, code) {
                    console.log("Error loading data:", xhr.responseText);
                    alert("Gagal memuat data absensi.");
                }
            },
            "columns": [
                { "data": "no", "orderable": false },
                { 
                    "data": "nama", 
                    "orderable": true 
                },
                { "data": "tanggal" },
                { "data": "jam_masuk" },
                { "data": "jam_keluar" },
                
                // Kolom Lokasi Masuk (Link Google Maps)
                { 
                    "data": "koordinat_masuk", 
                    "orderable": false,
                    "render": function(data, type, row) {
                        if (data === 'N/A' || !data) {
                            return 'Tidak Ada Data Lokasi';
                        }
                        const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${data}`;
                        return `<a href="${googleMapsUrl}" target="_blank" class="btn btn-sm btn-info">
                                    Lihat Lokasi <i class="fas fa-map-marker-alt"></i>
                                </a>`;
                    }
                },
                
                // 🚀 KOLOM FOTO MASUK
                { 
                    "data": "foto_masuk", 
                    "orderable": false,
                    "render": function(data, type, row) {
                        if (!data) {
                            return '-';
                        }
                        // Render link yang membuka gambar dalam tab baru
                        // Atur lebar max-width agar tabel tidak melebar
                        const photoUrl = BASE_URL_FOTO + data;
                        return `
                                <img 
                                    data-src="${photoUrl}"
                                    class="lazy-foto"
                                    style="max-width:80px;border-radius:4px"
                                    alt="Foto">
                                `;


                    }
                },
                
                // 🚀 KOLOM FOTO KELUAR
                { 
                    "data": "foto_keluar", 
                    "orderable": false,
                    "render": function(data, type, row) {
                        if (!data) {
                            return '-';
                        }
                        const photoUrl = BASE_URL_FOTO + data;
                        return `
                            <img 
                                data-src="${photoUrl}"
                                class="lazy-foto"
                                style="max-width:80px;border-radius:4px"
                                alt="Foto">
                            `;


                    }
                }
            ],
            rowCallback: function(row, data) {
                // Format jam pembanding
                const batasWaktu = "06:45";

                // Pastikan ada jam masuk
                if (data.jam_masuk) {

                    // Hitung selisih menit
                    const jamMasuk = data.jam_masuk;
                    const selisihMenit = hitungSelisihMenit(batasWaktu, jamMasuk);

                    if (selisihMenit > 0) {
                        // Tambahkan warna merah pada baris
                        $(row).css("background-color", "#f8d7da");

                        // Tambahkan keterangan di bawah jam masuk
                        $('td:eq(3)', row).html(`
                            ${jamMasuk}<br>
                            <small class="text-danger">Terlambat ${selisihMenit} menit</small>
                        `);
                    }
                }
            },

            // ... pengaturan bahasa lainnya
        });

        // Event handler untuk tombol Filter
        $('#filterForm').on('submit', function(e) {
            e.preventDefault(); // Mencegah form submit default
            absensiTable.ajax.reload(null, false); // Muat ulang data Datatables dengan filter baru
        });

        function hitungSelisihMenit(batas, jamMasuk) {
            const [bh, bm] = batas.split(":").map(Number);
            const [mh, mm] = jamMasuk.split(":").map(Number);
            const totalBatas = bh * 60 + bm;
            const totalMasuk = mh * 60 + mm;
            return totalMasuk - totalBatas; // hasil bisa negatif
        }

       $('#absensiTable').on('draw.dt', function () {
            $('.lazy-foto').each(function () {
                if (!this.src) {
                    this.src = $(this).data('src');
                }
            });
        });

        $('#kelas').on('change', function () {
            const kelas = $(this).val();

            $('#nama_siswa').prop('disabled', true).html(
                '<option value="">Memuat...</option>'
            );

            if (kelas === '') {
                $('#nama_siswa')
                    .html('<option value="">-- Semua Siswa --</option>')
                    .prop('disabled', true);
                return;
            }

            $.ajax({
                url: "<?= site_url('admin/report/getSiswaByKelas') ?>",
                type: "POST",
                dataType: "json",
                data: { kelas: kelas },
                success: function (res) {
                    let option = '<option value="">-- Semua Siswa --</option>';
                    res.forEach(function (s) {
                        option += `<option value="${s.nisn}">${s.nama}</option>`;
                    });

                    $('#nama_siswa')
                        .html(option)
                        .prop('disabled', false);
                },
                error: function () {
                    alert('Gagal memuat data siswa');
                }
            });
        });




    });
</script>

</body>
</html>