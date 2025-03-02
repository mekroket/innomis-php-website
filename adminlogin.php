<?php
session_start();

// Kullanıcı çıkış yapmak isterse
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: adminlogin.php");
    exit();
}

// Eğer kullanıcı giriş yapmışsa admin.php'ye yönlendir
if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
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

// Giriş kontrolü
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM authorities WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_email'] = $user['email'];
            header("Location: admin.php");
            exit();
        }
    }
    $error = "Geçersiz e-posta veya şifre!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link href="assets/img/fav.png" rel="icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
        }
        .card {
            background: #1e293b;
            color: #e2e8f0;
            border: none;
        }
        .form-control {
            background: #0f172a;
            border: 1px solid #3b82f6;
            color: #e2e8f0;
        }
        .form-control:focus {
            background: #0f172a;
            color: #e2e8f0;
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .btn-primary {
            background: #3b82f6;
            border: none;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-lg" style="width: 350px;">
        <h3 class="text-center mb-3">Admin Giriş</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-Posta</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
        </form>
    </div>
</body>
</html>
