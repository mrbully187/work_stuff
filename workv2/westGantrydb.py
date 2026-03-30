from pylogix import PLC
from datetime import datetime
import time
import mysql.connector

# ---------------- USER SETTINGS ----------------
PLC_IP = '192.168.1.10'
X_BASE_TAG = 'WestGantryXPositions'
Z_BASE_TAG = 'WestGantryZPositions'

START_INDEX = 0
END_INDEX = 10
SCAN_TIME = 2
DEADBAND = 0.001
# ----------------------------------------------

# ---------------- PLC ----------------
plc = PLC()
plc.IPAddress = PLC_IP
plc.ProcessorSlot = 0

# ---------------- DATABASE (MYSQL) ----------------
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="GantryDB"
)

cursor = conn.cursor()

cursor.execute("""
CREATE TABLE IF NOT EXISTS west_gantry_axis_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME NOT NULL,
    axis VARCHAR(10) NOT NULL,
    old_preset FLOAT NOT NULL,
    new_preset FLOAT NOT NULL
);
""")

conn.commit()

# ---------------- TRACK LAST VALUES ----------------
last_values = {
    'X': {i: None for i in range(START_INDEX, END_INDEX + 1)},
    'Z': {i: None for i in range(START_INDEX, END_INDEX + 1)}
}

print("Logging to MySQL west gantry changes ...")

# ---------------- MAIN LOOP ----------------
while True:
    try:
        for i in range(START_INDEX, END_INDEX + 1):
            for axis, base_tag in [('X', X_BASE_TAG), ('Z', Z_BASE_TAG)]:

                tag = f'{base_tag}[{i}].Position.Preset'
                r = plc.Read(tag)

                if r.Status != 'Success':
                    continue

                new_value = float(r.Value)

                if last_values[axis][i] is None:
                    last_values[axis][i] = new_value
                    continue

                old_value = last_values[axis][i]

                if abs(new_value - old_value) > DEADBAND:
                    now = datetime.now()

                    cursor.execute(
                        "INSERT INTO gantry_axis_log (timestamp, axis, old_preset, new_preset) VALUES (%s, %s, %s, %s)",
                        (now, axis, old_value, new_value)
                    )

                    conn.commit()

                    last_values[axis][i] = new_value

                    print(f'{now} | Axis {axis}: {old_value} → {new_value}')

        time.sleep(SCAN_TIME)

    except Exception as e:
        print(f'Logger error: {e}')
        time.sleep(2)