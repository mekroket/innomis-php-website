<?php
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

// Sayfa başlığı
$page_title = "Finans Yönetimi";

// Toplam bakiye hesapla
$sql = "SELECT 
        (SELECT COALESCE(SUM(amount), 0) FROM finances WHERE type='income') -
        (SELECT COALESCE(SUM(amount), 0) FROM finances WHERE type='expense') as balance";
$result = $conn->query($sql);
$balance = $result->fetch_assoc()['balance'];

// Finans kaydı ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_finance'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $payment_method = $_POST['payment_method'];
    $receipt_no = $_POST['receipt_no'];
    $added_by = $_SESSION['admin_id'];

    $sql = "INSERT INTO finances (type, description, amount, category, payment_method, receipt_no, added_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssi", $type, $description, $amount, $category, $payment_method, $receipt_no, $added_by);

    if ($stmt->execute()) {
        $success_message = "Kayıt başarıyla eklendi.";
        header("Location: finans.php");
    } else {
        $error_message = "Kayıt eklenirken bir hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finans Yönetimi - Admin Panel</title>

    <!-- Fontlar -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        .nav-link:hover,
        .nav-link.active {
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

        /* Messages Card */
        .messages-card {
            background: var(--dark-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Table Styles */
        .table {
            color: var(--text);
            margin: 0;
            background: var(--card-bg);
        }

        .table th {
            color: var(--text);
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem;
            background: var(--dark-bg);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
            color: var(--text);
            background: var(--card-bg);
        }

        .table tbody tr {
            transition: all 0.3s ease;
            background: var(--card-bg);
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Tablo başlıkları için ek stil */
        .table thead th {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tablo içeriği için ek stil */
        .table tbody td {
            font-size: 0.95rem;
        }

        /* Boş mesaj için stil */
        .text-center {
            color: var(--text-muted);
        }

        /* Message Details */
        .message-subject {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .message-preview {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Action Buttons */
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
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
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

        /* Table container için ek stil */
        .table-responsive {
            background: var(--dark-bg);
            border-radius: 10px;
            overflow: hidden;
        }

        /* Finans özet kartları için yeni stiller */
        .summary-item {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .summary-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .income .summary-icon {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        .expense .summary-icon {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .balance .summary-icon {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }

        .summary-info {
            flex: 1;
        }

        .summary-info h3 {
            font-size: 1rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .summary-info h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            color: var(--text);
        }

        .income .summary-info h2 {
            color: #22c55e;
        }

        .expense .summary-info h2 {
            color: #ef4444;
        }

        .balance .summary-info h2 {
            color: #3b82f6;
        }

        /* Responsive düzenlemeler */
        @media (max-width: 768px) {
            .summary-item {
                margin-bottom: 1rem;
            }
            
            .summary-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            
            .summary-info h2 {
                font-size: 1.5rem;
            }
        }

        /* Modal stilleri */
        .modal-content {
            background: var(--dark-bg) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: var(--text);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
            background: var(--dark-bg);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            background: var(--dark-bg);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Form elemanları */
        .form-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--card-bg) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text) !important;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: var(--card-bg) !important;
            border-color: var(--accent);
            color: var(--text) !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23e2e8f0' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            padding-right: 2.5rem;
        }

        /* Placeholder rengi */
        ::placeholder {
            color: var(--text-muted) !important;
            opacity: 0.7;
        }

        /* Form elemanları için hover efekti */
        .form-control:hover, .form-select:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Butonlar */
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text);
            padding: 0.75rem 1.5rem;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            color: var(--text);
        }

        .btn-primary {
            background: var(--accent);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #2563eb;
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

        <!-- Finans Ekleme Modal -->
        <div class="modal fade" id="addFinanceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni İşlem Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">İşlem Türü</label>
                                <select name="type" class="form-select" required>
                                    <option value="income">Gelir</option>
                                    <option value="expense">Gider</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Açıklama</label>
                                <input type="text" name="description" class="form-control" placeholder="İşlem açıklaması" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tutar</label>
                                <input type="number" name="amount" class="form-control" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select" required>
                                    <option value="Etkinlik">Etkinlik</option>
                                    <option value="Malzeme">Malzeme</option>
                                    <option value="Sponsorluk">Sponsorluk</option>
                                    <option value="Bağış">Bağış</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ödeme Yöntemi</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Nakit">Nakit</option>
                                    <option value="Banka">Banka</option>
                                    <option value="Kredi Kartı">Kredi Kartı</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fiş/Fatura No</label>
                                <input type="text" name="receipt_no" class="form-control" placeholder="Opsiyonel">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" name="add_finance" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content kısmını ekleyin -->
        <main class="main-content">
            <div class="page-title mb-4">
                <h1>Finans Yönetimi</h1>
                <p>Gelir ve giderleri yönet</p>
            </div>

            <div class="messages-card">
                <!-- Finans Özeti -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="summary-item income">
                            <div class="summary-icon">
                                <i class="fas fa-arrow-trend-up"></i>
                            </div>
                            <div class="summary-info">
                                <h3>Toplam Gelir</h3>
                                <?php
                                $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM finances WHERE type='income'";
                                $result = $conn->query($sql);
                                $total_income = $result->fetch_assoc()['total'];
                                ?>
                                <h2>₺<?php echo number_format($total_income, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="summary-item expense">
                            <div class="summary-icon">
                                <i class="fas fa-arrow-trend-down"></i>
                            </div>
                            <div class="summary-info">
                                <h3>Toplam Gider</h3>
                                <?php
                                $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM finances WHERE type='expense'";
                                $result = $conn->query($sql);
                                $total_expense = $result->fetch_assoc()['total'];
                                ?>
                                <h2>₺<?php echo number_format($total_expense, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="summary-item balance">
                            <div class="summary-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="summary-info">
                                <h3>Mevcut Bakiye</h3>
                                <h2>₺<?php echo number_format($balance, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- İşlem Ekleme Butonu -->
                <div class="mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFinanceModal">
                        <i class="fas fa-plus me-2"></i>Yeni İşlem Ekle
                    </button>
                </div>

                <!-- İşlem Listesi -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Tür</th>
                                <th>Açıklama</th>
                                <th>Kategori</th>
                                <th>Tutar</th>
                                <th>Ödeme Yöntemi</th>
                                <th>Fiş/Fatura No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM finances ORDER BY date DESC";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $type_class = $row['type'] == 'income' ? 'text-success' : 'text-danger';
                                    $type_text = $row['type'] == 'income' ? 'Gelir' : 'Gider';
                                    ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y H:i', strtotime($row['date'])); ?></td>
                                        <td class="<?php echo $type_class; ?>"><?php echo $type_text; ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td class="<?php echo $type_class; ?>">
                                            ₺<?php echo number_format($row['amount'], 2); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                        <td><?php echo htmlspecialchars($row['receipt_no']); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Henüz işlem bulunmamaktadır.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>

<?php $conn->close(); ?>