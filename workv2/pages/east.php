<?php
$table = "east_gantry_log";
$title = "East Gantry";

$result = $conn->query("
    SELECT * FROM $table
    ORDER BY timestamp DESC
    LIMIT 50
");
?>

<h2 style="text-align:center;"><?php echo $title; ?></h2>

<table>
<tr>
    <th>Time</th>
    <th>Location</th>
    <th>Axis</th>
    <th>Old</th>
    <th>New</th>
    <th>Change</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        $diff = $row['new_preset'] - $row['old_preset'];
        $class = (abs($diff) > 5) ? "big" : "";

        echo "<tr>
            <td>{$row['timestamp']}</td>
            <td>{$row['location_name']}</td>
            <td>{$row['axis']}</td>
            <td>{$row['old_preset']}</td>
            <td>{$row['new_preset']}</td>
            <td class='$class'>$diff</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No data</td></tr>";
}
?>

</table>