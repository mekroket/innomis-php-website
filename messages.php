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

// PHPMailer sınıflarını dahil et
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Yolları güncelleyin
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// E-posta gönderme fonksiyonunu güncelle
function sendEmailNotification($name, $email, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // SMTP ayarları
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Gmail SMTP sunucusu
        $mail->SMTPAuth = true;
        $mail->Username = 'info.innomis@gmail.com'; // Gmail adresiniz
        $mail->Password = 'lgrn etwl byoi hfnx'; // Oluşturduğunuz 16 haneli uygulama şifresini buraya yapıştırın
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Alıcı ayarları
        $mail->setFrom($email, 'INNOMIS İletişim');
        $mail->addAddress('info.innomis@gmail.com');
        $mail->addReplyTo($email, $name);

        // E-posta içeriği
        $mail->isHTML(true);
        $mail->Subject = "Yeni İletişim Formu Mesajı: " . $subject;
        
        $email_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { padding: 20px; }
                .header { background: #3b82f6; color: white; padding: 10px; }
                .content { padding: 15px 0; }
                .footer { color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Yeni İletişim Formu Mesajı</h2>
                </div>
                <div class='content'>
                    <p><strong>Gönderen:</strong> $name</p>
                    <p><strong>E-posta:</strong> $email</p>
                    <p><strong>Konu:</strong> $subject</p>
                    <p><strong>Mesaj:</strong></p>
                    <p>$message</p>
                </div>
                <div class='footer'>
                    <p>Bu e-posta INNOMIS web sitesi iletişim formundan gönderilmiştir.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $email_body;

        return $mail->send();
    } catch (Exception $e) {
        error_log("E-posta gönderme hatası: {$mail->ErrorInfo}");
        return false;
    }
}

// Mesaj silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Mesaj başarıyla silindi.</div>";
    } else {
        echo "<div class='alert alert-danger'>Hata: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Yeni mesajları kontrol et ve e-posta gönder
$sql = "SELECT * FROM messages WHERE is_notified = 0 OR is_notified IS NULL";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Her yeni mesaj için e-posta gönder
        if (sendEmailNotification($row['name'], $row['email'], $row['subject'], $row['message'])) {
            // Mesajı bildirildi olarak işaretle
            $update_sql = "UPDATE messages SET is_notified = 1 WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Tüm mesajları getir
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlar - Admin Panel</title>
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
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 1rem;
            background: var(--dark-bg);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            vertical-align: middle;
            color: var(--text);
            background: var(--card-bg);
        }

        .table tbody tr {
            transition: all 0.3s ease;
            background: var(--card-bg);
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.05);
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
                <h1>Mesajlar</h1>
                <p>Gelen mesajları yönet</p>
            </div>

            <!-- Messages Table -->
            <div class="messages-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gönderen</th>
                                <th>E-posta</th>
                                <th>Konu</th>
                                <th>Mesaj</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['subject']) ?></td>
                                        <td><?= htmlspecialchars($row['message']) ?></td>
                                        <td>
                                            <a href="?delete_id=<?= $row['id'] ?>" 
                                               class="btn-action btn-delete" 
                                               onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?')"
                                               title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Henüz mesaj bulunmamaktadır.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 