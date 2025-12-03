<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>

<body>
  <div class="wrapper">
    <?= view('admin/navbar') ?>
    <?php /** @var \App\Entities\User $item */ ?>
    <div class="content-wrapper p-4">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-6 mb-4">
            <div class="card rounded shadow-sm">
              <div class="card-body">
                <h1 class="h3 mb-3">Cari User</h1>
                 <label class="d-block mb-3">
                    <span>NISN</span>
                    <input type="text" class="form-control mb-3" name="carinisn" placeholder="Masukkan NISN" value="<?= esc($item->nisn) ?>" required>
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button" id="carinisn">Cari</button>
                    </div>
                  </label>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6 mb-4">
            <div class="card rounded shadow-sm">
              <div class="card-body">
                <form enctype="multipart/form-data" method="post">
                  <div class="d-flex mb-3">
                    <h1 class="h3 mb-0 mr-auto"><?= $subtitle?></h1>
                    <a href="/admin/manage/" class="btn btn-outline-secondary ml-2">Kembali</a>
                  </div>
                  <label class="d-block mb-3">
                    <span>Nama Lengkap</span>
                    <input type="hidden" class="form-control mb-3" name="nisn" value="<?= esc($item->nisn) ?>">
                    <input type="text" class="form-control" name="name" value="<?= esc($item->name) ?>" required>
                  </label>
                  <label class="d-block mb-3">
                    <span>Email</span>
                    <input type="text" class="form-control" name="email" value="<?= esc($item->email) ?>" required>
                    <small class="text-danger fs-6 d-none" id="email-error">Email sudah terdaftar</small>
                  </label>
                  <label class="d-block mb-3">
                    <span>Kata Sandi</span>
                    <input type="password" class="form-control" name="password" placeholder="<?= $item->id ? 'Hanya masukkan jika ingin mengubah kata sandi Anda' : '" required="required' ?>">
                  </label>
                  <label class="d-block mb-3">
                    <span>Avatar</span>
                    <?= view('shared/file', [
                      'value' => $item->avatar,
                      'name' => 'avatar',
                      'path' => 'avatar',
                      'disabled' => false,
                    ]) ?>
                  </label>
                  <label class="d-block mb-3">
                    <span>Peran</span>
                    <select name="role" class="form-control">
                      <?= implode('', array_map(function ($x) use ($item) {
                        return '<option ' . ($item->role === $x ? 'selected' : '') .
                          ' value="' . $x . '">' . ucfirst($x) . '</option>';
                      }, \App\Models\UserModel::$roles)) ?>
                    </select>
                  </label>
                  <div class="d-flex mb-3">
                    <input type="submit" value="Simpan" class="btn btn-primary mr-auto">
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
    </div>
  </div>

  <form method="POST" action="/admin/manage/delete/<?= $item->id ?>">
    <input type="submit" hidden id="delete-form" onclick="return confirm('Apakah Anda ingin menghapus pengguna ini secara permanen?')">
  </form>

  <script>

    $('input[name="carinisn"]').on('keypress', function(e) {
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

   // Fungsi untuk mencari NISN
    function cariNISN() {
      let nisn = $('input[name="carinisn"]').val();
      console.log(nisn);
      if (nisn) {
        $.ajax({
          url: '/admin/getnisn',
          method: 'GET',
          data: { nisn: nisn },
          dataType: 'json',
          success: function(response) {
            if (response.name !== null) {
              $('input[name="name"]').val(response.name);
              $('input[name="nisn"]').val(nisn);
              $('input[name="email"]').focus();
            } else {
              Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'NISN tidak ditemukan !!!, Periksa NISN...',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                  toast.addEventListener('mouseenter', Swal.stopTimer);
                  toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
              });
            }
          }
        });
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Oops...',
          text: 'NISN wajib diisi!',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
          }
        });
      }
    }

    // Ketika tombol diklik
    $('#carinisn').on('click', function(e) {
      cariNISN();
    });

    // Ketika tombol Enter ditekan di input NISN
    $('input[name="carinisn"]').on('keydown', function(e) {
      if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        cariNISN();
      }
    });

    let emailValid = true;

    // Cek email saat diketik
    $('input[name="email"]').on('input', function () {
      let email = $(this).val();
      let $error = $('#email-error');

      if (email.length > 5) {
        $.ajax({
          url: '/admin/cekemail',
          method: 'GET',
          data: { email: email },
          dataType: 'json',
          success: function (response) {
            if (response.exists) {
              $error.removeClass('d-none');
              $('input[name="email"]').addClass('is-invalid');
              emailValid = false;
            } else {
              $error.addClass('d-none');
              $('input[name="email"]').removeClass('is-invalid');
              emailValid = true;
            }
          }
        });
      } else {
        $error.addClass('d-none');
        $('input[name="email"]').removeClass('is-invalid');
        emailValid = true;
      }
    });

    // Cegah pindah field pakai Tab atau Enter
    $('input[name="email"]').on('keydown', function(e) {
      if (!emailValid && (e.key === 'Tab' || e.key === 'Enter' || e.keyCode === 9 || e.keyCode === 13)) {
        e.preventDefault();
      }
    });

    // Cegah pindah pakai mouse klik
    $('input, select, textarea').on('focusin', function (e) {
      let activeField = document.activeElement;
      if (!emailValid && activeField.name !== 'email') {
        e.preventDefault();
        $('input[name="email"]').focus();
      }
    });



  </script>
</body>
</html>
