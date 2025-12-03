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
              <h3 class="card-title">Data Artikel</h3>
          </div>
          <div class="card-body" id="articleTableContainer" style="position: relative;">
            <div class="form-group">
                <label for="categoryFilter">Filter berdasarkan Kategori:</label>
                <select id="categoryFilter" class="form-control" style="width: 200px;">
                    <option value="">-- Semua Kategori --</option>
                    <option value="news">News</option>
                    <option value="info">Info</option>
                    <option value="page">Page</option>
                    <option value="draft">Draft</option>
                    </select>
            </div> 
              <table id="articleTable" class="table table-bordered table-hover">
                  <thead>
                      <tr>
                          <th>No</th>
                          <th>Title</th>
                          <th>Author</th>
                          <th>Kategori</th>
                          <th>Updated</th>
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
      <input type="hidden" name="id" id="deleteArticleId">
  </form>

  <script>
    $(document).ready(function () {
      var articleTable = $('#articleTable').DataTable({
        responsive: true,
        autoWidth: false,
        // Hapus `processing: true` dan `serverSide: true`
        ajax: '<?= base_url('admin/datatablearticle') ?>',
        columns: [
            { title: "No" },
            { title: "Title" },
            { title: "Author" },
            { title: "Kategori" }, // Kolom kategori di indeks 3
            { title: "Updated" },
            { title: "Aksi", orderable: false, searchable: false }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm' },
            { extend: 'print', className: 'btn btn-secondary btn-sm' },
            {
                text: '<i class="fas fa-plus"></i> Tambah Data',
                className: 'btn btn-primary btn-sm mr-2',
                action: function ( e, dt, node, config ) {
                    window.location.href = '<?= base_url('admin/article/add') ?>';
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

    // Event listener untuk perubahan pada dropdown filter kategori (Client-side filtering)
    $('#categoryFilter').on('change', function() {
        var category = $(this).val();
        if (category) {
            // Apply filter to the 4th column (index 3)
            // `columns().eq(0)` -> memilih kolom
            // `.search()` -> menerapkan filter
            // `.draw()` -> menggambar ulang tabel
            articleTable.column(3).search('^' + category + '$', true, false).draw();
        } else {
            // Clear filter if "All Categories" is selected
            articleTable.column(3).search('').draw();
        }
    });
      // Menampilkan loading overlay sebelum permintaan AJAX
      articleTable.on('preXhr.dt', function (e, settings, data) {
          // Selector tetap sama, karena kita menempatkan overlay di dalam kontainer
          $('#loadingOverlay').show(); 
      });

      // Menyembunyikan loading overlay setelah permintaan AJAX selesai
      articleTable.on('xhr.dt', function (e, settings, json, xhr) {
          $('#loadingOverlay').hide();
      });

      // Event listener untuk tombol delete
      // Gunakan event delegation karena tombol dibuat secara dinamis oleh DataTables
      $('#articleTable tbody').on('click', '.btn-delete-article', function() {
          const siswaId = $(this).data('id'); // Ambil ID siswa dari atribut data-id

          Swal.fire({
              title: 'Konfirmasi Hapus Data Article',
              text: "Apakah Anda yakin ingin menghapus data Article ini secara permanen?",
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
                  form.attr('action', '/admin/article/delete/' + siswaId); // Sesuaikan URL
                  // Jika Anda mengirim ID melalui body POST, uncomment baris ini:
                  $('#deleteArticleId').val(siswaId);
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
  </script>


</body>
</html>