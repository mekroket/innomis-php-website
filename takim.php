<?php
session_start();
require_once 'db_connection.php';

// Yönetici girişi kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: adminlogin.php');
    exit;
}

// Üye Ekleme
if (isset($_POST['add_member'])) {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $instagram = $_POST['instagram'];
    $linkedin = $_POST['linkedin'];
    
    // Resim yükleme
    $target_dir = "assets/img/team/";
    $image = $_FILES['image']['name'];
    $target_file = $target_dir . basename($image);
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $sql = "INSERT INTO team_members (name, position, image, instagram, linkedin) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $position, $image, $instagram, $linkedin);
        $stmt->execute();
    }
}

// Üye Silme
if (isset($_POST['delete_member'])) {
    $id = $_POST['member_id'];
    
    // Önce resmi sil
    $sql = "SELECT image FROM team_members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    
    if ($member['image']) {
        unlink("assets/img/team/" . $member['image']);
    }
    
    // Sonra veritabanından sil
    $sql = "DELETE FROM team_members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Üye Güncelleme
if (isset($_POST['update_member'])) {
    $id = $_POST['member_id'];
    $name = $_POST['name'];
    $position = $_POST['position'];
    $instagram = $_POST['instagram'];
    $linkedin = $_POST['linkedin'];
    
    if ($_FILES['image']['size'] > 0) {
        // Yeni resim yüklendi
        $target_dir = "assets/img/team/";
        $image = $_FILES['image']['name'];
        $target_file = $target_dir . basename($image);
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $sql = "UPDATE team_members SET name=?, position=?, image=?, instagram=?, linkedin=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $position, $image, $instagram, $linkedin, $id);
        }
    } else {
        // Resim güncellenmedi
        $sql = "UPDATE team_members SET name=?, position=?, instagram=?, linkedin=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $position, $instagram, $linkedin, $id);
    }
    $stmt->execute();
}

// Tüm üyeleri getir
$sql = "SELECT * FROM team_members ORDER BY order_number ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takım Yönetimi - Admin Panel</title>
    <link href="assets/css/admin.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="admin-panel">
    <div class="container mt-5">
        <h2>Takım Yönetimi</h2>
        
        <!-- Üye Ekleme Formu -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Yeni Üye Ekle</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>İsim</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Pozisyon</label>
                            <input type="text" name="position" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Fotoğraf</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Instagram</label>
                            <input type="url" name="instagram" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>LinkedIn</label>
                            <input type="url" name="linkedin" class="form-control">
                        </div>
                    </div>
                    <button type="submit" name="add_member" class="btn btn-primary">Üye Ekle</button>
                </form>
            </div>
        </div>

        <!-- Üye Listesi -->
        <div class="card">
            <div class="card-header">
                <h5>Mevcut Üyeler</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fotoğraf</th>
                                <th>İsim</th>
                                <th>Pozisyon</th>
                                <th>Sosyal Medya</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="assets/img/team/<?= $row['image'] ?>" height="50"></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['position'] ?></td>
                                <td>
                                    <?php if($row['instagram']): ?>
                                        <a href="<?= $row['instagram'] ?>" target="_blank"><i class="bi bi-instagram"></i></a>
                                    <?php endif; ?>
                                    <?php if($row['linkedin']): ?>
                                        <a href="<?= $row['linkedin'] ?>" target="_blank"><i class="bi bi-linkedin"></i></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editMember(<?= $row['id'] ?>)">Düzenle</button>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete_member" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Bu üyeyi silmek istediğinizden emin misiniz?')">Sil</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Düzenleme Modalı -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Üye Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="editForm">
                        <input type="hidden" name="member_id" id="edit_id">
                        <div class="mb-3">
                            <label>İsim</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Pozisyon</label>
                            <input type="text" name="position" id="edit_position" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Yeni Fotoğraf (Opsiyonel)</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Instagram</label>
                            <input type="url" name="instagram" id="edit_instagram" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>LinkedIn</label>
                            <input type="url" name="linkedin" id="edit_linkedin" class="form-control">
                        </div>
                        <button type="submit" name="update_member" class="btn btn-primary">Güncelle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    function editMember(id) {
        // AJAX ile üye bilgilerini getir
        fetch(`get_member.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_position').value = data.position;
                document.getElementById('edit_instagram').value = data.instagram;
                document.getElementById('edit_linkedin').value = data.linkedin;
                
                // Modalı göster
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
    }
    </script>
</body>
</html> 