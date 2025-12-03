<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>

<body>
  <div class="wrapper">
    <?= view('admin/navbar') ?>
    <?php /** @var \App\Entities\Siswa $item */ ?>
    <div class="content-wrapper p-4">
      <div class="container" style="max-width: 540px;">
        <div class="card">
          <div class="card-body">
            <form enctype="multipart/form-data" method="post">
              <div class="d-flex mb-3">
                <h1 class="h3 mb-0 mr-auto"><?=$subtitle;?></h1>
                <a href="/admin/siswa/" class="btn btn-outline-secondary ml-2">Back</a>
              </div>
              <label class="d-block mb-3">
                <span>NISN</span>
                <input type="text" class="form-control" name="nisn" value="<?= esc($item->nisn) ?>" required>
              </label>
              <label class="d-block mb-3">
                <span>NIS</span>
                <input type="text" class="form-control" name="nis" value="<?= esc($item->nis) ?>" required>
              </label>
              <label class="d-block mb-3">
                <span>Nama</span>
                <input type="text" class="form-control" name="nama" value="<?= esc($item->nama) ?>" required>
              </label>
              <label class="d-block mb-3">
                <span>Tanggal Lahir</span>
                <input type="text" class="form-control" name="tgl_lahir" id="tglLahirInput" value="<?= esc($item->tgl_lahir) ?>" required>
              </label>
              <label class="d-block mb-3">
                <span>Alamat</span>
                <textarea class="form-control" name="alamat" required><?= esc($item->alamat) ?></textarea>
              </label>
              <label class="d-block mb-3">
                <span>Telpon Siswa</span>
                <input type="tel" class="form-control" name="telp_siswa" value="<?= esc($item->telp_siswa) ?>" required>
              </label>
              <label class="d-block mb-3">
                <span>Telpon Ortu</span>
                <input type="tel" class="form-control" name="telp_ortu" value="<?= esc($item->telp_ortu) ?>" required>
              </label>
              <label class="d-block mb-3">
                <span>Kelas</span>
                <select name="kelas" class="form-control">
                  <?= implode('', array_map(function ($x) use ($item) {
                    return '<option ' . ($item->kelas === $x ? 'selected' : '') .
                      ' value="' . $x . '">' . ucfirst($x) . '</option>';
                  }, \App\Models\SiswaModel::$kelas)) ?>
                </select>
              </label>
             <label class="d-block mb-3">
                <span>Rombel</span>
                <select name="rombel" class="form-control">
                  <?= implode('', array_map(function ($x) use ($item) {
                    return '<option ' . ($item->rombel === $x ? 'selected' : '') .
                      ' value="' . $x . '">' . ucfirst($x) . '</option>';
                  }, \App\Models\SiswaModel::$rombel)) ?>
                </select>
              </label>
              <div class="d-flex mb-3">
                <input type="submit" value="Save" class="btn btn-primary mr-auto">
                <?php if ($item->id) : ?>
                  <label for="delete-form" class="btn btn-danger mb-0"><i class="fa fa-trash"></i></label>
                <?php endif ?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <form method="POST" action="/admin/siswa/delete/<?= $item->id ?>">
    <input type="submit" hidden id="delete-form" onclick="return confirm('Do you want to delete this user permanently?')">
  </form>

<script>
  $(document).ready(function() {
    
    $('input[type="tel"]').on('keypress', function(e) {
      var charCode = (e.which) ? e.which : e.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning', // Ikon peringatan
          title: 'Oops...',
          text: 'Hanya angka yang diizinkan untuk kolom ini!',
          // toast: true, // Membuat notifikasi kecil yang muncul di sudut
          // position: 'top-end', // Posisi notifikasi (atas kanan)
          showConfirmButton: false, // Tidak menampilkan tombol konfirmasi
          timer: 3000, // Notifikasi akan hilang setelah 3 detik
          timerProgressBar: true, // Menampilkan progress bar di timer
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
          }
        });
      }
      return true;
    });

    $('#tglLahirInput').datepicker({
      dateFormat: 'yy-mm-dd', // Format tanggal: Tahun-Bulan-Tanggal (misal: 2024-01-31)
      changeMonth: true,     // Memungkinkan pemilihan bulan
      changeYear: true,      // Memungkinkan pemilihan tahun
      yearRange: '-100:+0',  // Rentang tahun (misal: 100 tahun ke belakang sampai tahun ini)
      // Opsi lain yang mungkin berguna:
      // showButtonPanel: true, // Menampilkan tombol "Today" dan "Done"
      // currentText: "Today",  // Teks untuk tombol "Today"
      // closeText: "Done",     // Teks untuk tombol "Done"
      // maxDate: "+0D",        // Batasi tanggal maksimum hingga hari ini (untuk tanggal lahir)
      // defaultDate: "-20y"    // Default tanggal saat kalender dibuka (misal: 20 tahun lalu)
    });

  });
</script>
</body>

</html>