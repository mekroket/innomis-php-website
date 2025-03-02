<?php
// Veritabanı yapılandırması
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';  // Eğer root şifreniz varsa buraya yazın
$db_name = 'innomis';

// Hata raporlamayı açın
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Veritabanı bağlantısını oluştur
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Bağlantıyı kontrol et
    if ($conn->connect_error) {
        throw new Exception("Bağlantı hatası: " . $conn->connect_error);
    }
    
    // Türkçe karakter desteği için karakter setini ayarla
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Hata mesajını göster
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?> 