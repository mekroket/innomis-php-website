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
  <!-- <link href="assets/vendor/aos/aos.css" rel="stylesheet"> -->
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

</head>

<body class="index-page">
  <?php
  $feedback_message = '';
  $message_class = '';
  $show_alert = false; // JavaScript alert göstermek için bir bayrak

  session_start();

  // Veritabanı bağlantısı
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "innomis";

  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Cihaz tespiti için fonksiyon
  function detectDevice()
  {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // Mobil cihazlar için kontrol
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
      return 'mobile';
    }

    // Tablet cihazlar için kontrol
    if (preg_match('/android|ipad|playbook|silk/i', $userAgent) && !preg_match('/(android.*mobile|android.*phone)/i', $userAgent)) {
      return 'tablet';
    }

    // Diğer tüm cihazları masaüstü olarak kabul et
    return 'desktop';
  }

  // Cihaz ziyaretini kaydet
  function recordDeviceVisit($conn)
  {
    if (!isset($_SESSION['device_recorded_today'])) {
      $device = detectDevice();
      $today = date('Y-m-d');

      // Bugün için kayıt var mı kontrol et
      $sql = "SELECT id FROM device_stats WHERE device_type = ? AND visit_date = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $device, $today);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        // Mevcut kaydı güncelle
        $sql = "UPDATE device_stats SET visitor_count = visitor_count + 1 WHERE device_type = ? AND visit_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $device, $today);
      } else {
        // Yeni kayıt oluştur
        $sql = "INSERT INTO device_stats (device_type, visit_date, visitor_count) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $device, $today);
      }

      $stmt->execute();
      $_SESSION['device_recorded_today'] = true;
      $stmt->close();
    }
  }

  // Ziyaretçi kaydından sonra cihaz kaydını da yap
  recordDeviceVisit($conn);

  // Session başlangıcında süreyi kaydet
  if (!isset($_SESSION['visit_start'])) {
    $_SESSION['visit_start'] = time();
  }

  // Sayfa her yüklendiğinde süreyi güncelle
  function recordVisitDuration($conn)
  {
    if (isset($_SESSION['visit_start'])) {
      $duration = round((time() - $_SESSION['visit_start']) / 60); // Dakika cinsinden

      if ($duration > 0) {
        $date = date('Y-m-d');
        $time = date('H:i:s');

        $sql = "INSERT INTO user_time_stats (visit_date, visit_time, duration_minutes) 
                      VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $date, $time, $duration);
        $stmt->execute();
        $stmt->close();

        // Yeni session başlangıcı
        $_SESSION['visit_start'] = time();
      }
    }
  }

  // Belirli aralıklarla süreyi kaydet
  if (isset($_SESSION['visit_start']) && (time() - $_SESSION['visit_start']) > 60) {
    recordVisitDuration($conn);
  }

  // Geri kalan kodlar...

  // İletişim formu işlemleri...
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
      $feedback_message = "Mesajınız Ulaştı En Kısa Sürede Geri Dönüş Sağlayacağız :)";
      $message_class = "sent-message";
      $show_alert = true;
    } else {
      $feedback_message = "Veritabanına kayıt sırasında hata oluştu.";
      $message_class = "error-message";
      $show_alert = true;
    }
    $stmt->close();
  }
  ?>






  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center me-auto me-lg-0">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="assets/img/ana logo.png" alt="">


      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Anasayfa<br></a></li>
          <li><a href="#about">Hakkımızda</a></li>
          <li><a href="galeri.php">Galeri</a></li>
          <li><a href="#team">Ekibimiz</a></li>

          <li><a href="#contact">İletişim</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="register.php">Üye Ol</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

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

    <!-- Etkinlikler -->
    <section>
      <?php
      // Yaklaşan etkinlikleri almak için
      $upcoming_sql = "SELECT * FROM events WHERE date >= NOW() ORDER BY date ASC LIMIT 1";
      $upcoming_result = $conn->query($upcoming_sql);

      // Geçmiş etkinlikleri almak için
      $past_sql = "SELECT * FROM events WHERE date < NOW() ORDER BY date DESC";
      $past_result = $conn->query($past_sql);

      $past_events = [];
      if ($past_result->num_rows > 0) {
        while ($row = $past_result->fetch_assoc()) {
          $past_events[] = $row;
        }
      }
      ?>

      <style>
        body {
          background-color: #f8f9fa;
        }

        .event-card {
          border-radius: 12px;
          overflow: hidden;
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
          margin-bottom: 15px;
          background: white;
          transition: transform 0.3s;
        }

        .event-card:hover {
          transform: scale(1.02);
        }

        .event-header img {
          width: 100%;
          height: 200px;
          object-fit: cover;
        }

        .event-logo {
          position: absolute;
          top: 15px;
          left: 15px;
          background: white;
          padding: 5px 10px;
          border-radius: 5px;
          font-weight: bold;
          font-size: 14px;
          color: #2c2c8c;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .event-body {
          padding: 15px;
        }

        .event-title {
          font-size: 1.3rem;
          font-weight: bold;
          margin-bottom: 10px;
        }

        .event-footer {
          font-size: 0.9rem;
          color: #666;
        }

        .more-button-container {
          text-align: center;
          margin-top: 20px;
        }

        .more-button {
          padding: 10px 20px;
          font-size: 1rem;
          background-color: #007bff;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          transition: background 0.3s;
        }

        .more-button:hover {
          background-color: #0056b3;
        }

        @media (max-width: 768px) {
          .event-title {
            font-size: 1.1rem;
          }

          .event-header img {
            height: 150px;
          }
        }
      </style>

      <div class="container mt-5">
        <h2 class="mb-4">Yaklaşan Etkinlikler</h2>
        <?php if ($upcoming_result->num_rows > 0): ?>
          <div class="row">
            <?php while ($row = $upcoming_result->fetch_assoc()): ?>
              <div class="col-md-4 col-sm-6 mb-4">
                <div class="card event-card">
                  <div class="event-header position-relative">
                    <img src="<?php echo $row['image']; ?>" alt="Etkinlik Resmi">
                    <div class="event-logo"><?php echo $row['title']; ?></div>
                  </div>
                  <div class="event-body">
                    <div class="event-title"><?php echo $row['title']; ?></div>
                    <div class="event-footer">
                      <p><i class="bi bi-calendar-event"></i> <?php echo date('d F Y H:i', strtotime($row['date'])); ?></p>
                      <p><i class="bi bi-geo-alt text-danger"></i> <?php echo $row['location']; ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p>Yaklaşan etkinlik bulunamadı!</p>
        <?php endif; ?>

        <h2 class="mt-5 mb-4">Geçmiş Etkinlikler</h2>
        <div class="row" id="past-events-container">
          <?php if (count($past_events) > 0): ?>
            <?php foreach ($past_events as $index => $row): ?>
              <div class="col-md-4 col-sm-6 mb-4 past-event-card <?php echo ($index >= 3) ? 'hidden-event' : ''; ?>">
                <div class="card event-card">
                  <div class="event-header position-relative">
                    <img src="<?php echo $row['image']; ?>" alt="Etkinlik Resmi">
                    <div class="event-logo"><?php echo $row['title']; ?></div>
                  </div>
                  <div class="event-body">
                    <div class="event-title"><?php echo $row['title']; ?></div>
                    <div class="event-footer">
                      <p><i class="bi bi-calendar-event"></i> <?php echo date('d F Y H:i', strtotime($row['date'])); ?></p>
                      <p><i class="bi bi-geo-alt text-danger"></i> <?php echo $row['location']; ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geçmiş etkinlik bulunamadı!</p>
          <?php endif; ?>
        </div>

        <?php if (count($past_events) > 3): ?>
          <div class="more-button-container">
            <button class="more-button" id="load-more">Daha Fazla Göster</button>
          </div>
        <?php endif; ?>
      </div>

      <script>
        const loadMoreBtn = document.getElementById('load-more');
        const pastEventsContainer = document.getElementById('past-events-container');
        let eventsDisplayed = 3;

        loadMoreBtn.addEventListener('click', function() {
          const allEvents = pastEventsContainer.children;
          const totalEvents = allEvents.length;

          for (let i = eventsDisplayed; i < eventsDisplayed + 3 && i < totalEvents; i++) {
            allEvents[i].style.display = 'block';
          }

          eventsDisplayed += 3;

          if (eventsDisplayed >= totalEvents) {
            loadMoreBtn.style.display = 'none';
          }
        });

        const allPastEvents = pastEventsContainer.children;
        for (let i = 3; i < allPastEvents.length; i++) {
          allPastEvents[i].style.display = 'none';
        }
      </script>

      <?php $conn->close(); ?>
    </section>









    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">
          <div class="col-lg-6 order-1 order-lg-2">
            <img src="assets/img/topluluk.JPG" class="img-fluid" alt="">
          </div>
          <div class="col-lg-6 order-2 order-lg-1 content">
            <h3>
              Yenilik ve Teknolojiye Adanmış Bir Topluluk</h3>
            <p class="fst-italic">
              İnovasyon ve Yönetim Bilişim Sistemleri Topluluğu olarak, geleceği şekillendiren yazılım, teknoloji ve inovasyon alanlarında fark yaratıyoruz. Öğrencilerimizi sektörün liderleriyle buluşturarak, kariyer yolculuklarına ilham kaynağı oluyoruz.
            </p>
            <ul>
              <li><i class="bi bi-check2-all"></i> <span>Eğitim ve Gelişim: Etkinliklerimizle yazılım ve teknolojide derinlemesine bilgi edinin.</span></li>
              <li><i class="bi bi-check2-all"></i> <span>İnovasyon Odaklılık: Yaratıcı fikirlerin hayata geçirilmesini destekliyoruz.</span></li>
              <li><i class="bi bi-check2-all"></i> <span>Kariyer Desteği: Sektörle güçlü bağlar kurmanıza olanak sağlıyoruz.</span></li>
            </ul>
            <p>
              MIS Topluluğu, gençlerin potansiyelini keşfetmesi ve yarınların liderleri olabilmesi için yanınızda!
            </p>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Sponsors Section -->
    <div class="sponsors-section">
      <div class="sponsors-container">
        <div class="sponsors-wrapper">
          <!-- İlk set -->
          <div class="sponsors-slide">
            <img src="assets/img/üni.png" alt="NEU" style="height:100px;">
            <img src="assets/img/kapsul.png" alt="Kapsul">
            <img src="assets/img/armiya.png" alt="AmericanLIFE">
            <img src="assets/img/life.png" alt="Armiya">
            <img src="assets/img/sia.png" alt="Konya">
          </div>
          <!-- Sonsuz döngü için tekrar -->
          <div class="sponsors-slide">
            <img src="assets/img/üni.png" alt="NEU" style="height:100px;">
            <img src="assets/img/kapsul.png" alt="Kapsul">
            <img src="assets/img/armiya.png" alt="AmericanLIFE">
            <img src="assets/img/life.png" alt="Armiya">
            <img src="assets/img/sia.png" alt="Konya">
          </div>
        </div>
      </div>
    </div>

    <style>
      .sponsors-section {
        padding: 20px 0;
        background: var(--dark-bg);
        overflow: hidden;
        width: 100%;
      }

      .sponsors-container {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 10px 0;
      }

      .sponsors-wrapper {
        display: flex;
        animation: slideshow 20s linear infinite;
        width: 200%;
      }

      .sponsors-slide {
        display: flex;
        align-items: center;
        justify-content: space-around;
        width: 50%;
        flex-shrink: 0;
      }

      .sponsors-slide img {
        height: 40px;
        width: auto;
        object-fit: contain;
        margin: 0 20px;
        transition: all 0.3s ease;
      }

      .sponsors-slide img:hover {
        transform: scale(1.1);
      }

      @keyframes slideshow {
        0% {
          transform: translateX(0);
        }

        100% {
          transform: translateX(-50%);
        }
      }

      .sponsors-wrapper:hover {
        animation-play-state: paused;
      }

      /* Responsive Tasarım */
      @media (max-width: 1200px) {
        .sponsors-slide img {
          height: 35px;
          margin: 0 15px;
        }
      }

      @media (max-width: 992px) {
        .sponsors-slide img {
          height: 30px;
          margin: 0 12px;
        }
      }

      @media (max-width: 768px) {
        .sponsors-section {
          padding: 15px 0;
        }
        
        .sponsors-container {
          padding: 8px 0;
        }
        
        .sponsors-slide img {
          height: 25px;
          margin: 0 10px;
        }
        
        .sponsors-wrapper {
          animation-duration: 15s; /* Mobilde daha hızlı kayma */
        }
      }

      @media (max-width: 576px) {
        .sponsors-slide img {
          height: 20px;
          margin: 0 8px;
        }
        
        .sponsors-wrapper {
          animation-duration: 12s;
        }
      }

      /* Çok küçük ekranlar için */
      @media (max-width: 400px) {
        .sponsors-slide img {
          height: 18px;
          margin: 0 6px;
        }
      }
    </style>

    <!-- Features Section -->
    <section id="features" class="features section">

      <div class="container">

        <div class="row gy-4">
          <div class="features-image col-lg-6" data-aos="fade-up" data-aos-delay="100"><img src="assets/img/toplu.jpg" alt=""></div>
          <div class="col-lg-6">

            <div class="features-item d-flex ps-0 ps-lg-3 pt-4 pt-lg-0" data-aos="fade-up" data-aos-delay="200">
              <i class="bi bi-archive flex-shrink-0"></i>
              <div>
                <h4>İnovasyona Adanmışlık</h4>
                <p>Sunduğumuz projeler ve etkinliklerle gençleri yazılım, teknoloji ve inovasyonla buluşturuyoruz.

                </p>
              </div>
            </div><!-- End Features Item-->

            <div class="features-item d-flex mt-5 ps-0 ps-lg-3" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-basket flex-shrink-0"></i>
              <div>
                <h4>Birlikte Daha Güçlüyüz</h4>
                <p>Potansiyelinizi keşfedin, yeni beceriler kazanın ve geleceğin liderleriyle aynı ortamda büyüyün.</p>
              </div>
            </div><!-- End Features Item-->

            <div class="features-item d-flex mt-5 ps-0 ps-lg-3" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-broadcast flex-shrink-0"></i>
              <div>
                <h4>Sınırları Aşan Vizyon</h4>
                <p>Sektörün en iyileriyle çalışarak, dünyayı değiştirecek projelere ilham veriyoruz.</p>
              </div>
            </div><!-- End Features Item-->

            <div class="features-item d-flex mt-5 ps-0 ps-lg-3" data-aos="fade-up" data-aos-delay="500">
              <i class="bi bi-camera-reels flex-shrink-0"></i>
              <div>
                <h4>Gelecek Bizimle</h4>
                <p>Her adımda gelişim, her projede başarı hedefiyle yol alıyoruz. Haydi, bize katılın ve yarınlara birlikte yön verelim!</p>
              </div>
            </div><!-- End Features Item-->

          </div>
        </div>

      </div>

    </section><!-- /Features Section -->



    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section dark-background">

      <img src="assets/img/call.JPG" alt="">

      <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
          <div class="col-xl-10">
            <div class="text-center">
              <h3>Topluluğa Üye Ol</h3>
              <p>Sende etkinliklerimize katılmak istiyorsan hemen Üye Ol</p>
              <a class="cta-btn" href="register.php">Topluluğa Üye Ol</a>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Call To Action Section -->



    <!-- Stats Section -->
    <section id="stats" class="stats section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 align-items-center justify-content-between">

          <div class="col-lg-5">
            <img src="assets/img/ot.jpg" alt="" class="img-fluid">
          </div>

          <div class="col-lg-6">

            <h3 class="fw-bold fs-2 mb-3">İnovasyon ve Başarıyla Yükselen Topluluk</h3>
            <p>
              Her geçen gün büyüyen güçlü bir aileye sahibiz. Öğrencilerimiz, yazılım ve teknoloji alanında fark yaratmayı hedefleyen bir topluluğun parçası olmanın ayrıcalığını yaşıyor. </p>

            <div class="row gy-4">

              <div class="col-lg-6">
                <div class="stats-item d-flex">
                  <i class="bi bi-emoji-smile flex-shrink-0"></i>
                  <div>
                    <span data-purecounter-start="0" data-purecounter-end="600" data-purecounter-duration="1" class="purecounter"></span>
                    <p><strong>Aşkın Üye Sayısı</span></p>
                  </div>
                </div>
              </div><!-- End Stats Item -->

              <div class="col-lg-6">
                <div class="stats-item d-flex">
                  <i class="bi bi-journal-richtext flex-shrink-0"></i>
                  <div>
                    <?php




                    // Veritabanı bağlantısı
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "innomis";

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                      die("Connection failed: " . $conn->connect_error);
                    }
                    // Aktif etkinlik sayısını getir
                    $events_sql = "SELECT COUNT(*) as total_events FROM events";
                    $events_result = $conn->query($events_sql);
                    $events_count = $events_result->fetch_assoc()['total_events'];

                    ?>
                    <span data-purecounter-start="0" data-purecounter-end="<?php echo $events_count; ?>" data-purecounter-duration="1" class="purecounter"></span>


                    <p><strong>Gerçekleştirilen Etkinlik</span></p>
                  </div>
                </div>
              </div><!-- End Stats Item -->

              <div class="col-lg-6">
                <div class="stats-item d-flex">
                  <i class="bi bi-headset flex-shrink-0"></i>
                  <div>
                    <span data-purecounter-start="0" data-purecounter-end="3" data-purecounter-duration="1" class="purecounter"></span>
                    <p><strong>Konferans</span></p>
                  </div>
                </div>
              </div><!-- End Stats Item -->

              <div class="col-lg-6">
                <div class="stats-item d-flex">
                  <i class="bi bi-people flex-shrink-0"></i>
                  <div>
                    <span data-purecounter-start="0" data-purecounter-end="450000" data-purecounter-duration="1" class="purecounter"></span>
                    <p><strong>Sosyal Medyada Erişilen İnsan Sayısı</span></p>
                  </div>
                </div>
              </div><!-- End Stats Item -->

            </div>

          </div>

        </div>

      </div>

    </section><!-- /Stats Section -->



    <!-- Team Section -->
    <section id="team" class="team section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Takım</h2>
        <p>Yönetim Takımımız</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
            <div class="team-member">
              <div class="member-img">
                <img src="assets/img/resim/arda.png" class="img-fluid" alt="">
                <div class="social">
                  <a href="https://www.instagram.com/ardaozxr/"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.linkedin.com/in/ardaaozerr/"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              <div class="member-info">
                <h4>Arda ÖZER</h4>
                <span>Topluluk Başkanı</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
            <div class="team-member">
              <div class="member-img">
                <img src="assets/img/resim/oguz.png" class="img-fluid" alt="">
                <div class="social">

                  <a href="https://www.instagram.com/oguz.kaan.ekin/"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.linkedin.com/in/oguz-kaan/"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              <div class="member-info">
                <h4>Oğuz Kaan Ekin</h4>
                <span>Başkan Yardımcısı</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300">
            <div class="team-member">
              <div class="member-img">
                <img src="assets/img/resim/furkan.png" class="img-fluid" alt="">
                <div class="social">
                  <a href="https://www.instagram.com/furkan_demirbas00/"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.linkedin.com/in/furkan-utkay-demirbas/"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              <div class="member-info">
                <h4>Furkan Utkay DEMİRBAŞ</h4>
                <span>Genel Direktör</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
            <div class="team-member">
              <div class="member-img">
                <img src="assets/img/resim/mustafa.png" class="img-fluid" alt="">
                <div class="social">
                  <a href="https://www.instagram.com/mus.tafa.kara/"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.linkedin.com/in/-mustafakara/"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              <div class="member-info">
                <h4>Mustafa KARA</h4>
                <span>Organizasyon ve Planlama Direktörü</span>
              </div>
            </div>
          </div><!-- End Team Member -->

        </div>

      </div>

    </section><!-- /Team Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>İletişm</h2>
        <p>Mesaj</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="mb-4" data-aos="fade-up" data-aos-delay="200">
          <iframe style="border:0; width: 100%; height: 270px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3149.780135773454!2d32.419348600000006!3d37.8654346!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14d085aa2f555bd3%3A0x6bbc509a49c5549d!2sNecmettin%20Erbakan%20%C3%9Cniversitesi%20Uygulamal%C4%B1%20Bilimler%20Fak%C3%BCltesi!5e0!3m2!1str!2str!4v1737563791240!5m2!1str!2str" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div><!-- End Google Maps -->

        <div class="row gy-4">

          <div class="col-lg-4">
            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-geo-alt flex-shrink-0"></i>
              <div>
                <h3>Adres</h3>
                <p> Köyceğiz, Dere Aşıklar, Köyceğiz Yerleşkesi Köyceğiz, Demeç Sk. No:42/3, 42005 Meram/Konya</p>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-telephone flex-shrink-0"></i>
              <div>
                <h3>Bize Ulaş</h3>
                <p>+90 552 706 36 20</p>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
              <i class="bi bi-envelope flex-shrink-0"></i>
              <div>
                <h3>E-posta Adresimiz</h3>
                <p>bilgi@innomis.tr</p>
              </div>
            </div><!-- End Info Item -->

          </div>

          <!-- Preloader -->

          <div class="col-lg-8">
            <form id="contactForm" action="" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">
                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Adınız ve Soyadınız" required>
                </div>

                <div class="col-md-6">
                  <input type="email" class="form-control" name="email" placeholder="Eposta" required>
                </div>

                <div class="col-md-12">
                  <input type="text" class="form-control" name="subject" placeholder="Konu" required>
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Mesaj" required></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Yükleniyor...</div>
                  <div class="<?php echo $message_class; ?>"><?php echo $feedback_message; ?></div>
                  <button type="submit">Gönder</button>
                </div>
              </div>
            </form>
          </div>







        </div>

      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer dark-background">

    <div class="footer-top">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 col-md-6 footer-about">
            <a href="index.php" class="logo d-flex align-items-center">
              <img src="assets/img/ana logo.png" alt="">

            </a>
            <div class="footer-contact pt-3">
              <p>Necmettin Erbakan Üniversitesi</p>
              <p>Uygulamalı Bilimler Fakültesi</p>
              <p class="mt-3"><strong>Phone:</strong> <span>+90 552 706 36 20</span></p>
              <p><strong>Email:</strong> <span>bilgi@innomis.tr</span></p>
            </div>
            <div class="social-links d-flex mt-4">
              <a href="https://x.com/neuinnomis"><i class="bi bi-twitter-x"></i></a>
              <a href="https://www.instagram.com/neuinnomis/"><i class="bi bi-instagram"></i></a>
              <a href="https://www.linkedin.com/in/inovasyon-ve-y%C3%B6netim-bili%C5%9Fim-sistemleri-toplulugu/"><i class="bi bi-linkedin"></i></a>
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
            <a href="adminlogin.php" class="admin-panel-btn">
              <i class="bi bi-shield-lock"></i>
              Yönetici Girişi
            </a>
          </div>
        </div>
      </div>

      <div class="copyright">
        <div class="container text-center">
          <p>© <span>Copyright</span> <strong class="px-1 sitename">INNOMIS</strong> <span>Tüm Hakları Saklıdır</span></p>
          <div class="credits">
            <!-- All the links in the footer should remain intact. -->
            <!-- You can delete the links only if you've purchased the pro version. -->
            <!-- Licensing information: https://bootstrapmade.com/license/ -->
            <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
            <a href="https://www.linkedin.com/in/oguz-kaan/">powered by oguzkaanekin</a>
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
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      new PureCounter();
    });
  </script>

  <script src="assets/js/main.js"></script>

  <!-- Modal HTML -->
  <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="feedbackModalLabel">Bilgi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php echo $feedback_message; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  // PHP tarafında Modal tetikleme
  if ($show_alert) {
    echo "<script>
            var myModal = new bootstrap.Modal(document.getElementById('feedbackModal'), {});
            myModal.show();
          </script>";
  }
  ?>

  <style>
    .admin-panel-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.2);
      border-radius: 6px;
      color: #fff;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .admin-panel-btn:hover {
      background: rgba(59, 130, 246, 0.2);
      border-color: rgba(59, 130, 246, 0.3);
      color: #3b82f6;
      transform: translateY(-1px);
    }

    .admin-panel-btn i {
      font-size: 1.1rem;
    }
  </style>

</body>

</html>