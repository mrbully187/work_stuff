<?php
// no heavy queries here — AJAX will handle it
?>

<div id="parts-tab">

<h1>📊 PARTS DASHBOARD</h1>

<div id="data-container">
    Loading...
</div>

<script>
function loadPartsData() {
    fetch('parts_data.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('data-container').innerHTML = data;
        })
        .catch(err => console.error("Load error:", err));
}

// initial load
loadPartsData();

// refresh every 2 sec
setInterval(loadPartsData, 2000);
</script>

</div>