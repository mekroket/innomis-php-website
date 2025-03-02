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

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Innomis</title>
    <link href="assets/img/fav.png" rel="icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            color: #fff;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #495057;
            border-radius: 5px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Innomis Admin Panel</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white" href="adminlogin.php?logout=true">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h4>Menü</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">Mesajlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="addphoto.php">Fotoğraf Yükle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="etkinlik.php">Etkinlik Ekle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Üye Listesi</a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="text-center my-4">Gelen Mesajlar</h2>
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>İsim</th>
                            <th>E-Posta</th>
                            <th>Konu</th>
                            <th>Mesaj</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['subject']) ?></td>
                                    <td><?= htmlspecialchars($row['message']) ?></td>
                                    <td>
                                        <a href="admin.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu mesajı silmek istediğinize emin misiniz?')">Sil</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Henüz mesaj yok.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
                

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>