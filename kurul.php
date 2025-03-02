<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['admin'])) {
    header("Location: adminlogin.php");
    exit();
}

// Veritabanı bağlantısı
require_once 'db_connection.php';

// Başarı ve hata mesajları için değişkenler
$success_message = '';
$error_message = '';

// Üye ekleme işlemi
if (isset($_POST['add_member'])) {
    try {
        $name = $_POST['name'];
        $position = $_POST['position'];
        $student_number = $_POST['student_number'];
        $department = $_POST['department'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $linkedin = $_POST['linkedin'] ?? '';
        
        $image = "";
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "assets/img/team/";
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            }
        }

        $sql = "INSERT INTO team_members (name, position, student_number, department, phone, email, linkedin, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $position, $student_number, $department, $phone, $email, $linkedin, $image);
        
        if ($stmt->execute()) {
            $success_message = "Üye başarıyla eklendi!";
        } else {
            throw new Exception("Veritabanı hatası: " . $stmt->error);
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Üye silme işlemi
if (isset($_POST['delete_member'])) {
    try {
        $id = $_POST['member_id'];
        
        // Önce mevcut resmi al
        $sql = "SELECT image FROM team_members WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        
        // Üyeyi sil
        $sql = "DELETE FROM team_members WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Varsayılan resim değilse dosyayı sil
            if ($member['image'] != "assets/img/team/default.jpg" && file_exists($member['image'])) {
                unlink($member['image']);
            }
            $success_message = "Üye başarıyla silindi!";
        } else {
            throw new Exception("Veritabanı hatası: " . $stmt->error);
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Üye güncelleme işlemi
if (isset($_POST['update_member'])) {
    try {
        $id = $_POST['member_id'];
        $name = $_POST['edit_name'];
        $position = $_POST['edit_position'];
        $student_number = $_POST['edit_student_number'];
        $department = $_POST['edit_department'];
        $phone = $_POST['edit_phone'];
        $email = $_POST['edit_email'];
        $linkedin = $_POST['edit_linkedin'] ?? '';
        
        // Resim yükleme işlemi
        if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
            $target_dir = "assets/img/team/";
            $file_extension = strtolower(pathinfo($_FILES["edit_image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Dosya türü kontrolü
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (in_array($file_extension, $allowed_types)) {
                if (move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file)) {
                    // Eski resmi sil
                    $sql = "SELECT image FROM team_members WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $member = $result->fetch_assoc();
                    
                    if (!empty($member['image']) && file_exists($member['image'])) {
                        unlink($member['image']);
                    }
                    
                    // Yeni resimle güncelle
                    $sql = "UPDATE team_members SET name=?, position=?, student_number=?, department=?, phone=?, email=?, linkedin=?, image=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssssi", $name, $position, $student_number, $department, $phone, $email, $linkedin, $target_file, $id);
                }
            }
        } else {
            // Resim güncellenmedi, diğer bilgileri güncelle
            $sql = "UPDATE team_members SET name=?, position=?, student_number=?, department=?, phone=?, email=?, linkedin=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $name, $position, $student_number, $department, $phone, $email, $linkedin, $id);
        }
        
        if ($stmt->execute()) {
            $success_message = "Üye başarıyla güncellendi!";
        } else {
            throw new Exception("Veritabanı hatası: " . $stmt->error);
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Error in update_member: " . $e->getMessage());
    }
}

// Tüm üyeleri getir
$sql = "SELECT * FROM team_members ORDER BY 
    CASE 
        WHEN position = 'Başkan' THEN 1
        WHEN position = 'Başkan Yardımcısı' THEN 2
        WHEN position = 'Genel Direktör' THEN 3
        WHEN position = 'Organizasyon ve Planlama Direktörü' THEN 4
        WHEN position = 'İnsan Kaynakları Direktörü' THEN 5
        WHEN position = 'Finans Direktörü' THEN 6
        WHEN position = 'Sosyal Medya Direktörü' THEN 7
        ELSE 8
    END, created_at DESC";
$result = $conn->query($sql);

// Admin bilgilerini al
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT name, image, role FROM authorities WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin = $admin_result->fetch_assoc();

// Rol ismini Türkçeleştir
$role_names = [
    'admin' => 'Yönetici',
    'moderator' => 'Moderatör',
    'editor' => 'Editör'
];
$role_display = $role_names[$admin['role']] ?? $admin['role'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurul Üyeleri - Admin Panel</title>
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
                    <h1>Kurul Üyeleri</h1>
                    <p>Kurul üyelerini yönet</p>
                </div>
            </div>

            <!-- Kurul Üyesi Ekleme Formu -->
            <div class="authority-card">
                <h5 class="mb-4">Yeni Kurul Üyesi Ekle</h5>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ad Soyad</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pozisyon</label>
                                <select class="form-select" name="position" required>
                                    <option value="Başkan">Başkan</option>
                                    <option value="Başkan Yardımcısı">Başkan Yardımcısı</option>
                                    <option value="Genel Direktör">Genel Direktör</option>
                                    <option value="Organizasyon ve Planlama Direktörü">Organizasyon ve Planlama Direktörü</option>
                                    <option value="İnsan Kaynakları Direktörü">İnsan Kaynakları Direktörü</option>
                                    <option value="Finans Direktörü">Finans Direktörü</option>
                                    <option value="Sosyal Medya Direktörü">Sosyal Medya Direktörü</option>
                                    <option value="Genel Kurul Üyesi">Genel Kurul Üyesi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Okul Numarası</label>
                                <input type="text" name="student_number" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bölüm</label>
                                <input type="text" name="department" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Telefon</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>E-posta</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>LinkedIn</label>
                                <input type="url" name="linkedin" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profil Fotoğrafı</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_member" class="btn btn-primary">Üye Ekle</button>
                </form>
            </div>

            <!-- Kurul Üyeleri Listesi -->
            <div class="authority-card">
                <h5 class="mb-4">Kurul Üyeleri Listesi</h5>
                <div class="authority-grid">
                    <?php while($member = $result->fetch_assoc()): ?>
                        <div class="authority-item">
                            <img src="<?= htmlspecialchars($member['image']) ?>" 
                                 alt="<?= htmlspecialchars($member['name']) ?>" 
                                 class="authority-image"
                                 onerror="this.src='assets/img/team/default.jpg'">
                            <div class="authority-info">
                                <div class="authority-name"><?= htmlspecialchars($member['name']) ?></div>
                                <div class="authority-role"><?= htmlspecialchars($member['position']) ?></div>
                                <div class="authority-details">
                                    <i class="fas fa-id-card me-2"></i><?= htmlspecialchars($member['student_number']) ?>
                                </div>
                                <div class="authority-details">
                                    <i class="fas fa-graduation-cap me-2"></i><?= htmlspecialchars($member['department']) ?>
                                </div>
                                <div class="authority-details">
                                    <i class="fas fa-phone me-2"></i><?= htmlspecialchars($member['phone']) ?>
                                </div>
                                <div class="authority-details">
                                    <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($member['email']) ?>
                                </div>
                                <?php if($member['linkedin']): ?>
                                <div class="authority-details">
                                    <i class="fab fa-linkedin me-2"></i>
                                    <a href="<?= htmlspecialchars($member['linkedin']) ?>" target="_blank" class="text-light">LinkedIn</a>
                                </div>
                                <?php endif; ?>
                                <div class="authority-actions">
                                    <button class="btn btn-warning btn-sm" 
                                            onclick="editMember(<?= htmlspecialchars(json_encode($member)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                        <button type="submit" name="delete_member" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Bu üyeyi silmek istediğinize emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Üye Düzenle</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="member_id" id="edit_id">
                        <div class="form-group">
                            <label>Ad Soyad</label>
                            <input type="text" name="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Pozisyon</label>
                            <select class="form-select" name="edit_position" required>
                                <option value="Başkan">Başkan</option>
                                <option value="Başkan Yardımcısı">Başkan Yardımcısı</option>
                                <option value="Genel Direktör">Genel Direktör</option>
                                <option value="Organizasyon ve Planlama Direktörü">Organizasyon ve Planlama Direktörü</option>
                                <option value="İnsan Kaynakları Direktörü">İnsan Kaynakları Direktörü</option>
                                <option value="Finans Direktörü">Finans Direktörü</option>
                                <option value="Sosyal Medya Direktörü">Sosyal Medya Direktörü</option>
                                <option value="Genel Kurul Üyesi">Genel Kurul Üyesi</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Okul Numarası</label>
                            <input type="text" name="edit_student_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Bölüm</label>
                            <input type="text" name="edit_department" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="tel" name="edit_phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>E-posta</label>
                            <input type="email" name="edit_email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>LinkedIn</label>
                            <input type="url" name="edit_linkedin" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Yeni Profil Fotoğrafı (Opsiyonel)</label>
                            <input type="file" name="edit_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" name="update_member" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editMember(member) {
        document.getElementById('edit_id').value = member.id;
        document.getElementById('edit_name').value = member.name;
        document.getElementById('edit_position').value = member.position;
        document.getElementById('edit_student_number').value = member.student_number;
        document.getElementById('edit_department').value = member.department;
        document.getElementById('edit_phone').value = member.phone;
        document.getElementById('edit_email').value = member.email;
        document.getElementById('edit_linkedin').value = member.linkedin;
        
        var editModal = new bootstrap.Modal(document.getElementById('editMemberModal'));
        editModal.show();
    }
    </script>
</body>
</html>