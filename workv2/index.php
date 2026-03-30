<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB
$conn = new mysqli("localhost", "gantryuser", "password123", "gantrydb");
if ($conn->connect_error) {
    die("DB Failed: " . $conn->connect_error);
}

// ROUTER
$page = $_GET['page'] ?? 'dashboard';

// ALLOWED TABS
$allowed = ['dashboard', 'east', 'west', 'gearfit'];

if (!in_array($page, $allowed)) {
    $page = 'dashboard';
}

// FILE PATH
$file = __DIR__ . "/pages/$page.php";
?>

<!DOCTYPE html>
<html>
<head>

    <link rel="stylesheet" href="styles.css">

    <title>Gantry HMI</title>

</head>

<body>

<div class="tabs">
    <a href="?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="?page=east" class="<?= $page=='east'?'active':'' ?>">East</a>
    <a href="?page=west" class="<?= $page=='west'?'active':'' ?>">West</a>
    <a href="?page=gearfit" class="<?= $page=='gearfit'?'active':'' ?>">GearFit</a>
</div>

<div class="download-container">
    <a href="/workv2/pages/download.php" class="download-btn">
        ⬇ Download CSV
    </a>
</div>


<div class="content">
    <?php
    if (file_exists($file)) {
        include $file;
    } else {
        echo "<h2>Page missing: $page</h2>";
    }
    ?>
</div>

</body>
</html>

<?php $conn->close(); ?>