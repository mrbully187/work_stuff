<?php
// ⚠️ NO spaces or output before this line

// Turn off errors from printing to screen (they break downloads)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connect DB
$conn = new mysqli("localhost", "gantryuser", "password123", "gantrydb");
if ($conn->connect_error) {
    die("DB Failed");
}

// Force download headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="gantry_log.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output
$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['Time', 'Axis', 'Position']);

// Query (adjust table if needed)
$sql = "SELECT timestamp, axis_name, position FROM axis_position_log ORDER BY timestamp DESC";
$result = $conn->query($sql);

// If query fails, still output something
if (!$result) {
    fputcsv($output, ['ERROR', $conn->error]);
} else {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['timestamp'],
            $row['axis_name'],
            $row['position']
        ]);
    }
}

fclose($output);
$conn->close();
exit;
?>