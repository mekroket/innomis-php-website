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

// Giriş yapan admin bilgilerini al
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT name, image, role FROM authorities WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Rol ismini Türkçeleştir
$role_names = [
    'admin' => 'Yönetici',
    'moderator' => 'Moderatör',
    'editor' => 'Editör'
];
$role_display = $role_names[$admin['role']] ?? $admin['role'];

// Toplam ziyaretçi sayısını getir
$sql = "SELECT COUNT(*) as total_visitors FROM user_sessions";
$result = $conn->query($sql);
$total_visitors = $result->fetch_assoc()['total_visitors'];

// Bugünkü ziyaretçi sayısını getir
$sql = "SELECT COUNT(*) as today_visitors FROM user_sessions WHERE DATE(session_start) = CURDATE()";
$result = $conn->query($sql);
$today_visitors = $result->fetch_assoc()['today_visitors'];

// Toplam üye sayısını getir
$users_sql = "SELECT COUNT(*) as total_users FROM registrations";
$users_result = $conn->query($users_sql);
$users_count = $users_result->fetch_assoc()['total_users'];

// Aktif etkinlik sayısını getir
$events_sql = "SELECT COUNT(*) as total_events FROM events";
$events_result = $conn->query($events_sql);
$events_count = $events_result->fetch_assoc()['total_events'];

// Yetkilileri getir
$sql = "SELECT * FROM authorities ORDER BY created_at DESC";
$authorities = $conn->query($sql);
$authorities_count = $authorities->num_rows;

// Mesajları getir
$sql = "SELECT id, name, email, subject, message FROM messages";
$result = $conn->query($sql);

// Mesaj silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: admin.php");
    } else {
        echo "Mesaj silinemedi.";
    }

    $stmt->close();
}

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

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        .nav-link:hover,
        .nav-link.active {
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
            padding-top: calc(70px + 2rem);
            /* Header yüksekliği + padding */
            min-height: 100vh;
            background: var(--dark-bg);
            position: relative;
            overflow-y: auto;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-right: 1.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .member-card .stat-icon {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .event-card .stat-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .admin-card .stat-icon {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }

        .stat-info {
            flex: 1;
        }

        .stat-title {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text);
        }

        /* Cihaz İstatistikleri Kartı */
        .device-card {
            grid-column: span 2;
            display: block;
        }

        .device-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 1.5rem;
        }

        .device-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
        }

        .device-item i {
            font-size: 2rem;
            margin-right: 1rem;
            color: #3b82f6;
        }

        .device-info {
            display: flex;
            flex-direction: column;
        }

        .device-count {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text);
        }

        .device-percent {
            color: #10b981;
            font-size: 0.9rem;
        }

        .device-label {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .device-card {
                grid-column: span 1;
            }
            
            .device-stats {
                flex-direction: column;
                gap: 1rem;
            }
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .chart-actions {
            display: flex;
            gap: 0.5rem;
        }

        .chart-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: var(--text);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .chart-btn:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .chart-btn.active {
            background: var(--accent);
            color: white;
        }

        #trafficChart {
            min-height: 350px;
            margin-top: 1rem;
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

            .chart-grid {
                grid-template-columns: 1fr;
            }
        }

        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--card-bg);
            border-radius: 10px;
            padding: 0.5rem;
            min-width: 160px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent);
        }

        .dropdown-item i {
            font-size: 1rem;
            width: 20px;
        }

        .active-users-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
        }

        .active-users-count {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .active-users-count .count {
            font-size: 3rem;
            font-weight: 600;
            color: #22c55e;
        }

        .active-users-count .label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .active-users-icon {
            width: 60px;
            height: 60px;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .active-users-icon i {
            font-size: 1.5rem;
            color: #22c55e;
        }

        .visitor-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
        }

        .visitor-count {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .total-visitors,
        .today-visitors {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .visitor-count .count {
            font-size: 2.5rem;
            font-weight: 600;
            color: #3b82f6;
            line-height: 1;
        }

        .visitor-count .label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .today-visitors .count {
            color: #22c55e;
        }

        .visitor-icon {
            width: 60px;
            height: 60px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .visitor-icon i {
            font-size: 1.5rem;
            color: #3b82f6;
        }

        .visitor-count-box {
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .visitor-numbers {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .number {
            font-size: 2.5rem;
            font-weight: 600;
            color: #3b82f6;
        }

        .today-visitors .number {
            color: #22c55e;
        }

        .label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .header {
            background: var(--card-bg);
            padding: 0.5rem 2rem;
            position: fixed;
            top: 0;
            right: 0;
            left: 280px;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 70px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            font-size: 1.2rem;
            cursor: pointer;
        }

        .page-title {
            font-size: 1.5rem;
            margin: 0;
            color: var(--text);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .admin-info {
            display: flex;
            align-items: flex-end;
            flex-direction: column;
        }

        .admin-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .welcome-text {
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-bottom: -2px;
        }

        .admin-name {
            color: var(--text);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .admin-role {
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 500;
            padding: 2px 8px;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 4px;
            margin-top: 2px;
        }

        .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent);
        }

        .admin-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: var(--card-bg);
            border-radius: 8px;
            padding: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            display: none;
            min-width: 160px;
        }

        .admin-profile:hover .admin-dropdown {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent);
        }

        @media (max-width: 768px) {
            .toggle-sidebar {
                display: block;
            }

            .header {
                left: 0;
                padding: 0.5rem 1rem;
            }

            .welcome-text {
                display: none;
            }

            .admin-info {
                display: none;
            }

            .admin-profile {
                padding: 0.3rem;
            }

            .admin-avatar {
                width: 35px;
                height: 35px;
            }
        }

        /* Mevcut bakiye kartı için özel stiller */
        .balance-card {
            background: var(--card-bg) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            padding: 1.5rem;
        }

        .balance-card .stat-card-info {
            position: relative;
            z-index: 1;
        }

        .balance-card .stat-card-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .balance-card .stat-card-title {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .balance-card .stat-card-value {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .balance-card .stat-card-change {
            font-size: 0.875rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .balance-card .text-success {
            color: #22c55e !important;
        }

        .balance-card .text-danger {
            color: #ef4444 !important;
        }

        .balance-card .stat-period {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Cihaz istatistikleri kartı için stiller */
        .device-stats-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .device-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 1rem;
        }

        .device-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .device-stat i {
            font-size: 1.5rem;
            color: var(--accent);
        }

        .device-count {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .device-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Mevcut stillere ekleyin */
        .time-stats {
            margin-top: 1rem;
            overflow-x: auto;
        }

        .time-stats-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text);
        }

        .time-stats-table th,
        .time-stats-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--dark-bg);
        }

        .time-stats-table th {
            background: var(--dark-bg);
            color: var(--text-muted);
            font-weight: 500;
        }

        .time-stats-table tr:hover {
            background: var(--dark-bg);
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
            <header class="header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="toggle-sidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="page-title">Yönetici Paneli</h1>
                    </div>

                    <div class="header-right">
                        <div class="admin-profile">
                            <div class="admin-info">
                                <div class="admin-details">
                                    <span class="welcome-text">Hoş geldiniz</span>
                                    <span class="admin-name"><?php echo htmlspecialchars($admin['name']); ?></span>
                                </div>
                                <span class="admin-role"><?php echo htmlspecialchars($role_display); ?></span>
                            </div>
                            <img src="<?php echo htmlspecialchars($admin['image']); ?>" alt="Admin" class="admin-avatar">
                            <div class="admin-dropdown">
                                <a href="adminlogin.php?logout=1" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <!-- Toplam Üye -->
                <div class="stat-card member-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Toplam Üye</div>
                        <div class="stat-value"><?php echo $users_count; ?></div>
                    </div>
                </div>

                <!-- Yapılan Etkinlik -->
                <div class="stat-card event-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Yapılan Etkinlik Sayısı</div>
                        <div class="stat-value"><?php echo $events_count; ?></div>
                    </div>
                </div>

                <!-- Yetkililer -->
                <div class="stat-card admin-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Yetkililer</div>
                        <div class="stat-value"><?php echo $authorities_count; ?></div>
                    </div>
                </div>

                <!-- Mevcut Bakiye Kartı -->
                <div class="stat-card balance-card">
                    <div class="stat-card-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-card-info">
                        <div class="stat-card-title">Mevcut Bakiye</div>
                        <?php
                        // Toplam bakiye hesapla
                        $sql = "SELECT 
                                (SELECT COALESCE(SUM(amount), 0) FROM finances WHERE type='income') -
                                (SELECT COALESCE(SUM(amount), 0) FROM finances WHERE type='expense') as balance";
                        $result = $conn->query($sql);
                        $balance = $result->fetch_assoc()['balance'];

                        // Son 30 günlük değişimi hesapla
                        $sql = "SELECT 
                                (SELECT COALESCE(SUM(amount), 0) FROM finances WHERE type='income' AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) -
                                (SELECT COALESCE(SUM(amount), 0) FROM finances WHERE type='expense' AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as monthly_change";
                        $result = $conn->query($sql);
                        $monthly_change = $result->fetch_assoc()['monthly_change'];
                        ?>
                        <div class="stat-card-value">₺<?php echo number_format($balance, 2); ?></div>
                        <div class="stat-card-change">
                            <?php
                            if ($monthly_change > 0) {
                                echo '<span class="text-success"><i class="fas fa-arrow-up"></i> ₺' . number_format($monthly_change, 2) . '</span>';
                            } elseif ($monthly_change < 0) {
                                echo '<span class="text-danger"><i class="fas fa-arrow-down"></i> ₺' . number_format(abs($monthly_change), 2) . '</span>';
                            } else {
                                echo '<span class="text-muted">Değişim yok</span>';
                            }
                            ?>
                            <span class="stat-period">Son 30 gün</span>
                        </div>
                    </div>
                </div>

                <!-- Cihaz İstatistikleri -->
                <div class="stat-card device-card">
                    <div class="stat-header">
                        <h3>Cihaz İstatistikleri</h3>
                    </div>
                    <div class="device-stats">
                        <?php
                        // Son 30 günlük cihaz istatistiklerini getir
                        $sql = "SELECT 
                                device_type,
                                SUM(visitor_count) as total_count,
                                (SUM(visitor_count) * 100.0 / (
                                    SELECT SUM(visitor_count) 
                                    FROM device_stats 
                                    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                )) as percentage
                                FROM device_stats 
                                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                GROUP BY device_type";
                        
                        $result = $conn->query($sql);
                        $stats = [
                            'desktop' => ['count' => 0, 'percent' => 0],
                            'mobile' => ['count' => 0, 'percent' => 0],
                            'tablet' => ['count' => 0, 'percent' => 0]
                        ];
                        
                        while ($row = $result->fetch_assoc()) {
                            $stats[$row['device_type']] = [
                                'count' => $row['total_count'],
                                'percent' => round($row['percentage'], 1)
                            ];
                        }
                        ?>
                        <div class="device-item">
                            <i class="fas fa-desktop"></i>
                            <div class="device-info">
                                <span class="device-count"><?php echo $stats['desktop']['count']; ?></span>
                                <span class="device-percent"><?php echo $stats['desktop']['percent']; ?>%</span>
                                <span class="device-label">Masaüstü</span>
                            </div>
                        </div>
                        <div class="device-item">
                            <i class="fas fa-mobile-alt"></i>
                            <div class="device-info">
                                <span class="device-count"><?php echo $stats['mobile']['count']; ?></span>
                                <span class="device-percent"><?php echo $stats['mobile']['percent']; ?>%</span>
                                <span class="device-label">Mobil</span>
                            </div>
                        </div>
                        <div class="device-item">
                            <i class="fas fa-tablet-alt"></i>
                            <div class="device-info">
                                <span class="device-count"><?php echo $stats['tablet']['count']; ?></span>
                                <span class="device-percent"><?php echo $stats['tablet']['percent']; ?>%</span>
                                <span class="device-label">Tablet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="chart-grid">
                <!-- Ziyaretçi Sayısı -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Ziyaretçi İstatistikleri</div>
                    </div>
                    <div class="visitor-stats">
                        <div class="visitor-count">
                            <?php
                            // Toplam ziyaretçi sayısı
                            $total_sql = "SELECT SUM(visitor_count) as total FROM visitors";
                            $total_result = $conn->query($total_sql);
                            $total_visitors = $total_result->fetch_assoc()['total'] ?? 0;

                            // Bugünkü ziyaretçi sayısı
                            $today = date('Y-m-d');
                            $today_sql = "SELECT visitor_count FROM visitors WHERE visit_date = ?";
                            $stmt = $conn->prepare($today_sql);
                            $stmt->bind_param("s", $today);
                            $stmt->execute();
                            $today_result = $stmt->get_result();
                            $today_visitors = $today_result->fetch_assoc()['visitor_count'] ?? 0;
                            ?>
                            <div class="total-visitors">
                                <span class="count"><?php echo number_format($total_visitors); ?></span>
                                <span class="label">Toplam Ziyaretçi</span>
                            </div>
                            <div class="today-visitors">
                                <span class="count"><?php echo number_format($today_visitors); ?></span>
                                <span class="label">Bugünkü Ziyaretçi</span>
                            </div>
                        </div>
                        <div class="visitor-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <!-- Site Kullanım Süresi -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Site Kullanım Süresi</div>
                    </div>
                    <div class="time-stats">
                        <?php
                        // Son 7 günün verilerini getir
                        $sql = "SELECT 
                                DATE_FORMAT(visit_date, '%W') as day,
                                SUM(duration_minutes) as total_minutes 
                                FROM user_time_stats 
                                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                GROUP BY visit_date 
                                ORDER BY visit_date";
                        
                        $result = $conn->query($sql);
                        ?>
                        <table class="time-stats-table">
                            <thead>
                                <tr>
                                    <th>Hedef</th>
                                    <th>Toplam Süre (Dakika)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>Geçirilen Süre</td>
                                    <td><?php echo $row['total_minutes']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grafik kodları buraya gelecek
        // Her bir grafik için ayrı options ve render işlemleri

        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Dropdown dışına tıklandığında menüyü kapat
        window.onclick = function(event) {
            if (!event.target.matches('.chart-btn')) {
                var dropdowns = document.getElementsByClassName('dropdown-menu');
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Site kullanım istatistikleri grafiği
        function updateTrafficChart(period) {
            // Aktif butonu güncelle
            document.querySelectorAll('.chart-actions .chart-btn').forEach(btn => {
                if (btn.getAttribute('data-period') === period) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Mevcut grafiği temizle
            document.querySelector("#trafficChart").innerHTML = '';

            // AJAX ile verileri al
            fetch(`get_traffic_data.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    let chartOptions = {
                        series: [{
                            name: 'Geçirilen Süre',
                            data: data.durations
                        }],
                        chart: {
                            height: 350,
                            foreColor: '#94a3b8',
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            },
                            background: 'transparent'
                        },
                        grid: {
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            strokeDashArray: 4,
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        }
                    };

                    if (period === 'daily') {
                        // Günlük görünüm için dikey bar chart
                        chartOptions.chart.type = 'bar';
                        chartOptions.colors = ['#22c55e']; // Yeşil renk
                        chartOptions.plotOptions = {
                            bar: {
                                horizontal: false, // Dikey bar
                                borderRadius: 2,
                                columnWidth: '50%',
                                distributed: true
                            }
                        };
                        chartOptions.dataLabels = {
                            enabled: true,
                            formatter: function(val) {
                                return Math.round(val) + ' dk';
                            },
                            style: {
                                colors: ['#94a3b8'],
                                fontSize: '12px'
                            },
                            offsetY: -20
                        };
                        // X ekseni ayarları (saat dilimleri)
                        chartOptions.xaxis = {
                            categories: data.dates,
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '12px'
                                },
                                formatter: function(val) {
                                    return val;
                                }
                            },
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        };
                        // Y ekseni ayarları (geçirilen süre - dakika)
                        chartOptions.yaxis = {
                            title: {
                                text: 'Geçirilen Süre (Dakika)',
                                style: {
                                    color: '#94a3b8'
                                }
                            },
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '12px'
                                },
                                formatter: function(val) {
                                    return Math.round(val) + ' dk';
                                }
                            }
                        };
                        // Grid ayarları
                        chartOptions.grid = {
                            show: true,
                            borderColor: '#1e293b',
                            strokeDashArray: 4,
                            position: 'back',
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            padding: {
                                top: 0,
                                right: 20,
                                bottom: 0,
                                left: 20
                            }
                        };
                    } else if (period === 'weekly') {
                        // Haftalık görünüm için bar chart
                        chartOptions.chart.type = 'bar';
                        chartOptions.colors = ['#3b82f6'];
                        chartOptions.plotOptions = {
                            bar: {
                                horizontal: false,
                                columnWidth: '50%',
                                endingShape: 'rounded'
                            }
                        };
                        chartOptions.dataLabels = {
                            enabled: true,
                            formatter: function(val) {
                                return Math.round(val) + ' dk';
                            },
                            style: {
                                colors: ['#94a3b8']
                            },
                            offsetY: -20
                        };
                        // X ekseni ayarları (günler)
                        chartOptions.xaxis = {
                            categories: data.dates,
                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '12px'
                                }
                            }
                        };
                        // Y ekseni ayarları (dakika)
                        chartOptions.yaxis = {
                            title: {
                                text: 'Geçirilen Süre (Dakika)',
                                style: {
                                    color: '#94a3b8'
                                }
                            },
                            labels: {
                                style: {
                                    colors: '#94a3b8'
                                }
                            }
                        };
                    } else {
                        // Haftalık ve aylık görünüm için column chart
                        chartOptions.chart.type = 'bar';
                        chartOptions.plotOptions = {
                            bar: {
                                borderRadius: 6,
                                columnWidth: '60%',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        };
                    }

                    // Ortak tooltip ayarları
                    chartOptions.tooltip = {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return Math.round(val) + ' dakika';
                            }
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#trafficChart"), chartOptions);
                    chart.render();
                });
        }

        // Sayfa yüklendiğinde günlük grafiği göster
        document.addEventListener('DOMContentLoaded', function() {
            updateTrafficChart('daily');
        });

        function updateVisitorChart(period) {
            // Aktif butonu güncelle
            document.querySelectorAll('.chart-card:last-child .chart-btn').forEach(btn => {
                if (btn.textContent.toLowerCase().includes(period === 'weekly' ? '7' : '30')) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // AJAX ile verileri al
            fetch(`get_visitor_data.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    var options = {
                        series: [{
                            name: 'Ziyaretçi Sayısı',
                            data: data.counts
                        }],
                        chart: {
                            type: 'line',
                            height: 350,
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: true
                            },
                            background: 'transparent'
                        },
                        colors: ['#3b82f6'],
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        markers: {
                            size: 4,
                            colors: ['#3b82f6'],
                            strokeColors: '#fff',
                            strokeWidth: 2
                        },
                        xaxis: {
                            categories: data.dates,
                            labels: {
                                style: {
                                    colors: '#94a3b8'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#94a3b8'
                                },
                                formatter: function(val) {
                                    return Math.round(val);
                                }
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: function(val) {
                                    return Math.round(val) + ' ziyaretçi';
                                }
                            }
                        },
                        grid: {
                            borderColor: '#1e293b',
                            strokeDashArray: 4
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#visitorChart"), options);
                    chart.render();
                });
        }

        // Sayfa yüklendiğinde haftalık ziyaretçi grafiğini göster
        document.addEventListener('DOMContentLoaded', function() {
            updateVisitorChart('weekly');
        });

        let timeChart = null;

        function updateTimeChart(period) {
            fetch(`get_time_stats.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (timeChart) {
                        timeChart.destroy();
                    }

                    const ctx = document.getElementById('timeChart').getContext('2d');
                    timeChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Geçirilen Süre (Dakika)',
                                data: data.values,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#94a3b8'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#94a3b8'
                                    }
                                }
                            }
                        }
                    });
                });
        }

        // Sayfa yüklendiğinde günlük grafiği göster
        document.addEventListener('DOMContentLoaded', () => {
            updateTimeChart('daily');
        });
    </script>
</body>

</html>

<?php
// En son veritabanı bağlantısını kapat
$conn->close();
?>