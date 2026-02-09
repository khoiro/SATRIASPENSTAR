<!DOCTYPE html>
<html lang="id">
<?= view('shared/head') ?>

<body>
<div class="wrapper">
    <?= view('admin/navbar') ?>

    <?php /** @var \App\Entities\Kamar $item */ ?>

    <div class="content-wrapper p-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">

                    <div class="card shadow-sm rounded">
                        <div class="card-body">

                            <div class="d-flex mb-3">
                                <h1 class="h4 mb-0 mr-auto"><?= esc($subtitle) ?></h1>
                                <a href="/admin/kamar" class="btn btn-outline-secondary ml-2">
                                    Kembali
                                </a>
                            </div>

                            <form method="post">

                                <!-- NOMOR KAMAR -->
                                <label class="d-block mb-3">
                                    <span>Nomor Kamar</span>
                                    <input type="text"
                                           name="nomor_kamar"
                                           class="form-control"
                                           value="<?= esc($item->nomor_kamar) ?>"
                                           required>
                                </label>

                                <!-- JENJANG -->
                                <label class="d-block mb-3">
                                    <span>Jenjang</span>
                                    <select name="jenjang" class="form-control" required>
                                        <option value="">-- Pilih Jenjang --</option>
                                        <?php foreach (['7', '8', '9'] as $j): ?>
                                            <option value="<?= $j ?>" <?= $item->jenjang === $j ? 'selected' : '' ?>>
                                                <?= $j ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </label>

                                <!-- KAPASITAS -->
                                <label class="d-block mb-3">
                                    <span>Kapasitas</span>
                                    <input type="number"
                                           name="kapasitas"
                                           class="form-control"
                                           min="1"
                                           value="<?= esc($item->kapasitas) ?>"
                                           required>
                                </label>

                                <!-- STATUS -->
                                <label class="d-block mb-4">
                                    <span>Status</span>
                                    <select name="status" class="form-control">
                                        <option value="1" <?= $item->status == 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= $item->status == 0 ? 'selected' : '' ?>>Nonaktif</option>
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

<?php if ($item->id): ?>
<form method="POST" action="/admin/kamar/delete/<?= $item->id ?>" id="deleteForm">
</form>
<?php endif ?>

<script>
<?php if ($item->id): ?>
$('#btn-delete').on('click', function () {
    Swal.fire({
        title: 'Hapus Kamar?',
        text: 'Data kamar akan dinonaktifkan!',
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
