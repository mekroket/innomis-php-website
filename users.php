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

// Kullanıcı düzenleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $faculty = $_POST['faculty'];
    $class = $_POST['class'];
    $studentNumber = $_POST['studentNumber'];
    $department = $_POST['department'];
    $contactNumber = $_POST['contactNumber'];
    $address = $_POST['address'];

    $update_sql = "UPDATE registrations SET 
                   fullName=?, email=?, faculty=?, class=?, 
                   studentNumber=?, department=?, contactNumber=?, address=? 
                   WHERE id=?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssssi", $fullName, $email, $faculty, $class, 
                      $studentNumber, $department, $contactNumber, $address, $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Kullanıcı bilgileri güncellendi!');
                window.location.href = 'users.php';
              </script>";
    } else {
        echo "<script>alert('Güncelleme başarısız oldu!');</script>";
    }
    $stmt->close();
}

// Silme işlemi
if (isset($_GET['delete_id'])) {
    $userId = $_GET['delete_id'];
    $sql = "DELETE FROM registrations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo "<script>alert('Kayıt başarıyla silindi!');</script>";
    } else {
        echo "<script>alert('Silme işlemi başarısız oldu!');</script>";
    }

    $stmt->close();
}

// Sayfalama işlemi
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam üye sayısını al
$total_sql = "SELECT COUNT(*) as total FROM registrations";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];

// Toplam sayfa sayısını hesapla
$total_pages = ceil($total_users / $limit);

// Kayıtları almak için sorgu
$sql = "SELECT * FROM registrations LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Yönetimi - Admin Panel</title>
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

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

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

        .top-bar-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            background: var(--card-bg);
            border: none;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border-radius: 10px;
            color: var(--text);
            width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* Tablo Stilleri */
        .users-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0 1rem;
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text);
        }

        .table-actions {
            display: flex;
            gap: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.8rem;
            margin-bottom: 1rem;
        }

        .table th {
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .table td {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: none;
            color: var(--text);
            font-size: 0.95rem;
        }

        .table tr td:first-child {
            border-radius: 8px 0 0 8px;
        }

        .table tr td:last-child {
            border-radius: 0 8px 8px 0;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover td {
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-1px);
        }

        /* Buton stilleri */
        .btn-action {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent);
        }

        .btn-view {
            background: rgba(34, 197, 94, 0.2);
            color: var(--success);
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        /* Sayfalama Stilleri */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-link {
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.75rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--accent);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--accent);
            color: white;
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
        }

        /* Mail rengi için ekleme */
        .text-muted {
            color: var(--text) !important;
        }

        /* Modal stilleri */
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

        .form-control {
            background: var(--dark-bg);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text);
        }

        .form-control:focus {
            background: var(--dark-bg);
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        .btn-primary {
            background: var(--accent);
            border: none;
        }

        .btn-primary:hover {
            background: var(--accent);
            opacity: 0.9;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            border: none;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
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
                    <h1>Üye Yönetimi</h1>
                    <p>Toplam Üye: <?= $total_users ?></p>
                </div>
                <div class="top-bar-actions">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Üye ara...">
                    </div>
                </div>
            </div>

            <div class="users-card">
                <div class="table-header">
                    <div class="table-title">
                        Üye Listesi
                    </div>
                    <div class="table-actions">
                        <button class="btn-action btn-view" title="Dışa Aktar">
                            <i class="fas fa-file-export"></i>
                        </button>
                        <button class="btn-action btn-edit" title="Filtrele">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ad Soyad</th>
                                <th>Fakülte</th>
                                <th>Sınıf</th>
                                <th>Öğrenci No</th>
                                <th>Bölüm</th>
                                <th>İletişim</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user_number = ($page - 1) * $limit + 1;
                            while ($row = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= $user_number++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2">
                                                <div class="fw-bold"><?= htmlspecialchars($row["fullName"]) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($row["email"]) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row["faculty"]) ?></td>
                                    <td><?= htmlspecialchars($row["class"]) ?></td>
                                    <td><?= htmlspecialchars($row["studentNumber"]) ?></td>
                                    <td><?= htmlspecialchars($row["department"]) ?></td>
                                    <td>
                                        <div class="small">
                                            <div><?= htmlspecialchars($row["contactNumber"]) ?></div>
                                            <div class="text-muted"><?= htmlspecialchars($row["address"]) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-actions">
                                            <button class="btn-action btn-edit" 
                                                    onclick='editUser(<?= json_encode($row) ?>)' 
                                                    title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_id=<?= $row['id'] ?>" 
                                               class="btn-action btn-delete" 
                                               onclick="return confirm('Bu kaydı silmek istediğinizden emin misiniz?')"
                                               title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sayfalama -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </main>
    </div>

    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Üye Düzenle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" name="fullName" id="edit_fullName">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fakülte</label>
                            <input type="text" class="form-control" name="faculty" id="edit_faculty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sınıf</label>
                            <input type="text" class="form-control" name="class" id="edit_class">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Öğrenci No</label>
                            <input type="text" class="form-control" name="studentNumber" id="edit_studentNumber">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bölüm</label>
                            <input type="text" class="form-control" name="department" id="edit_department">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="contactNumber" id="edit_contactNumber">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="address" id="edit_address"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(userData) {
            document.getElementById('edit_user_id').value = userData.id;
            document.getElementById('edit_fullName').value = userData.fullName;
            document.getElementById('edit_email').value = userData.email;
            document.getElementById('edit_faculty').value = userData.faculty;
            document.getElementById('edit_class').value = userData.class;
            document.getElementById('edit_studentNumber').value = userData.studentNumber;
            document.getElementById('edit_department').value = userData.department;
            document.getElementById('edit_contactNumber').value = userData.contactNumber;
            document.getElementById('edit_address').value = userData.address;
            
            var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>