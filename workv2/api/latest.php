<?php
$conn = new mysqli("localhost", "gantryuser", "password123", "gantrydb");

$result = $conn->query("
    SELECT t1.*
    FROM gantry_axis_log t1
    INNER JOIN (
        SELECT axis_name, MAX(timestamp) as max_time
        FROM gantry_axis_log
        GROUP BY axis_name
    ) t2
    ON t1.axis_name = t2.axis_name AND t1.timestamp = t2.max_time
");

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[$row['axis_name']] = $row['position'];
}

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();