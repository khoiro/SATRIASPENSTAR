<!DOCTYPE html>
<html lang="id">
<?= view('shared/head') ?>

<body>
<div class="wrapper">
    <?= view('admin/navbar') ?>

    <div class="content-wrapper p-4">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Reset Kamar</h3>
                </div>

                <div class="card-body" style="position:relative">
                    <table id="kamarTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th></th>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Kamar</th>
                                <th>Terisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div id="loadingOverlay"
                         style="display:none; position:absolute; inset:0;
                         background:rgba(255,255,255,.7);
                         display:flex; align-items:center; justify-content:center">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="globalDeleteForm" method="post">
    <?= csrf_field() ?>
</form>

<script>
$(function () {

    let table = $('#kamarTable').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 0
            }
        },
        ajax: '<?= base_url('admin/datatableresetkamar') ?>',
        columnDefs: [
            { className: 'control', orderable: false, targets: 0 },
            { orderable: false, targets: -1 }
        ],
        columns: [
            { data: null, defaultContent: '', className:'control' },
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm' },
            { extend: 'print', className: 'btn btn-secondary btn-sm' },
        ]
    });

    table.on('preXhr.dt', () => $('#loadingOverlay').show());
    table.on('xhr.dt', () => $('#loadingOverlay').hide());

    $('#kamarTable').on('click', '.btn-reset-booking', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Reset kamar?',
            text: 'Data booking kamar akan dihapus',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus'
        }).then((res) => {
            if (res.isConfirmed) {
                $('#globalDeleteForm')
                    .attr('action', '/admin/resetkamar/delete/' + id)
                    .submit();
            }
        });
    });

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= session()->getFlashdata('success') ?>',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif ?>
});
</script>
</body>
</html>
