<!DOCTYPE html>
<html lang="<?= lang('Interface.code') ?>">

<?= view('shared/head') ?>

<body class="text-center" style="background: url(https://images.unsplash.com/photo-1608501078713-8e445a709b39?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1953&q=80) center/cover #7452bf; position: relative">
    <?= view('home/styling') ?>
    <div class="justify-content-center container d-flex flex-column" style="min-height: 100vh; max-width: 476px">
        <p class="my-5"><a href="/"><img src="/logo_smp.png" alt="Logo" width="150px"></a></p>
        <form method="POST" name="loginForm" class="container shadow d-flex flex-column justify-content-center pb-1 pt-3 text-white">

            <?= csrf_field() ?>
            <h1 class="mb-4">Register Username</h1>
            <?= $errors ?>

            <input type="text" name="nisn" id="nisnInput" placeholder="NISN" value="<?= old('nisn') ?>" class="form-control mb-2" autocomplete="off">
            <input type="text" name="tgllahir" id="dobInput" placeholder="Date of Birth (yyyy-mm-dd)" value="<?= old('tgllahir') ?>" class="form-control mb-2">
            <input type="submit" value="check" id="checkButton" class="btn bg-dark btn mb-3">

            <div id="registrationFields" style="display: none;">
                <input type="text" name="name" placeholder="Full Name" value="<?= old('name') ?>" class="form-control mb-2">
                <input type="text" name="email" placeholder="Email / Username" value="<?= old('email') ?>" class="form-control mb-2">
                <small id="email-error" class="fs-6 d-none" style="color: white; background-color: rgba(255, 0, 0, 0.7); padding: 2px 5px; border-radius: 3px;">Email sudah terdaftar</small>
                <input type="password" name="password" placeholder="Password" class="form-control mb-2" autocomplete="new-password">
                <div class="g-recaptcha mb-2 mx-auto" data-sitekey="<?= $recapthaSite ?>"></div>
                <p><small>By continuing you're agreeing with our service and privacy terms</small></p>
                <input type="submit" id='registerButton' value="Register" class="btn bg-indigo btn mb-3">
            </div>

        </form>
        <div class="d-flex mb-5 text-shadow">
            <a href="/login" class="btn btn-link text-white mr-auto">Sign In Instead</a>
        </div>

        <div class="floating">
            <small>
                <a href="https://unsplash.com/photos/2FiXtdnVhjQ" target="_blank" rel="noopener noreferrer">Background by Jezael Melgoza</a>
            </small>
        </div>
    </div>

      <!-- datepicker -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            // Ketika tombol dengan ID 'checkButton' diklik
            $('#checkButton').on('click', function(e) {
                e.preventDefault(); // Mencegah form untuk submit secara default (dan refresh halaman)

                // Ambil nilai NISN dan Tanggal Lahir
                var nisn = $('#nisnInput').val();
                var tgllahir = $('#dobInput').val();
                // Ambil token CSRF (penting untuk keamanan di CodeIgniter)
                var csrfToken = $('input[name="csrf_test_name"]').val(); 

                // Lakukan permintaan AJAX
                $.ajax({
                    url: 'check-nisn-dob', // Ini adalah URL endpoint di controller CodeIgniter Anda
                    method: 'POST', // Metode permintaan
                    data: {
                        nisn: nisn,
                        tgllahir: tgllahir,
                        csrf_test_name: csrfToken // Kirim token CSRF bersama data
                    },
                    dataType: 'json', // Harapkan respons dalam format JSON dari server
                    success: function(response) {
                        // Fungsi ini akan dijalankan jika permintaan AJAX berhasil
                        if (response.success) {
                            // Jika NISN dan tanggal lahir cocok (berhasil), tampilkan bidang pendaftaran
                            $('#registrationFields').slideDown(); // Gunakan slideDown untuk efek animasi
                            // Sangat penting: Perbarui token CSRF setelah setiap permintaan AJAX yang berhasil
                            // CodeIgniter akan menghasilkan token baru, dan Anda perlu memperbarui input tersembunyi
                            $('input[name="csrf_test_name"]').val(response.csrfHash);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil...',
                                text: response.message + ' Silahkan lanjutkan registrasi dengan membuat username dan password',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                },
                                didClose: () => {
                                    $('input[name="name"]').val(response.data.nama);
                                    $('input[name="email"]').focus(); // Fokus setelah Swal benar-benar ditutup
                                }
                            });

                        } else {
                            // Jika tidak cocok, sembunyikan kembali bidang pendaftaran
                            $('#registrationFields').slideUp(); // Sembunyikan dengan efek animasi
                            // Perbarui token CSRF bahkan jika gagal untuk permintaan berikutnya
                            $('input[name="csrf_test_name"]').val(response.csrfHash);
                            Swal.fire({
                                        icon: 'warning', // Ikon peringatan
                                        title: 'Oops...',
                                        text: response.message,
                                        // toast: true, // Membuat notifikasi kecil yang muncul di sudut
                                        // position: 'top-end', // Posisi notifikasi (atas kanan)
                                        showConfirmButton: false, // Tidak menampilkan tombol konfirmasi
                                        timer: 3000, // Notifikasi akan hilang setelah 3 detik
                                        timerProgressBar: true, // Menampilkan progress bar di timer
                                        didOpen: (toast) => {
                                            toast.addEventListener('mouseenter', Swal.stopTimer);
                                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                                        },
                                        didClose: () => {
                                            $('input[name="nisn"]').focus();
                                        }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Fungsi ini akan dijalankan jika ada kesalahan pada permintaan AJAX (misal: error jaringan, server error 500)
                        console.error("Kesalahan AJAX: " + status + " - " + error);
                        alert("Terjadi kesalahan saat melakukan pengecekan. Mohon coba lagi.");
                    }
                });
            });

            $('#nisnInput').on('keypress', function(e) {
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

            $('#dobInput').datepicker({
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

            $('#registerButton').on('click', function(e) {
                e.preventDefault(); // Cegah submit form langsung

                const email = $('input[name="email"]').val().trim();
                const password = $('input[name="password"]').val().trim();

                // Validasi email/username
                if (email === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Kosong!',
                        text: 'Silakan isi email atau username.',
                        showConfirmButton: true,
                        didClose: () => {
                                    $('input[name="email"]').focus();
                                }
                    });
                    return;
                }

                // Validasi panjang password
                if (password.length < 8) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Password Terlalu Pendek!',
                        text: 'Password minimal 8 karakter.',
                        showConfirmButton: true,
                        didClose: () => {
                                     $('input[name="password"]').focus();
                                }
                    });
                    return;
                }

                // Jika semua validasi lolos, submit form secara manual
                $('form[name="loginForm"]').submit();
            });

            let emailValid = true;

            // Cek email saat diketik
            $('input[name="email"]').on('input', function () {
                let email = $(this).val();
                let $error = $('#email-error');

                if (email.length > 5) {
                    $.ajax({
                    url: '/home/cekemail',
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



        });
    </script>
</body>

</html>