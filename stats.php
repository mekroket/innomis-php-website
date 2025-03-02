<?php
session_start();
require_once 'db_connection.php';

// Yönetici girişi kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: adminlogin.php');
    exit;
}

// İstatistik güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stats'])) {
    $member_count = $_POST['member_count'];
    $event_count = $_POST['event_count'];
    $conference_count = $_POST['conference_count'];
    $social_reach = $_POST['social_reach'];

    $updates = [
        'member_count' => $member_count,
        'event_count' => $event_count,
        'conference_count' => $conference_count,
        'social_reach' => $social_reach
    ];

    foreach ($updates as $name => $value) {
        $sql = "UPDATE statistics SET value = ? WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $value, $name);
        $stmt->execute();
    }

    $success_message = "İstatistikler başarıyla güncellendi!";
}

// Mevcut istatistikleri getir
$sql = "SELECT * FROM statistics";
$result = $conn->query($sql);
$stats = [];
while($row = $result->fetch_assoc()) {
    $stats[$row['name']] = $row['value'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstatistik Yönetimi - Admin Panel</title>
    <link href="assets/css/admin.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="admin-panel">
    <div class="container mt-5">
        <h2>İstatistik Yönetimi</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5>İstatistikleri Güncelle</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Üye Sayısı</label>
                            <input type="number" name="member_count" class="form-control" 
                                   value="<?= $stats['member_count'] ?? 0 ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Etkinlik Sayısı</label>
                            <input type="number" name="event_count" class="form-control" 
                                   value="<?= $stats['event_count'] ?? 0 ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Konferans Sayısı</label>
                            <input type="number" name="conference_count" class="form-control" 
                                   value="<?= $stats['conference_count'] ?? 0 ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Sosyal Medya Erişimi</label>
                            <input type="number" name="social_reach" class="form-control" 
                                   value="<?= $stats['social_reach'] ?? 0 ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_stats" class="btn btn-primary">
                        İstatistikleri Güncelle
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 