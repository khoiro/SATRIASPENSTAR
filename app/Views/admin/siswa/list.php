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
              <h3 class="card-title">Data Siswa</h3>
          </div>
          <div class="card-body" id="siswaTableContainer" style="position: relative;"> 
              <table id="siswaTable" class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th></th>
                      <th>No</th>
                      <th>NISN</th>
                      <th>Nama</th>
                      <th>Alamat</th>
                      <th>Kelas</th>
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

  <div id="loadingSpinner" style="display: none; position: fixed; top: 50%; left: 50%; z-index: 9999; transform: translate(-50%, -50%);">
      <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden"></span>
      </div>
  </div>

  <form id="globalDeleteForm" method="POST" action="">
      <input type="hidden" name="method" value="POST">
      <input type="hidden" name="id" id="deleteSiswaId">
  </form>

  <!-- Modal info siswa -->
  <div class="modal fade" id="simpleInfoModal" tabindex="-1" aria-labelledby="simpleInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        
        <div class="modal-header">
          <h5 class="modal-title" id="simpleInfoModalLabel">Detail Siswa</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

        </div>

        <div class="modal-body d-flex">
          <div class="me-4">
            <img id="modalPhoto" src="https://placehold.co/400" alt="Foto Siswa" class="img-thumbnail mr-3" style="width: 150px; height: 150px;">
          </div>
          <div>
            <p><strong>NISN:</strong> <span id="modalNisn">-</span></p>
            <p><strong>NIS:</strong> <span id="modalNis">-</span></p>
            <p><strong>Nama:</strong> <span id="modalNama">-</span></p>
            <p><strong>Tanggal Lahir:</strong> <a href="#" id="modalTglLahir">-</a></p>
            <p><strong>Alamat:</strong> <span id="modalAlamat">-</span></p>
            <p><strong>Telpon Siswa:</strong> <span id="modalTelponSiswa">-</span></p>
            <p><strong>Telpon Ortu:</strong> <span id="modalTelponOrtu">-</span></p>
            <p><strong>Kelas:</strong> <span id="modalKelas">-</span></p>
            <p><strong>Rombel:</strong> <span id="modalRombel">-</span></p>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal Import -->
  <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form action="<?= base_url('admin/importsiswa') ?>" method="post" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="importModalLabel">Import Data Siswa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Link download template -->
            <div class="mb-3">
              <a href="<?= base_url('template/template_import_siswa.xlsx') ?>" download class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download"></i> Download Template Excel
              </a>
            </div>

            <!-- Upload file -->
            <div class="mb-3">
              <label for="file_excel" class="form-label">Pilih File Excel (.xlsx, .xls, .csv):</label>
              <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx,.csv" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Import</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      var siswaTable = $('#siswaTable').DataTable({
          responsive: {
              details: {
                  type: 'column',
                  target: 0
              }
          },
          columnDefs: [
              { className: 'control', orderable: false, targets: 0 },
              { targets: -1, orderable: false, searchable: false }
          ],
          order: [1, 'asc'],
          ajax: '<?= base_url('admin/datatablesiswa') ?>',
          columns: [
              { data: null, defaultContent: '', className: 'control' },
              { data: '0' },
              { data: '1' },
              { data: '2' },
              { data: '3' },
              { data: '4' },
              { data: '5' }
          ],
          dom: 'Bfrtip',
          buttons: [
              { extend: 'excel', className: 'btn btn-success btn-sm' },
              { extend: 'pdf', className: 'btn btn-danger btn-sm' },
              { extend: 'print', className: 'btn btn-secondary btn-sm' },
              {
                  text: '<i class="fas fa-plus"></i> Tambah Data',
                  className: 'btn btn-primary btn-sm mr-2',
                  action: function () {
                      window.location.href = '<?= base_url('admin/siswa/add') ?>';
                  }
              },
              {
                  text: '<i class="fas fa-file-import"></i> Import Data',
                  className: 'btn btn-info btn-sm',
                  action: function () {
                      $('#importModal').modal('show');
                  }
              }
          ]
      });


      // Menampilkan loading overlay sebelum permintaan AJAX
      siswaTable.on('preXhr.dt', function (e, settings, data) {
          // Selector tetap sama, karena kita menempatkan overlay di dalam kontainer
          $('#loadingOverlay').show(); 
      });

      // Menyembunyikan loading overlay setelah permintaan AJAX selesai
      siswaTable.on('xhr.dt', function (e, settings, json, xhr) {
          $('#loadingOverlay').hide();
      });

      // Event listener untuk tombol delete
      // Gunakan event delegation karena tombol dibuat secara dinamis oleh DataTables
      $('#siswaTable tbody').on('click', '.btn-delete-siswa', function() {
          const siswaId = $(this).data('id'); // Ambil ID siswa dari atribut data-id

          Swal.fire({
              title: 'Konfirmasi Hapus Data Siswa',
              text: "Apakah Anda yakin ingin menghapus data siswa ini secara permanen?",
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
                  form.attr('action', '/admin/siswa/delete/' + siswaId); // Sesuaikan URL
                  // Jika Anda mengirim ID melalui body POST, uncomment baris ini:
                  $('#deleteSiswaId').val(siswaId);
                  form.submit();
              }
          });
      });

      // view siswa
       $('#siswaTable tbody').on('click', '.btn-view-siswa', function(e) {
        e.preventDefault();
         const siswaId = $(this).data('id'); // Ambil ID siswa dari atribut data-id
         
         $.ajax({
            url: '/admin/findsiswa/' + siswaId, // Contoh: /api/info/denise-spangler-id
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#loadingSpinner').show(); // Tampilkan loading spinner
            },
            success: function(data) {
                 const avatarUrl = data.dataUser && data.dataUser.avatar
                                  ? '/uploads/avatar/' + data.dataUser.avatar
                                  : 'https://placehold.co/400';
                $('#modalPhoto').attr('src', avatarUrl);
                $('#modalNisn').text(data.data.nisn);
                $('#modalNama').text(data.data.nama);
                $('#modalTglLahir').text(data.data.tgl_lahir);
                $('#modalAlamat').text(data.data.alamat);
                $('#modalTelponSiswa').text(data.data.telp_siswa);
                $('#modalTelponOrtu').text(data.data.telp_ortu);
                $('#modalKelas').text(data.data.kelas);
                $('#modalRombel').text(data.data.rombel);

                // Tampilkan modal
                $('#simpleInfoModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Gagal mengambil data informasi:", textStatus, errorThrown);
                alert("Terjadi kesalahan saat mengambil informasi. Silakan coba lagi.");
            },
            complete: function() {
                $('#loadingSpinner').hide(); // Sembunyikan spinner setelah selesai
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
  </script>


</body>
</html>