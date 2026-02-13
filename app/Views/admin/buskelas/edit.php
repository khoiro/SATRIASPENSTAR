<!DOCTYPE html>
<html lang="id">
<?= view('shared/head') ?>

<body>
<div class="wrapper">
    <?= view('admin/navbar') ?>

    <?php /** @var \App\Entities\bus $item */ ?>

    <div class="content-wrapper p-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">

                    <div class="card shadow-sm rounded">
                        <div class="card-body">

                            <div class="d-flex mb-3">
                                <h1 class="h4 mb-0 mr-auto"><?= esc($subtitle) ?></h1>
                                <a href="/admin/buskelas" class="btn btn-outline-secondary ml-2">
                                    Kembali
                                </a>
                            </div>

                            <form method="post">

                                <!-- NOMOR KAMAR -->
                                <label class="d-block mb-3">
                                    <span>Nama Bus</span>
                                    <select name="bus_id" class="form-control" required>
                                        <option value="">-- Pilih Bus --</option>
                                        <?php foreach ($busList as $bus): ?>
                                            <option value="<?= $bus['id'] ?>"
                                                <?= ($item->bus_id == $bus['id']) ? 'selected' : '' ?>>
                                                <?= esc($bus['nama_bus']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>


                                <!-- JENJANG -->
                                <label class="d-block mb-3">
                                    <span>Kelas</span>
                                    <select class="form-control" id="rombel" name="rombel" required>
                                        <option value="">-- Semua Kelas --</option>
                                        <?php foreach ($datarombel as $k): ?>
                                            <option value="<?= esc($k) ?>"
                                                <?= ($item->rombel == $k) ? 'selected' : '' ?>>
                                                <?= esc($k) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>


                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary mr-auto">
                                        Simpan
                                    </button>

                                    <?php if ($item->id): ?>
                                        <button type="button"
                                                class="btn btn-danger"
                                                id="btn-delete">
                                            Hapus
                                        </button>
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

<?php if (session()->getFlashdata('error')): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= session()->getFlashdata('error') ?>'
});
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= session()->getFlashdata('success') ?>'
});
</script>
<?php endif; ?>


<?php if ($item->id): ?>
<form method="POST" action="/admin/buskelas/delete/<?= $item->id ?>" id="deleteForm">
</form>
<?php endif ?>

<script>
<?php if ($item->id): ?>
$('#btn-delete').on('click', function () {
    Swal.fire({
        title: 'Hapus Setting Bus Kelas?',
        text: 'Data setting bus kelas akan dihapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#deleteForm').submit();
        }
    });
});
<?php endif ?>
</script>

</body>
</html>
