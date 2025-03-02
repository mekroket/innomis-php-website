<?php
require_once 'db_connection.php';

$period = $_GET['period'] ?? 'daily';
$data = ['labels' => [], 'values' => []];

switch ($period) {
    case 'daily':
        $sql = "SELECT 
                    HOUR(visit_time) as hour,
                    SUM(duration_minutes) as total_duration
                FROM user_time_stats 
                WHERE visit_date = CURDATE()
                GROUP BY HOUR(visit_time)
                ORDER BY hour";
        break;
        
    case 'weekly':
        $sql = "SELECT 
                    DATE_FORMAT(visit_date, '%W') as day,
                    SUM(duration_minutes) as total_duration
                FROM user_time_stats 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY visit_date
                ORDER BY visit_date";
        break;
        
    case 'monthly':
        $sql = "SELECT 
                    DATE_FORMAT(visit_date, '%d %b') as day,
                    SUM(duration_minutes) as total_duration
                FROM user_time_stats 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY visit_date
                ORDER BY visit_date";
        break;
}

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $data['labels'][] = $period == 'daily' ? $row['hour'] . ':00' : $row['day'];
    $data['values'][] = (int)$row['total_duration'];
}

header('Content-Type: application/json');
echo json_encode($data); 