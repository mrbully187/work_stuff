async function updateData() {
    try {
        const response = await fetch('/api/latest.php');
        const data = await response.json();

        const now = new Date().toLocaleTimeString();

        for (const axis in data) {
            const el = document.getElementById(axis);

            if (el) {
                el.textContent = parseFloat(data[axis]).toFixed(2);
                el.style.color = "#00ff00";
            }

            if (axis.includes("East")) {
                document.getElementById("timeEast").textContent = now;
            }
            if (axis.includes("West")) {
                document.getElementById("timeWest").textContent = now;
            }
            if (axis.includes("GearFit")) {
                document.getElementById("timeGear").textContent = now;
            }
        }

    } catch (err) {
        console.error("AJAX error:", err);
    }
}

// run every 1 second
setInterval(updateData, 1000);

// run once immediately
updateData();

//heartbeat test
setInterval(() => {
    const hb = document.getElementById("heartbeat");
    hb.style.opacity = hb.style.opacity == "0.2" ? "1" : "0.2";
}, 500);

