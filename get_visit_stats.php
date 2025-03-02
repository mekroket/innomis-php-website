<?php
require_once 'db_connection.php'; // Veritabanı bağlantısı

$period = $_GET['period'] ?? 'daily';
$data = ['labels' => [], 'values' => []];

switch ($period) {
    case 'daily':
        // Son 24 saatin verileri
        $sql = "SELECT 
                    DATE_FORMAT(visit_date, '%H:00') as label,
                    SUM(visitor_count) as total
                FROM visitors
                WHERE visit_date = CURDATE()
                GROUP BY HOUR(visit_date)
                ORDER BY HOUR(visit_date)";
        break;

    case 'weekly':
        // Son 7 günün verileri
        $sql = "SELECT 
                    DATE_FORMAT(visit_date, '%d %b') as label,
                    SUM(visitor_count) as total
                FROM visitors
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY visit_date
                ORDER BY visit_date";
        break;

    case 'monthly':
        // Son 30 günün verileri
        $sql = "SELECT 
                    DATE_FORMAT(visit_date, '%d %b') as label,
                    SUM(visitor_count) as total
                FROM visitors
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY visit_date
                ORDER BY visit_date";
        break;
}

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $data['labels'][] = $row['label'];
    $data['values'][] = (int)$row['total'];
}

header('Content-Type: application/json');
echo json_encode($data); 