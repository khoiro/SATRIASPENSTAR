<!DOCTYPE html>
<html lang="en">

<?= view('shared/head') ?>

<body>
<div class="wrapper">
    <?= view('admin/navbar') ?>

    <div class="content-wrapper p-4">
        <div class="container">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Menu Update Status Pembayaran</h3>
                </div>

                <div class="card-body">

                    <table id="tableBayar" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    var table = $('#tableBayar').DataTable({
        ajax: "<?= base_url('admin/datatableupdatebayar') ?>",
        columns: [
            { data: 'no' },
            { data: 'nisn' },
            { data: 'nama' },
            { data: 'kelas' },
            { data: 'status_bayar' },
            { data: 'aksi' }
        ]
    });

    // tombol update bayar
    $('#tableBayar').on('click', '.btn-konfirmasi', function () {

        var id    = $(this).data('id');
        var nama  = $(this).data('nama');
        var kelas = $(this).data('kelas');

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `
                <div style="text-align:left">
                    <p><b>Nama:</b> ${nama}</p>
                    <p><b>Kelas:</b> ${kelas}</p>
                    <hr>
                    <p>Yakin ubah status menjadi <b>Lunas</b>?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "<?= base_url('admin/updatebayar/konfirmasi') ?>/" + id,
                    type: "POST",
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: "json",
                    success: function(response) {

                        if (response.status === 'success') {

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            table.ajax.reload(null, false);

                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });

            }
        });

    });

});
</script>

</body>
</html>