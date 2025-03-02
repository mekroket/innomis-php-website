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
$interval = $period === 'weekly' ? '7 DAY' : '30 DAY';

$sql = "SELECT 
        DATE_FORMAT(visit_date, '%d %b') as date,
        visitor_count
        FROM visitors 
        WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL $interval)
        ORDER BY visit_date ASC";

$result = $conn->query($sql);

$dates = [];
$counts = [];

while($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
    $counts[] = (int)$row['visitor_count'];
}

echo json_encode([
    'dates' => $dates,
    'counts' => $counts
]);

$conn->close(); 