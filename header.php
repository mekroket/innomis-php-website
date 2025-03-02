<!DOCTYPE html>
<html lang="tr">
<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="İnnomis Logo">
        <h1 class="d-flex align-items-center">İnnomis</h1>
      </a>

      <i class="mobile-nav-toggle mobile-nav-show bi bi-list"></i>
      <i class="mobile-nav-toggle mobile-nav-hide d-none bi bi-x"></i>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Ana Sayfa</a></li>
          <li><a href="hakkimizda.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'hakkimizda.php') ? 'active' : ''; ?>">Hakkımızda</a></li>
          <li><a href="urul.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'urul.php') ? 'active' : ''; ?>">Yönetim Kurulu</a></li>
          <li><a href="galeri.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'galeri.php') ? 'active' : ''; ?>">Galeri</a></li>
          <li><a href="etkinlikler.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'etkinlikler.php') ? 'active' : ''; ?>">Etkinlikler</a></li>
          <li><a href="iletisim.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'iletisim.php') ? 'active' : ''; ?>">İletişim</a></li>
        </ul>
      </nav>

    </div>
  </header>
</html> 