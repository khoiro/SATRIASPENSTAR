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
                    <div class="row mb-3">

                        <div class="col-md-3">
                            <select id="filterJenjang" class="form-control">
                                <option value="">Semua Kelas</option>
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                                <option value="9">Kelas 9</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select id="filterKelas" class="form-control">
                                <option value="">Semua Rombel</option>
                                <option value="KELAS 7A">KELAS 7A</option>
                                <option value="KELAS 7B">KELAS 7B</option>
                                <option value="KELAS 7C">KELAS 7C</option>
                                <option value="KELAS 7D">KELAS 7D</option>
                                <option value="KELAS 7E">KELAS 7E</option>
                                <option value="KELAS 7F">KELAS 7F</option>
                                <option value="KELAS 7G">KELAS 7G</option>
                                <option value="KELAS 7H">KELAS 7H</option>
                                <option value="KELAS 8A">KELAS 8A</option>
                                <option value="KELAS 8B">KELAS 8B</option>
                                <option value="KELAS 8C">KELAS 8C</option>
                                <option value="KELAS 8D">KELAS 8D</option>
                                <option value="KELAS 8E">KELAS 8E</option>
                                <option value="KELAS 8F">KELAS 8F</option>
                                <option value="KELAS 8G">KELAS 8G</option>
                                <option value="KELAS 8H">KELAS 8H</option>
                                <option value="KELAS 9A">KELAS 9A</option>
                                <option value="KELAS 9B">KELAS 9B</option>
                                <option value="KELAS 9C">KELAS 9C</option>
                                <option value="KELAS 9D">KELAS 9D</option>
                                <option value="KELAS 9E">KELAS 9E</option>
                                <option value="KELAS 9F">KELAS 9F</option>
                                <option value="KELAS 9G">KELAS 9G</option>
                                <option value="KELAS 9H">KELAS 9H</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select id="filterStatus" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="0">Belum Bayar</option>
                                <option value="1">Lunas</option>
                            </select>
                        </div>

                    </div>

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

    var rombel = {
        "7": ["KELAS 7A","KELAS 7B","KELAS 7C","KELAS 7D","KELAS 7E","KELAS 7F","KELAS 7G","KELAS 7H"],
        "8": ["KELAS 8A","KELAS 8B","KELAS 8C","KELAS 8D","KELAS 8E","KELAS 8F","KELAS 8G","KELAS 8H"],
        "9": ["KELAS 9A","KELAS 9B","KELAS 9C","KELAS 9D","KELAS 9E","KELAS 9F","KELAS 9G","KELAS 9H"]
    };

    var table = $('#tableBayar').DataTable({
        ajax: {
            url: "<?= base_url('admin/datatableupdatebayar') ?>",
            data: function (d) {
                d.jenjang = $('#filterJenjang').val();
                d.kelas   = $('#filterKelas').val();
                d.status  = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'no' },
            { data: 'nisn' },
            { data: 'nama' },
            { data: 'kelas' },
            { data: 'status_bayar' },
            { data: 'aksi' }
        ]
    });

    $('#filterJenjang').change(function(){

        var jenjang = $(this).val();
        var kelas = rombel[jenjang] || [];

        var html = '<option value="">Semua Kelas</option>';

        kelas.forEach(function(k){
            html += '<option value="'+k+'">'+k+'</option>';
        });

        $('#filterKelas').html(html);

    });

    $('#filterJenjang, #filterKelas, #filterStatus').change(function(){

        Swal.fire({
            title: 'Memuat Data...',
            text: 'Sedang mengambil data siswa',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        table.ajax.reload(function(){
            Swal.close();
        });

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