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
    die("Connection failed: " . $conn->connect_error);
}

// Yetkili ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_authority'])) {
    $name = $_POST['name'];
    $student_number = $_POST['student_number'];
    $department = $_POST['department'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Resim yükleme
    $target_dir = "assets/img/authorities/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    } else {
        $target_file = "assets/img/default-user.png";
    }

    $stmt = $conn->prepare("INSERT INTO authorities (name, student_number, department, phone, email, role, password, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $student_number, $department, $phone, $email, $role, $password, $target_file);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Yetkili başarıyla eklendi.</div>";
    } else {
        echo "<div class='alert alert-danger'>Hata: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Yetkili silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Resmi sil
    $sql = "SELECT image FROM authorities WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['image'] != "assets/img/default-user.png" && file_exists($row['image'])) {
            unlink($row['image']);
        }
    }
    
    // Kaydı sil
    $sql = "DELETE FROM authorities WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Yetkili başarıyla silindi.</div>";
    } else {
        echo "<div class='alert alert-danger'>Hata: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Yetkili düzenleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_authority'])) {
    $id = $_POST['authority_id'];
    $name = $_POST['edit_name'];
    $student_number = $_POST['edit_student_number'];
    $department = $_POST['edit_department'];
    $phone = $_POST['edit_phone'];
    $email = $_POST['edit_email'];
    $role = $_POST['edit_role'];
    
    $sql = "UPDATE authorities SET name=?, student_number=?, department=?, phone=?, email=?, role=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $name, $student_number, $department, $phone, $email, $role, $id);
    
    if ($stmt->execute()) {
        // Şifre değişikliği kontrolü
        if (!empty($_POST['edit_password'])) {
            $password = password_hash($_POST['edit_password'], PASSWORD_DEFAULT);
            $sql = "UPDATE authorities SET password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $password, $id);
            $stmt->execute();
        }
        
        // Resim değişikliği kontrolü
        if (isset($_FILES["edit_image"]) && $_FILES["edit_image"]["error"] == 0) {
            $target_dir = "assets/img/authorities/";
            $target_file = $target_dir . basename($_FILES["edit_image"]["name"]);
            
            if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
                $sql = "UPDATE authorities SET image=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $target_file, $id);
                $stmt->execute();
            }
        }
        
        echo "<div class='alert alert-success'>Yetkili başarıyla güncellendi.</div>";
    } else {
        echo "<div class='alert alert-danger'>Hata: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Yetkilileri getir
$sql = "SELECT * FROM authorities ORDER BY created_at DESC";
$authorities = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yetkililer - Admin Panel</title>
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

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-size: 1.75rem;
            margin: 0;
        }

        .page-title p {
            color: var(--text-muted);
            margin: 0;
        }

        /* Authority Card Styles */
        .authority-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .authority-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .authority-item {
            background: var(--dark-bg);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .authority-item:hover {
            transform: translateY(-5px);
        }

        .authority-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .authority-info {
            padding: 1.5rem;
        }

        .authority-name {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .authority-role {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent);
            border-radius: 20px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .authority-details {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .authority-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        /* Form Styles */
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

        .form-label {
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--accent);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-warning {
            background: var(--warning);
            border: none;
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            border: none;
        }

        /* Modal Styles */
        .modal-content {
            background: var(--card-bg);
            color: var(--text);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                padding: 1rem;
            }
            
            .logo-img {
                width: 60px;
            }
            
            .nav-text {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .authority-grid {
                grid-template-columns: 1fr;
            }
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
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1>Yetkililer</h1>
                    <p>Yetkili kullanıcıları yönet</p>
                </div>
            </div>

            <!-- Yetkili Ekleme Formu -->
            <div class="authority-card">
                <h5 class="mb-4">Yeni Yetkili Ekle</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Okul Numarası</label>
                            <input type="text" class="form-control" name="student_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bölüm</label>
                            <input type="text" class="form-control" name="department" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Yetki</label>
                            <select class="form-select" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderatör</option>
                                <option value="editor">Editör</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profil Fotoğrafı</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                    <button type="submit" name="add_authority" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Yetkili Ekle
                    </button>
                </form>
            </div>

            <!-- Yetkililer Listesi -->
            <div class="authority-card">
                <h5 class="mb-4">Yetkililer Listesi</h5>
                <div class="authority-grid">
                    <?php if ($authorities->num_rows > 0): ?>
                        <?php while($row = $authorities->fetch_assoc()): ?>
                            <div class="authority-item">
                                <img src="<?= htmlspecialchars($row['image']) ?>" 
                                     alt="<?= htmlspecialchars($row['name']) ?>" 
                                     class="authority-image">
                                <div class="authority-info">
                                    <div class="authority-name"><?= htmlspecialchars($row['name']) ?></div>
                                    <div class="authority-role"><?= htmlspecialchars($row['role']) ?></div>
                                    <div class="authority-details">
                                        <i class="fas fa-id-card me-2"></i><?= htmlspecialchars($row['student_number']) ?>
                                    </div>
                                    <div class="authority-details">
                                        <i class="fas fa-graduation-cap me-2"></i><?= htmlspecialchars($row['department']) ?>
                                    </div>
                                    <div class="authority-details">
                                        <i class="fas fa-phone me-2"></i><?= htmlspecialchars($row['phone']) ?>
                                    </div>
                                    <div class="authority-details">
                                        <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($row['email']) ?>
                                    </div>
                                    <div class="authority-actions">
                                        <button class="btn btn-warning btn-sm" 
                                                onclick="editAuthority(<?= htmlspecialchars(json_encode($row)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete_id=<?= $row['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bu yetkiliyi silmek istediğinize emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">Henüz yetkili bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Yetkili Düzenle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="authority_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" name="edit_name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Okul Numarası</label>
                            <input type="text" class="form-control" name="edit_student_number" id="edit_student_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bölüm</label>
                            <input type="text" class="form-control" name="edit_department" id="edit_department" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" class="form-control" name="edit_phone" id="edit_phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="edit_email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yetki</label>
                            <select class="form-select" name="edit_role" id="edit_role" required>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderatör</option>
                                <option value="editor">Editör</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yeni Şifre (Boş bırakılabilir)</label>
                            <input type="password" class="form-control" name="edit_password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yeni Profil Fotoğrafı (Opsiyonel)</label>
                            <input type="file" class="form-control" name="edit_image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="edit_authority" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editAuthority(authority) {
        document.getElementById('edit_id').value = authority.id;
        document.getElementById('edit_name').value = authority.name;
        document.getElementById('edit_student_number').value = authority.student_number;
        document.getElementById('edit_department').value = authority.department;
        document.getElementById('edit_phone').value = authority.phone;
        document.getElementById('edit_email').value = authority.email;
        document.getElementById('edit_role').value = authority.role;
        
        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }
    </script>
</body>
</html> 