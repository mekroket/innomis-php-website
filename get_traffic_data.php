<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "innomis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$period = $_GET['period'] ?? 'weekly';

switch($period) {
    case 'daily':
        // 24 saatlik dilimleri getir (00:00'dan 23:00'a)
        $sql = "SELECT 
                h.hour,
                COALESCE(SUM(us.duration)/60, 0) as total_duration
                FROM (
                    SELECT 0 as hour UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
                    UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 
                    UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 
                    UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 
                    UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 
                    UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23
                ) h
                LEFT JOIN user_sessions us ON HOUR(us.session_start) = h.hour 
                AND DATE(us.session_start) = CURDATE()
                GROUP BY h.hour
                ORDER BY h.hour ASC";
        break;
        
    case 'weekly':
        // Günlük veriler - Pazartesi'den Pazar'a
        $sql = "SELECT 
                CASE DAYOFWEEK(session_start)
                    WHEN 2 THEN 'Pazartesi'
                    WHEN 3 THEN 'Salı'
                    WHEN 4 THEN 'Çarşamba'
                    WHEN 5 THEN 'Perşembe'
                    WHEN 6 THEN 'Cuma'
                    WHEN 7 THEN 'Cumartesi'
                    WHEN 1 THEN 'Pazar'
                END as day,
                SUM(duration)/60 as total_duration 
                FROM user_sessions 
                WHERE session_start >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DAYOFWEEK(session_start)
                ORDER BY DAYOFWEEK(session_start)";
        break;
        
    case 'monthly':
        // Haftalık veriler
        $sql = "SELECT 
                CONCAT('Hafta ', WEEK(session_start) - WEEK(DATE_SUB(CURDATE(), INTERVAL 30 DAY)) + 1) as week,
                SUM(duration)/60 as total_duration 
                FROM user_sessions 
                WHERE session_start >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY WEEK(session_start)
                ORDER BY WEEK(session_start)";
        break;
}

$result = $conn->query($sql);

$dates = [];
$durations = [];

while($row = $result->fetch_assoc()) {
    if ($period === 'daily') {
        $hour = str_pad($row['hour'], 2, '0', STR_PAD_LEFT);
        $dates[] = $hour . ':00';
    } else if ($period === 'weekly') {
        $dates[] = $row['day'];
    } else {
        $dates[] = $row['week'];
    }
    $durations[] = round($row['total_duration'], 2);
}

echo json_encode([
    'dates' => $dates,
    'durations' => $durations
]);

$conn->close(); 