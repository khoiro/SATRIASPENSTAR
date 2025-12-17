<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>
<body>
 <div class="wrapper">

  <?= view('admin/navbar'); ?> <!-- navbar + sidebar -->

  <div class="content-wrapper p-4">
    <section class="content">
      <div class="container-fluid">

        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="card">
              <div class="card-body text-center">
                <h1 class="mb-4">Welcome to Portal!</h1>

                <div class="btn-group btn-block">
                  <a href="/user/article/" class="btn btn-outline-primary py-4">
                    <i class="fas fa-2x fa-info"></i><br>Articles
                  </a>
                  <a href="/user/manage/" class="btn btn-outline-primary py-4">
                    <i class="fas fa-2x fa-users"></i><br>Users
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>

 </div>

</body>

</html>