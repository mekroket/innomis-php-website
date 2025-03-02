<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>İnovasyon ve Yönetim Bilişim Sistemleri Topluluğu</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/fav.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- Galeri Özel CSS -->
  <style>
    .gallery-item {
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
      border-radius: 8px;
    }

    .gallery-item img {
      width: 100%;
      height: auto;
      transition: transform 0.3s ease;
    }

    .gallery-item:hover img {
      transform: scale(1.1);
    }

    .gallery-item .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .gallery-item:hover .overlay {
      opacity: 1;
    }

    .gallery-item .overlay i {
      color: #fff;
      font-size: 2rem;
    }

    .filter-button-group {
      margin-bottom: 30px;
      text-align: center;
    }

    .filter-button-group .btn {
      margin: 5px;
    }

    /* Header'ın sabit kalmasını sağla ve arka planını siyah yap */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 9999;
      /* Sayfanın üstünde olması için */
      background-color: rgba(0, 0, 0, 0.8);
      /* Siyah arka plan */
      transition: background-color 0.3s ease;
      /* Arka plan rengi geçişi */
    }

    /* Sayfa kaydırıldıkça header'ın arka planını siyah yapmak */
    body {
      padding-top: 70px;
      /* Header'ın yüksekliği kadar üstten boşluk bırak */
    }

    /* Mobilde navmenu'nun görünürlüğünü kontrol et */
    @media (max-width: 768px) {
      .navmenu {
        display: none;
      }

      .navmenu.active {
        display: block;
      }

      .mobile-nav-toggle {
        display: block;
        cursor: pointer;
      }
    }
  </style>
</head>

<?php
// Veritabanı bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "innomis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Bağlantı hatası: " . $conn->connect_error);
}

// Fotoğrafları getir
$sql = "SELECT * FROM gallery";
$result = $conn->query($sql);
?>

<body class="index-page">
<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center me-auto me-lg-0">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="assets/img/ana logo.png" alt="">


      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="active">Anasayfa<br></a></li>
          <li><a href="index.php">Hakkımızda</a></li>
          <li><a href="galeri.php">Galeri</a></li>
          <li><a href="index.php">Ekibimiz</a></li>

          <li><a href="#contact">İletişim</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="register.php">Üye Ol</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background" style="margin-top: -5%;">

      <img src="assets/img/con.jpg" alt="" data-aos="fade-in">

      <div class="container">

        <div class="row justify-content-center text-center" data-aos="fade-up" data-aos-delay="100">
          <div class="col-xl-6 col-lg-8">
            <h2>Teknoloji ve Yaratıcılığın Buluştuğu Yer<span>.</span></h2>
            <p>"INNOMIS ile Geleceği Şekillendir!"</p>
          </div>
        </div>

        <div class="row gy-4 mt-5 justify-content-center" data-aos="fade-up" data-aos-delay="200">
          <div class="col-xl-2 col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="icon-box">
              <i class="bi bi-binoculars"></i>
              <h3>Eğitim</h3>
            </div>
          </div>
          <div class="col-xl-2 col-md-4" data-aos="fade-up" data-aos-delay="400">
            <div class="icon-box">
              <i class="bi bi-bullseye"></i>
              <h3>Konferans</h3>
            </div>
          </div>
          <div class="col-xl-2 col-md-4" data-aos="fade-up" data-aos-delay="500">
            <div class="icon-box">
              <i class="bi bi-fullscreen-exit"></i>
              <h3>Workshop</h3>
            </div>
          </div>
          <div class="col-xl-2 col-md-4" data-aos="fade-up" data-aos-delay="600">
            <div class="icon-box">
              <i class="bi bi-card-list"></i>
              <h3>Gezi</h3>
            </div>
          </div>
          <div class="col-xl-2 col-md-4" data-aos="fade-up" data-aos-delay="700">
            <div class="icon-box">
              <i class="bi bi-gem"></i>
              <h3>Yarışma</h3>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Hero Section -->

    <section id="gallery" class="gallery section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Galeri</h2>
        <p>Topluluğumuzun Etkinliklerinden Kareler</p>
      </div>

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gallery">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <div class="col-lg-4 col-md-6 gallery-item <?= htmlspecialchars($row['category']) ?>">
                <a href="assets/img/gallery/<?= htmlspecialchars($row['image_name']) ?>" data-lightbox="gallery">
                  <img src="assets/img/gallery/<?= htmlspecialchars($row['image_name']) ?>" alt="<?= htmlspecialchars($row['category']) ?>">
                  <div class="overlay">
                    <i class="bi bi-zoom-in"></i>
                  </div>
                </a>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p>Galeriye fotoğraf eklenmemiş.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>

  </main>

  <br><br><br><br><br><br><br><br><br><br><br><br><br><br>

  <footer id="footer" class="footer dark-background">
    <div class="footer-top">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 col-md-6 footer-about">
            <a href="index.html" class="logo d-flex align-items-center">
              <img src="assets/img/ana logo.png" alt="">
            </a>
            <div class="footer-contact pt-3">
              <p>Necmettin Erbakan Üniversitesi</p>
              <p>Uygulamalı Bilimler Fakültesi</p>
              <p class="mt-3"><strong>Phone:</strong> <span>+90 552 706 36 20</span></p>
              <p><strong>Email:</strong> <span>bilgi@innomis.tr</span></p>
            </div>
            <div class="social-links d-flex mt-4">
              <a href=""><i class="bi bi-twitter-x"></i></a>
              <a href=""><i class="bi bi-facebook"></i></a>
              <a href=""><i class="bi bi-instagram"></i></a>
              <a href=""><i class="bi bi-linkedin"></i></a>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 footer-links">
            <h4>Linkler</h4>
            <ul>
              <li><i class="bi bi-chevron-right"></i> <a href="#"> Anasayfa</a></li>
              <li><i class="bi bi-chevron-right"></i> <a href="#"> Hakkımızda</a></li>
              <li><i class="bi bi-chevron-right"></i> <a href="#"> Galeri</a></li>
              <li><i class="bi bi-chevron-right"></i> <a href="#"> Ekibimiz</a></li>
              <li><i class="bi bi-chevron-right"></i> <a href="#"> İletişim</a></li>
            </ul>
          </div>

          <div class="col-lg-2 col-md-3 footer-links">
            <h4>Alanlarımız</h4>
            <ul>
              <li><i class="bi bi-chevron-right"></i> Eğitim</li>
              <li><i class="bi bi-chevron-right"></i> Konferans</li>
              <li><i class="bi bi-chevron-right"></i> Workshop</li>
              <li><i class="bi bi-chevron-right"></i> Gezi</li>
              <li><i class="bi bi-chevron-right"></i> Yarışma</li>
            </ul>
          </div>

          <div class="col-lg-4 col-md-12 footer-newsletter">
            <h4>Topluluğumuza Katılın</h4>
            <p>Bültenimize abone olun ve etkinliklerimiz, projelerimiz ve teknoloji dünyasındaki en son gelişmelerden haberdar olun!</p>
          </div>
        </div>
      </div>

      <div class="copyright">
        <div class="container text-center">
          <p>© <span>Copyright</span> <strong class="px-1 sitename">INNOMIS</strong> <span>TÜm Hakları Saklıdır</span></p>
          <div class="credits">
          <a href="https://www.linkedin.com/in/oguz-kaan/">powered by oguzkaanekin</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@srexi/purecounterjs/dist/purecounter_vanilla.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js"></script>

  <!-- Galeri Özel JS -->
  <script>
    $(document).ready(function() {
      // Isotope Filtreleme
      var $gallery = $('.gallery').isotope({
        itemSelector: '.gallery-item',
        layoutMode: 'fitRows'
      });

      // Filtreleme Butonları
      $('.filter-button-group').on('click', 'button', function() {
        var filterValue = $(this).attr('data-filter');
        $gallery.isotope({
          filter: filterValue
        });
      });

      // Buton Aktif Durumu
      $('.filter-button-group .btn').on('click', function() {
        $('.filter-button-group .btn').removeClass('active');
        $(this).addClass('active');
      });

      // Lightbox
      lightbox.option({
        resizeDuration: 200,
        wrapAround: true,
        disableScrolling: true
      });
    });
  </script>
</body>

</html>