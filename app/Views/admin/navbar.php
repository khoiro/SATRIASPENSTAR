<!-- ===================== -->
<!-- TOP NAVBAR -->
<!-- ===================== -->
<nav class="main-header navbar navbar-expand navbar-dark bg-dark">
  <!-- Left navbar -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="/admin" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="/admin/profile" class="nav-link">Edit Profile</a>
    </li>
  </ul>

  <!-- Right navbar -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <a class="nav-link" href="/logout">
        <i class="fas fa-sign-out-alt"></i> Sign Out
      </a>
    </li>
  </ul>
</nav>
<!-- /.navbar -->


<!-- ===================== -->
<!-- SIDEBAR -->
<!-- ===================== -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <!-- Brand Logo -->
  <a href="/admin" class="brand-link">
    <img src="<?= base_url('/logo_smp.png') ?>"
         alt="Logo"
         class="brand-image img-circle elevation-3"
         style="opacity:.8">
    <span class="brand-text font-weight-light">SPENSTAR</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">

    <?php
      $avatar = \Config\Services::login()->getAvatarUrl();
      $avatarUrl = $avatar
        ? base_url($avatar)
        : base_url('assets/img/user-default.png');
    ?>

    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="<?= $avatarUrl ?>"
             class="img-circle elevation-2"
             alt="User Image"
             style="width:45px;height:45px;object-fit:cover;">
      </div>
      <div class="info">
        <a href="/admin/profile" class="d-block">
          <?= esc(\Config\Services::login()->name ?? 'Administrator') ?>
        </a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="true">

        <!-- Dashboard -->
        <li class="nav-item">
          <a href="/admin"
             class="nav-link <?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- MASTER DATA -->
        <li class="nav-item has-treeview <?= in_array(($page ?? ''), ['manage','siswa']) ? 'menu-open' : '' ?>">
          <a href="#"
             class="nav-link <?= in_array(($page ?? ''), ['manage','siswa']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-database"></i>
            <p>
              Master Data
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="/admin/manage"
                 class="nav-link <?= ($page ?? '') === 'manage' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Users</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/admin/siswa"
                 class="nav-link <?= ($page ?? '') === 'siswa' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Siswa</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- PENGATURAN -->
        <li class="nav-item has-treeview <?= in_array(($page ?? ''), ['location','holiday']) ? 'menu-open' : '' ?>">
          <a href="#"
             class="nav-link <?= in_array(($page ?? ''), ['location','holiday']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-cogs"></i>
            <p>
              Pengaturan
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="/admin/location"
                 class="nav-link <?= ($page ?? '') === 'location' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Koordinat Lokasi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/admin/holiday"
                 class="nav-link <?= ($page ?? '') === 'holiday' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Jadwal Libur</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- LAPORAN -->
        <li class="nav-item has-treeview <?= in_array(($page ?? ''), ['report_absensi','reportstatusabsensi']) ? 'menu-open' : '' ?>">
           <a href="#"
             class="nav-link <?= in_array(($page ?? ''), ['report_absensi','reportstatusabsensi']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>
              Laporan
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="/admin/report"
                 class="nav-link <?= ($page ?? '') === 'report_absensi' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Report Absensi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/admin/reportstatusabsensi"
                 class="nav-link <?= ($page ?? '') === 'reportstatusabsensi' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Report Status Absensi</p>
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </nav>
  </div>
</aside>
