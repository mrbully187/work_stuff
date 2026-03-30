<?php
$conn = new mysqli("localhost", "root", "", "gantry_db");

$system = isset($_GET['system']) ? $_GET['system'] : 'EastGantry';

/* ================= DOWNLOAD CSV ================= */
if (isset($_GET['download'])) {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$system.'_gantry_log.csv"');

    $output = fopen("php://output", "w");

    // column headers
    fputcsv($output, ['Time','System','Axis','Location','Old','New']);

    $sql = "SELECT * FROM gantry_axis_log 
            WHERE system = '".$conn->real_escape_string($system)."'
            ORDER BY timestamp DESC";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['timestamp'],
            $row['system'],
            $row['axis'],
            $row['location_name'],
            $row['old_preset'],
            $row['new_preset']
        ]);
    }

    fclose($output);
    exit;
}
/* ================================================= */

$sql = "SELECT * FROM gantry_axis_log 
        WHERE system = '".$conn->real_escape_string($system)."'
        ORDER BY timestamp DESC 
        LIMIT 100";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gantry Dashboard</title>

    <style>
        body { font-family: Arial; background: #111; color: #eee; }

        .tabs { display: flex; margin-bottom: 10px; }

        .tab {
            padding: 10px 20px;
            margin-right: 5px;
            background: #333;
            cursor: pointer;
            border-radius: 5px;
        }

        .tab.active {
            background: #00bcd4;
            color: black;
            font-weight: bold;
        }

        .download {
            margin: 10px 0;
            padding: 8px 15px;
            background: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #222;
        }

        th, td {
            padding: 8px;
            border: 1px solid #444;
            text-align: center;
        }

        th { background: #333; }
    </style>
</head>

<body>

<h2>Gantry Axis Changes</h2>

<div class="tabs">
    <div class="tab <?= $system=='EastGantry'?'active':'' ?>" onclick="go('EastGantry')">East</div>
    <div class="tab <?= $system=='WestGantry'?'active':'' ?>" onclick="go('WestGantry')">West</div>
    <div class="tab <?= $system=='GearFit'?'active':'' ?>" onclick="go('GearFit')">GearFit</div>
</div>

<!-- DOWNLOAD BUTTON -->
<button class="download" onclick="downloadCSV()">⬇ Download CSV</button>

<table>
<tr>
    <th>Time</th>
    <th>Axis</th>
    <th>Location</th>
    <th>Old</th>
    <th>New</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['timestamp'] ?></td>
    <td><?= $row['axis'] ?></td>
    <td><?= $row['location_name'] ?></td>
    <td><?= $row['old_preset'] ?></td>
    <td><?= $row['new_preset'] ?></td>
</tr>
<?php endwhile; ?>

</table>

<script>
function go(system) {
    window.location = "?system=" + system;
}

function downloadCSV() {
    window.location = "?system=<?= $system ?>&download=1";
}
</script>

</body>
</html>