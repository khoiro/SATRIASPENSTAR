<!DOCTYPE html>
<html lang="en">


<?= view('shared/head') ?>
<!-- Tambahan CSS DataTables -->


<body>
  <div class="wrapper">
    <?= view('admin/navbar') ?>
    <div class="content-wrapper p-4">
      <div class="container">
        <div class="card">
          <div class="card-header">
              <h3 class="card-title">Data User</h3>
          </div>
          <div class="card-body" id="userTableContainer" style="position: relative;"> 
              <table id="userTable" class="table table-bordered table-hover">
                  <thead>
                      <tr>
                          <th></th>
                          <th>No</th>
                          <th>Nama</th>
                          <th>Email</th>
                          <th>Role</th>
                          <th>NISN</th>
                          <th>Aksi</th>
                      </tr>
                  </thead>
                  <tbody>
                      </tbody>
              </table>

              <div id="loadingOverlay" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 10; display: flex; justify-content: center; align-items: center;">
                  <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Loading...</span>
                  </div>
              </div>
          </div>
        </div>
    </div>
  </div>

  <form id="globalDeleteForm" method="POST" action="">
      <input type="hidden" name="method" value="POST">
      <input type="hidden" name="id" id="deleteUserId">
  </form>

  <script>
    $(document).ready(function () {
        var userTable = $('#userTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // Kolom pertama jadi pemicu responsive expand
                    }
                },
                autoWidth: false,
                ajax: {
                    url: '<?= base_url('admin/datatableuser') ?>',
                    type: 'GET'
                },
                columnDefs: [
                    {
                        className: 'control',
                        orderable: false,
                        targets: 0 // Kolom "No" jadi icon expand mobile
                    },
                    {
                        targets: -1, // Kolom aksi (terakhir)
                        orderable: false,
                        searchable: false
                    }
                ],
                columns: [
                    { data: null, defaultContent: '', title: "", className: 'control' }, // kolom responsif
                    { data: '0', title: "No" },
                    { data: '1', title: "Full Name" },
                    { data: '2', title: "Email" },
                    { data: '3', title: "Role" },
                    { data: '4', title: "NISN" },
                    { data: '5', title: "Aksi" }
                ],
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', className: 'btn btn-secondary btn-sm' },
                    {
                        text: '<i class="fas fa-plus"></i> Tambah Data',
                        className: 'btn btn-primary btn-sm mr-2',
                        action: function (e, dt, node, config) {
                            window.location.href = '<?= base_url('admin/manage/add') ?>';
                        }
                    },
                    // Tombol Generate Password Massal
                    {
                        text: '<i class="fas fa-key"></i> Generate Password Massal',
                        className: 'btn btn-warning btn-sm mr-2',
                        action: function (e, dt, node, config) {
                            Swal.fire({
                                title: 'Generate Password Massal',
                                text: "Apakah Anda yakin ingin generate password semua siswa aktif?",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Generate!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if(result.isConfirmed){
                                    fetch('<?= base_url('admin/generatepassword') ?>', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if(data.status === 'success'){
                                            // Tampilkan hasil di modal SweetAlert
                                            let html = `<table class="table table-bordered">
                                                <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>Password Baru</th></tr></thead><tbody>`;
                                            data.data.forEach((item, i) => {
                                                html += `<tr>
                                                    <td>${i+1}</td>
                                                    <td>${item.nama}</td>
                                                    <td>${item.nisn}</td>
                                                    <td>${item.password_baru}</td>
                                                </tr>`;
                                            });
                                            html += `</tbody></table>`;

                                            Swal.fire({
                                                title: 'Password Berhasil Digenerate',
                                                html: html,
                                                width: '800px',
                                                scrollbarPadding: false,
                                                confirmButtonText: 'Tutup'
                                            });
                                        } else {
                                            Swal.fire('Gagal', data.message, 'error');
                                        }
                                    })
                                    .catch(err => {
                                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                                    });
                                }
                            });
                        }
                    },
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Tidak ada data tersedia",
                    zeroRecords: "Tidak ada data yang cocok",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    }
                }
        });


        // Menampilkan loading overlay sebelum permintaan AJAX
        userTable.on('preXhr.dt', function (e, settings, data) {
            // Selector tetap sama, karena kita menempatkan overlay di dalam kontainer
            $('#loadingOverlay').show(); 
        });

        // Menyembunyikan loading overlay setelah permintaan AJAX selesai
        userTable.on('xhr.dt', function (e, settings, json, xhr) {
            $('#loadingOverlay').hide();
        });

    

        // Event listener untuk tombol delete
        // Gunakan event delegation karena tombol dibuat secara dinamis oleh DataTables
        $('#userTable tbody').on('click', '.btn-delete-user', function() {
            const siswaId = $(this).data('id'); // Ambil ID siswa dari atribut data-id

            Swal.fire({
                title: 'Konfirmasi Hapus User',
                text: "Apakah Anda yakin ingin menghapus user ini secara permanen?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika dikonfirmasi, set action URL form dan submit
                    const form = $('#globalDeleteForm');
                    form.attr('action', '<?= site_url('admin/manage/delete') ?>/' + siswaId); // Sesuaikan URL
                    // Jika Anda mengirim ID melalui body POST, uncomment baris ini:
                    $('#deleteUserId').val(siswaId);
                    form.submit();
                }
            });
        });

        // Periksa apakah ada flashdata 'success'
        <?php if (session()->getFlashdata('success')) : ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= session()->getFlashdata('success') ?>',
                showConfirmButton: false,
                timer: 2000 // Notifikasi akan hilang setelah 2 detik
            });
        <?php endif; ?>

        // Periksa apakah ada flashdata 'error'
        <?php if (session()->getFlashdata('error')) : ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= session()->getFlashdata('error') ?>',
                showConfirmButton: true,
                confirmButtonText: 'Tutup'
            });
        <?php endif; ?>
      
    });

    $('#userTable tbody').on('click', '.btn-view-password', function() {

        const userId = $(this).data('id');

        fetch('<?= base_url('admin/getuserpassword') ?>/' + userId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(res => res.json())
        .then(data => {

            if(data.status === 'success'){

                let html = `
                    <table class="table table-bordered">
                        <tr>
                            <th>Nama</th>
                            <td>${data.data.nama}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>${data.data.username}</td>
                        </tr>
                        <tr>
                            <th>Password</th>
                            <td><b>${data.data.password}</b></td>
                        </tr>
                    </table>
                `;

                Swal.fire({
                    title: 'Detail Akun Siswa',
                    html: html,
                    width: 600,
                    confirmButtonText: 'Tutup'
                });

            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        });

    });
  </script>


</body>
</html>