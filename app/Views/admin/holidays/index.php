<style>
    /* Pastikan CSS ini dimuat setelah CSS Bootstrap */
    .alert .close {
        /* Mengubah warna 'x' menjadi putih */
        color: #fff;
        /* Memberikan sedikit transparansi, agar terlihat berbeda dari teks */
        opacity: 0.8;
    }

    .alert .close:hover {
        /* Mengubah warna menjadi lebih solid saat di-hover */
        color: #fff;
        opacity: 1;
    }
</style>
<?= view('shared/head') ?>
<body>
<div class="wrapper">
    <?= view('admin/navbar'); ?>
    
    <div class="content-wrapper p-4">
        <div class="container-fluid mt-4">
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <h5 class="mb-0">Pengaturan Jadwal Hari Libur</h5>
                </div>
                
                <div class="card-body">
                    
                    <h5 class="mb-3">Tambah Jadwal Libur Baru</h5>
                    <form id="formHoliday">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="tanggal_mulai">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="tanggal_akhir">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="keterangan">Keterangan/Nama Libur</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Contoh: Cuti Bersama Idul Fitri" required>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block" id="btnSimpanLibur">
                                    <i class="fas fa-plus me-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                        
                        <?php 
                        // Menggabungkan pesan sukses dan error menjadi satu blok untuk efisiensi
                        $success_message = session()->getFlashdata('success');
                        $error_message = session()->getFlashdata('error');

                        if ($success_message || $error_message): 
                            
                            // Tentukan kelas alert (success atau danger) dan pesan
                            $alert_class = $success_message ? 'alert-success' : 'alert-danger';
                            $message = $success_message ? $success_message : $error_message;
                        ?>
                            <div class="alert <?= $alert_class ?> alert-dismissible fade show mt-2" role="alert">
                                <?= $message ?>
                                
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                    </form>
                    
                    <hr>
                    
                    <h5 class="mb-3">Tampilan Kalender Libur</h5>
                    <div id="calendar" style="height: 600px;"></div> 

                    <hr>

                    <h5 class="mt-4 mb-3">Daftar Libur Tersimpan</h5>
                    <div class="table-responsive">
                       <table id="holidayTable" class="table table-bordered table-hover w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Akhir</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let calendar;

        const calendarEl = document.getElementById('calendar');

        // Data awal dari PHP
        const holidaysData = <?= json_encode($holidays) ?>;

        const events = holidaysData.map(h => ({
            title: h.keterangan,
            start: h.tanggal_mulai,
            end: new Date(new Date(h.tanggal_akhir).getTime() + (24 * 60 * 60 * 1000)).toISOString().split('T')[0],
            allDay: true,
            color: '#dc3545'
        }));

        // INIT FULL CALENDAR
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'id',
            events: events,
            dateClick: function(info) {
                document.getElementById('tanggal_mulai').value = info.dateStr;
                document.getElementById('tanggal_akhir').value = info.dateStr;
            }
        });

        calendar.render();

        // INIT DATATABLE
        const table = $('#holidayTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('admin/holiday/datatable') ?>",
                type: "POST"
            }
        });

        // ===============================
        // 🔥 AJAX SIMPAN JADWAL LIBUR
        // ===============================
        $('#formHoliday').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "<?= site_url('admin/holiday/store') ?>",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(res) {

                    if (res.status === "success") {

                        // Refresh DataTable
                        $('#holidayTable').DataTable().ajax.reload(null, false);

                        // Refresh Calendar Event
                        calendar.removeAllEvents();
                        fetch("<?= site_url('admin/holiday/events') ?>")
                            .then(r => r.json())
                            .then(data => {
                                data.forEach(ev => calendar.addEvent(ev));
                            });

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#formHoliday')[0].reset();

                    } else {

                        let errorText = "";

                        if (typeof res.message === 'object') {
                            errorText = Object.values(res.message).join("\n");
                        } else {
                            errorText = res.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorText
                        });
                    }

                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: "Terjadi kesalahan saat menyimpan data."
                    });
                }
            });
        });

        // ===============================
        // 🔥 AJAX HAPUS JADWAL LIBUR
        // ===============================
        $('#holidayTable').on('click', '.btn-delete-holiday', function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const url = "<?= site_url('admin/holiday/delete') ?>/" + id;

            Swal.fire({
                title: 'Hapus Jadwal Libur?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "GET",
                        success: function() {

                            // Reload DataTable
                            $('#holidayTable').DataTable().ajax.reload(null, false);

                            // Reload Events di Calendar
                            calendar.removeAllEvents();
                            fetch("<?= site_url('admin/holiday/events') ?>")
                                .then(r => r.json())
                                .then(data => {
                                    data.forEach(ev => calendar.addEvent(ev));
                                });

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Jadwal libur berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Tidak dapat menghapus data.'
                            });
                        }
                    });
                }
            });
        });

    });
</script>

</body>
</html>