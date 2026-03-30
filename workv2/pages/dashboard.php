<h2>Dashboard  (work in <progress></progress></h2>

<?php
// GET LATEST VALUE FOR EACH AXIS
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

// ORGANIZE DATA
$data = [];

while ($row = $result->fetch_assoc()) {
    $name = $row['axis_name'];

    if (strpos($name, 'East') !== false) {
        $gantry = 'East';
    } elseif (strpos($name, 'West') !== false) {
        $gantry = 'West';
    } elseif (strpos($name, 'GearFit') !== false) {
        $gantry = 'GearFit';
    } else {
        continue;
    }

    if (strpos($name, 'X') !== false) {
        $axis = 'X';
    } elseif (strpos($name, 'Z') !== false) {
        $axis = 'Z';
    } else {
        continue;
    }

    $data[$gantry][$axis] = $row['position'];
}
?>

<div id="heartbeat" style="position:absolute; top:10px; right:15px; color:#0f0;">
    ● LIVE
</div>

<div class="dashboard-grid">

    <div class="card">
        <div class="update-time" id="timeEast">--:--:--</div>
        <h3>East Gantry</h3>

        <div class="axis">
            X: <span id="EastGantryX" class="big-value">---</span>
        </div>

        <div class="axis">
            Z: <span id="EastGantryZ" class="big-value">---</span>
        </div>
    </div>

    <div class="card">
        <div class="update-time" id="timeWest">--:--:--</div>
        <h3>West Gantry</h3>

        <div class="axis">
            X: <span id="WestGantryX" class="big-value">---</span>
        </div>

        <div class="axis">
            Z: <span id="WestGantryZ" class="big-value">---</span>
        </div>
    </div>

    <div class="card">
        <div class="update-time" id="timeGear">--:--:--</div>
        <h3>GearFit</h3>

        <div class="axis">
            X: <span id="GearFitX" class="big-value">---</span>
        </div>

        <div class="axis">
            Z: <span id="GearFitZ" class="big-value">---</span>
        </div>
    </div>

</div>

</div>

<script src="/js/dashscripts.js"></script>