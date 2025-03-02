<?php
require_once 'db_connection.php';
session_start();

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
    END";
$result = $conn->query($sql);
$members = $result->fetch_all(MYSQLI_ASSOC);

// CRUD işlemleri sadece admin girişi yapılmışsa çalışsın
if(isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['add_member'])) {
        // Üye ekleme kodu...
    }
    
    if (isset($_POST['delete_member'])) {
        // Üye silme kodu...
    }
    
    if (isset($_POST['update_member'])) {
        // Üye güncelleme kodu...
    }
} 