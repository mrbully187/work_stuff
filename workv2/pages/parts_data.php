<?php
$conn = new mysqli("localhost", "gantry", "yourpassword", "gantrydb");

// ---------------- CURRENT RUN ----------------
$current = $conn->query("
    SELECT *
    FROM part_runs
    WHERE end_time IS NULL
    ORDER BY start_time DESC
    LIMIT 1
");

// ---------------- LAST RUNS ----------------
$runs = $conn->query("
    SELECT *
    FROM part_runs
    WHERE end_time IS NOT NULL
    ORDER BY end_time DESC
    LIMIT 10
");

// ---------------- TOTALS ----------------
$totals = $conn->query("
    SELECT 
        part_name,
        SUM(pass_count) as total_pass,
        SUM(fail_count) as total_fail
    FROM part_runs
    GROUP BY part_name
    ORDER BY part_name
");

// ---------------- LOST TIME ----------------
$lost = $conn->query("
    SELECT 
        SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as lost_seconds
    FROM fault_log
    WHERE end_time IS NOT NULL
");

$lost_data = $lost->fetch_assoc();
$lost_seconds = $lost_data['lost_seconds'] ?? 0;
$lost_minutes = round($lost_seconds / 60, 2);

// ---------------- CURRENT ROW ----------------
$current_row = $current->fetch_assoc();

// ---------------- LIVE METRICS ----------------
$runtime_minutes = 0;
$rate = 0;

if ($current_row) {
    $start = strtotime($current_row['start_time']);
    $now = time();

    $runtime_minutes = max(($now - $start) / 60, 0.01);
    $rate = round($current_row['pass_count'] / $runtime_minutes, 2);
}
?>

<!-- 🔴 LOST TIME -->
<div style="background:#b71c1c; padding:15px; margin-bottom:20px; text-align:center;">
    <h2>🛑 LOST TIME</h2>
    <h1><?= $lost_minutes ?> min</h1>
</div>

<!-- 🟢 CURRENT RUN SUMMARY -->
<div style="background:#1b5e20; padding:15px; margin-bottom:20px; text-align:center;">
    <?php if($current_row): ?>
        <h2>CURRENT PART: <?= $current_row['part_name'] ?></h2>
        <h3>RUN TIME: <?= round($runtime_minutes,1) ?> min</h3>
        <h3>RATE: <?= $rate ?> parts/min</h3>
    <?php else: ?>
        <h2>No Active Run</h2>
    <?php endif; ?>
</div>

<!-- CURRENT RUN TABLE -->
<h2>🟢 CURRENT RUN</h2>
<table>
<tr>
    <th>Part</th>
    <th>Start</th>
    <th>Pass</th>
    <th>Fail</th>
</tr>

<?php if($current_row): ?>
<tr class="active">
    <td><?= $current_row['part_name'] ?></td>
    <td><?= $current_row['start_time'] ?></td>
    <td><?= $current_row['pass_count'] ?></td>
    <td><?= $current_row['fail_count'] ?></td>
</tr>
<?php else: ?>
<tr><td colspan="4">No active run</td></tr>
<?php endif; ?>
</table>

<!-- LAST RUNS -->
<h2>📊 LAST 10 RUNS</h2>
<table>
<tr>
    <th>Part</th>
    <th>Start</th>
    <th>End</th>
    <th>Pass</th>
    <th>Fail</th>
</tr>

<?php while($run = $runs->fetch_assoc()): ?>
<tr>
    <td><?= $run['part_name'] ?></td>
    <td><?= $run['start_time'] ?></td>
    <td><?= $run['end_time'] ?></td>
    <td><?= $run['pass_count'] ?></td>
    <td><?= $run['fail_count'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- TOTALS -->
<h2>📈 TOTALS PER PART</h2>
<table>
<tr>
    <th>Part</th>
    <th>Total Pass</th>
    <th>Total Fail</th>
</tr>

<?php while($total = $totals->fetch_assoc()): ?>
<tr>
    <td><?= $total['part_name'] ?></td>
    <td><?= $total['total_pass'] ?></td>
    <td><?= $total['total_fail'] ?></td>
</tr>
<?php endwhile; ?>
</table>