<?php
session_start();

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['admin'])) {
    header("Location: adminlogin.php");
    exit();
}

// Veritabanı bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "innomis";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Fotoğraf silme işlemi
if (isset($_GET['delete_image_id'])) {
    $delete_image_id = $_GET['delete_image_id'];
    $delete_sql = "SELECT image_name FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $image_path = "assets/img/gallery/" . $image['image_name'];

    // Fotoğrafı sil
    if (unlink($image_path)) {
        $delete_sql = "DELETE FROM gallery WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_image_id);
        if ($stmt->execute()) {
            echo "Fotoğraf başarıyla silindi.";
        } else {
            echo "Fotoğraf silinemedi.";
        }
    } else {
        echo "Dosya silinemedi.";
    }
    $stmt->close();
}

// Mesajları getir
$sql = "SELECT id, name, email, subject, message FROM messages";
$result = $conn->query($sql);

// Fotoğraf yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $category = $_POST['category'];
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $target_dir = "assets/img/gallery/";

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($image_name);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['image']['type'], $allowed_types)) {
        if (move_uploaded_file($image_tmp, $target_file)) {
            $stmt = $conn->prepare("INSERT INTO gallery (image_name, category) VALUES (?, ?)");
            $stmt->bind_param("ss", $image_name, $category);

            if ($stmt->execute()) {
                echo "Fotoğraf başarıyla eklendi.";
            } else {
                echo "Veritabanına kaydederken hata oluştu.";
            }
            $stmt->close();
        } else {
            echo "Dosya yüklenemedi.";
        }
    } else {
        echo "Sadece resim dosyaları kabul edilir.";
    }
}

// Galerideki fotoğrafları getir
$gallery_sql = "SELECT id, image_name, category FROM gallery";
$gallery_result = $conn->query($gallery_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/img/fav.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --accent: #3b82f6;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            background: var(--dark-bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .layout-wrapper {
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--card-bg);
            min-height: 100vh;
            padding: 1.5rem;
            position: fixed;
        }

        .brand {
            padding: 0.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .logo-img {
            width: 220px;
            height: auto;
            max-width: 100%;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--accent);
            color: white;
        }

        .nav-link i {
            font-size: 1.25rem;
            width: 24px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        /* Upload Card */
        .upload-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            color: var(--text);
        }

        .form-control, .form-select {
            background: var(--dark-bg);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text);
            padding: 0.75rem;
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            background: var(--dark-bg);
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .gallery-item {
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
        }

        .gallery-image {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
        }

        .gallery-info {
            padding: 1rem;
        }

        .gallery-category {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        .btn-submit {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <img src="assets/img/ana logo.png" alt="Logo" class="logo-img">
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Üyeler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kurul.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kurul.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-tie"></i>
                        <span class="nav-text">Kurul Üyeleri</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="etkinlik.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'etkinlik.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="nav-text">Etkinlikler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="addphoto.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'addphoto.php' ? 'active' : ''; ?>">
                        <i class="fas fa-image"></i>
                        <span class="nav-text">Galeri</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="messages.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span class="nav-text">Mesajlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="yetkililer.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'yetkililer.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i>
                        <span class="nav-text">Yetkililer</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="finans.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'finans.php' ? 'active' : ''; ?>">
                        <i class="fas fa-wallet"></i>
                        <span class="nav-text">Finans</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-title mb-4">
                <h1>Galeri Yönetimi</h1>
                <p>Fotoğrafları yükle ve yönet</p>
            </div>

            <!-- Fotoğraf Yükleme Formu -->
            <div class="upload-card">
                <h5 class="mb-4">Yeni Fotoğraf Yükle</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category" required>
                            <option value="etkinlik">Etkinlikler</option>
                            <option value="workshop">Workshop'lar</option>
                            <option value="gezi">Geziler</option>
                            <option value="konferans">Konferanslar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fotoğraf Seç</label>
                        <input type="file" class="form-control" name="image" required>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-upload me-2"></i>Fotoğraf Yükle
                    </button>
                </form>
            </div>

            <!-- Galeri Grid -->
            <div class="upload-card">
                <h5 class="mb-4">Galerideki Fotoğraflar</h5>
                <div class="gallery-grid">
                    <?php if ($gallery_result->num_rows > 0): ?>
                        <?php while ($row = $gallery_result->fetch_assoc()): ?>
                            <div class="gallery-item">
                                <img src="assets/img/gallery/<?php echo $row['image_name']; ?>" 
                                     class="gallery-image" alt="Fotoğraf">
                                <div class="gallery-info">
                                    <div class="gallery-category">
                                        <i class="fas fa-folder me-2"></i><?php echo $row['category']; ?>
                                    </div>
                                    <a href="?delete_image_id=<?php echo $row['id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Bu fotoğrafı silmek istediğinize emin misiniz?')">
                                        <i class="fas fa-trash me-2"></i>Sil
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">Henüz fotoğraf yüklenmemiş.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
