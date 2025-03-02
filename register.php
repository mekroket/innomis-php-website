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
            <a href="index.php" class="logo d-flex align-items-center me-auto me-lg-0">
                <img src="assets/img/ana logo.png" alt="">
            </a>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php" class="active">Anasayfa<br></a></li>
                    <li><a href="index.php">Hakkımızda</a></li>
                    <li><a href="galeri.php">Galeri</a></li>
                    <li><a href="index.php">Ekibimiz</a></li>
                    <li><a href="index.php">İletişim</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <a class="btn-getstarted" href="register.php">Üye Ol</a>
        </div>
    </header>


    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "innomis";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $successMessage = ''; // Success message variable

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fullName = $_POST['fullName'];
        $faculty = $_POST['faculty'];
        $class = $_POST['class'];
        $studentNumber = $_POST['studentNumber'];
        $department = $_POST['department'];
        $address = $_POST['address'];
        $contactNumber = $_POST['contactNumber'];
        $email = $_POST['email'];

        $sql = "INSERT INTO registrations (fullName, faculty, class, studentNumber, department, address, contactNumber, email)
            VALUES ('$fullName', '$faculty', '$class', '$studentNumber', '$department', '$address', '$contactNumber', '$email')";

        if ($conn->query($sql) === TRUE) {
            $successMessage = "Kayıt başarılı!"; // Set success message if registration is successful
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>

    <!-- HTML Code -->
    <section class="bg-light p-3 p-md-4 p-xl-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xxl-11">
                    <div class="card border-light-subtle shadow-sm">
                        <div class="row g-0">
                            <div class="col-12 col-md-6">
                                <img class="img-fluid rounded-start w-100 h-100 object-fit-cover" loading="lazy" src="assets/img/kayıt.JPG" alt="Welcome back you've been missed!">
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                                <div class="col-12 col-lg-11 col-xl-10">
                                    <div class="card-body p-3 p-md-4 p-xl-5">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-5">
                                                    <div class="text-center mb-4">
                                                        <a href="#!"></a>
                                                    </div>
                                                    <h2 class="h4 text-center">INNOMIS'e Üye OL</h2>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex gap-3 flex-column">
                                                    <p class="text-center mt-4 mb-5">Lütfen Gerekli Bilgileri Giriniz</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Success Message Alert -->
                                        <?php if ($successMessage) : ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Başarıyla kaydoldunuz!</strong> <?= $successMessage ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form action="register.php" method="POST">
                                            <div class="row gy-3 overflow-hidden">
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="fullName" id="fullName" placeholder="Adı Soyadı" required>
                                                        <label for="fullName" class="form-label">Adı Soyadı</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="faculty" id="faculty" placeholder="Fakülte/Yüksekokul" required>
                                                        <label for="faculty" class="form-label">Fakülte/Yüksekokul</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="class" id="class" placeholder="Sınıf" required>
                                                        <label for="class" class="form-label">Sınıf</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="studentNumber" id="studentNumber" placeholder="Öğrenci Numarası" required>
                                                        <label for="studentNumber" class="form-label">Öğrenci Numarası</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="department" id="department" placeholder="Bölümü" required>
                                                        <label for="department" class="form-label">Bölümü</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" name="address" id="address" placeholder="İkamet Adresi" required>
                                                        <label for="address" class="form-label">İkamet Adresi</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="tel" class="form-control" name="contactNumber" id="contactNumber" placeholder="İrtibat Telefonu" required>
                                                        <label for="contactNumber" class="form-label">İrtibat Telefonu</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="email" class="form-control" name="email" id="email" placeholder="E-Posta" required>
                                                        <label for="email" class="form-label">E-Posta</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" name="iAgree" id="iAgree" required>
                                                        <label class="form-check-label text-secondary" for="iAgree">
                                                            <a href="#!" class="link-primary text-decoration-none">Üye Olma koşullarını </a> kabul ediyorum.
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-grid">
                                                        <button class="btn btn-dark btn-lg" type="submit">Üye Ol</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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


</body>

</html>