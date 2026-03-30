<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "gantry", "gantry123", "gantry_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM gantry_axis_log ORDER BY timestamp DESC LIMIT 20");

if (!$result) {
    die("Query failed: " . $conn->error);
}

while($row = $result->fetch_assoc()) {
    echo $row['system'] . " | " . $row['axis'] . " | " . $row['location_name'] . " | " . $row['new_preset'] . "<br>";
}
?>