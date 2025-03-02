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

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Etkinlik ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    
    // Resim dosyasının yüklenmesi
    $target_dir = "assets/img/gallery/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    
    // Dosya tipi kontrolü
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['image']['type'], $allowed_types)) { // Burada fazladan parantez kaldırıldı
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Veritabanına kaydetme
            $sql = "INSERT INTO events (title, date, location, image) VALUES ('$title', '$date', '$location', '$target_file')";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success'>Yeni etkinlik başarıyla eklendi!</div>";
            } else {
                echo "<div class='alert alert-danger'>Hata: " . $sql . "<br>" . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Dosya yüklenemedi.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Sadece resim dosyaları kabul edilir.</div>";
    }
}

// Etkinlik silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM events WHERE id = $delete_id";
    if ($conn->query($delete_sql)) {
        echo "<div class='alert alert-success'>Etkinlik başarıyla silindi.</div>";
    } else {
        echo "<div class='alert alert-danger'>Hata: " . $conn->error . "</div>";
    }
}

// Etkinlikleri getir
$events_sql = "SELECT id, title, date, location, image FROM events";
$events_result = $conn->query($events_sql);

// Etkinlik düzenleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_event'])) {
    $id = $_POST['event_id'];
    $title = $_POST['edit_title'];
    $date = $_POST['edit_date'];
    $location = $_POST['edit_location'];
    
    // Yeni resim yüklendi mi kontrol et
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['size'] > 0) {
        $target_dir = "assets/img/gallery/";
        $target_file = $target_dir . basename($_FILES["edit_image"]["name"]);
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['edit_image']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
                $update_sql = "UPDATE events SET title=?, date=?, location=?, image=? WHERE id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ssssi", $title, $date, $location, $target_file, $id);
            }
        }
    } else {
        // Resim güncellenmedi, diğer bilgileri güncelle
        $update_sql = "UPDATE events SET title=?, date=?, location=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssi", $title, $date, $location, $id);
    }

    if ($stmt->execute()) {
        echo "<script>
                alert('Etkinlik başarıyla güncellendi!');
                window.location.href = 'etkinlik.php';
              </script>";
    } else {
        echo "<script>alert('Güncelleme başarısız oldu!');</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Favicons -->
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
            transition: all 0.3s ease;
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

        .nav-item {
            margin-bottom: 0.5rem;
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

        /* Form Card */
        .event-form-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: var(--dark-bg);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text);
            padding: 0.75rem;
            border-radius: 8px;
        }

        .form-control:focus {
            background: var(--dark-bg);
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        /* Event List Card */
        .event-list-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .event-title {
            font-size: 1.25rem;
            color: var(--text);
            font-weight: 500;
        }

        .event-actions {
            display: flex;
            gap: 0.75rem;
        }

        .table {
            color: var(--text);
            border-collapse: separate;
            border-spacing: 0 0.75rem;
            margin: 0;
        }

        .table th {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: none;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1.25rem 1rem;
            background: rgba(255, 255, 255, 0.02);
            border: none;
            vertical-align: middle;
        }

        .table tr:hover td {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-1px);
            transition: all 0.3s ease;
        }

        .table tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .event-image {
            width: 120px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .event-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .event-name {
            font-weight: 500;
            color: var(--text);
            font-size: 1rem;
        }

        .event-date {
            color: var(--text-muted);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-location {
            color: var(--text-muted);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .btn-edit:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        /* Modal stilleri */
        .modal-content {
            background: var(--card-bg);
            color: var(--text);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            color: var(--text);
        }

        /* Form elemanları için stiller */
        .modal .form-control {
            background: var(--dark-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text);
        }

        .modal .form-control:focus {
            background: var(--dark-bg);
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        .modal .form-label {
            color: var(--text);
        }

        /* Modal butonları */
        .modal .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text);
        }

        .modal .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text);
        }

        .modal .btn-primary {
            background: var(--accent);
            border: none;
        }

        .modal .btn-primary:hover {
            background: var(--accent);
            opacity: 0.9;
        }

        /* Kapatma butonu */
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Input file özel stili */
        .modal input[type="file"] {
            background: var(--dark-bg);
            color: var(--text);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .modal input[type="file"]::-webkit-file-upload-button {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-right: 1rem;
            cursor: pointer;
        }

        .modal input[type="file"]::-webkit-file-upload-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Datetime input özel stili */
        .modal input[type="datetime-local"] {
            background: var(--dark-bg);
            color: var(--text);
        }

        .modal input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
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
            <div class="top-bar">
                <div class="page-title">
                    <h1>Etkinlik Yönetimi</h1>
                    <p>Etkinlikleri ekle ve yönet</p>
                </div>
            </div>

            <!-- Etkinlik Ekleme Formu -->
            <div class="event-form-card">
                <h5 class="mb-4">Yeni Etkinlik Ekle</h5>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Etkinlik Başlığı</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarih ve Saat</label>
                            <input type="datetime-local" class="form-control" name="date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Yer</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Etkinlik Resmi</label>
                            <input type="file" class="form-control" name="image" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success" style="color: white;">
                        <i class="fas fa-plus me-2"></i>Etkinlik Ekle
                    </button>
                </form>
            </div>

            <!-- Etkinlik Listesi -->
            <div class="event-list-card">
                <div class="event-header">
                    <h2 class="event-title">Etkinlik Listesi</h2>
                    <div class="event-actions">
                        <button class="btn-action btn-edit" title="Excel'e Aktar">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button class="btn-action btn-edit" title="PDF'e Aktar">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 60px ; color: white;">#</th>
                                <th style="width: 140px">Görsel</th>
                                <th>Etkinlik Detayları</th>
                                <th style="width: 120px">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($events_result->num_rows > 0): ?>
                                <?php while ($row = $events_result->fetch_assoc()): ?>
                                    <tr>
                                        <td style="color: white;"><?= htmlspecialchars($row['id']) ?></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($row['image']) ?>" 
                                                 alt="Etkinlik" 
                                                 class="event-image">
                                        </td>
                                        <td>
                                            <div class="event-info">
                                                <div class="event-name"><?= htmlspecialchars($row['title']) ?></div>
                                                <div class="event-date">
                                                    <i class="far fa-calendar-alt"></i>
                                                    <?= date('d.m.Y H:i', strtotime($row['date'])) ?>
                                                </div>
                                                <div class="event-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars($row['location']) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit" 
                                                        onclick='editEvent(<?= json_encode($row) ?>)' 
                                                        title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?delete_id=<?= $row['id'] ?>" 
                                                   class="btn-action btn-delete" 
                                                   onclick="return confirm('Bu etkinliği silmek istediğinize emin misiniz?')"
                                                   title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Henüz etkinlik bulunmamaktadır.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Etkinlik Düzenle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="event_id" id="edit_event_id">
                        <div class="mb-3">
                            <label class="form-label">Etkinlik Başlığı</label>
                            <input type="text" class="form-control" name="edit_title" id="edit_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tarih ve Saat</label>
                            <input type="datetime-local" class="form-control" name="edit_date" id="edit_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yer</label>
                            <input type="text" class="form-control" name="edit_location" id="edit_location" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mevcut Resim</label>
                            <img id="current_image" src="" alt="Mevcut Resim" style="max-width: 200px; display: block; margin-bottom: 10px;">
                            <label class="form-label">Yeni Resim (Opsiyonel)</label>
                            <input type="file" class="form-control" name="edit_image" id="edit_image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="edit_event" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editEvent(event) {
        document.getElementById('edit_event_id').value = event.id;
        document.getElementById('edit_title').value = event.title;
        document.getElementById('edit_location').value = event.location;
        
        // Tarihi datetime-local formatına çevir
        const eventDate = new Date(event.date);
        const formattedDate = eventDate.toISOString().slice(0, 16);
        document.getElementById('edit_date').value = formattedDate;
        
        // Mevcut resmi göster
        document.getElementById('current_image').src = event.image;
        
        // Modal'ı göster
        var editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
        editModal.show();
    }
    </script>
</body>
</html>