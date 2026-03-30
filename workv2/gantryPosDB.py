from pylogix import PLC
from datetime import datetime
import time
import mysql.connector

# ---------------- USER SETTINGS ----------------
PLC_IP = '192.168.1.10'
START_INDEX = 0
END_INDEX = 10
SCAN_TIME = 0.25
DEADBAND = 0.001

DB_CONFIG = {
    "host": "localhost",
      "user": "gantry",
    "password": "gantry123",
    "database": "gantry_db"
}

SYSTEMS = {
    "EastGantry": {
        "x_tag": "EastGantryXPositions",
        "z_tag": "EastGantryZPositions"
    },
    "WestGantry": {
        "x_tag": "WestGantryXPositions",
        "z_tag": "WestGantryZPositions"
    },
    "GearFit": {
        "x_tag": "GearFitXPositions",
        "z_tag": "GearFitZPositions"
    }
}
# ----------------------------------------------

# ---------------- PLC SETUP ----------------
plc = PLC()
plc.IPAddress = PLC_IP
plc.ProcessorSlot = 0
# ----------------------------------------------

# ---------------- DATABASE ----------------
db = mysql.connector.connect(**DB_CONFIG)
cursor = db.cursor()
# ----------------------------------------------

# ---------------- READ LOCATION NAMES ----------------
location_names = {}

print("Reading location names from PLC...")

for system, cfg in SYSTEMS.items():
    location_names[system] = {}

    for i in range(START_INDEX, END_INDEX + 1):
        r = plc.Read(f'{cfg["x_tag"]}[{i}].Name')
        location_names[system][i] = str(r.Value) if r.Status == 'Success' else f'Loc_{i}'
# ----------------------------------------------

# ---------------- TRACK LAST VALUES ----------------
last_values = {}

for system in SYSTEMS:
    last_values[system] = {
        'X': {i: None for i in range(START_INDEX, END_INDEX + 1)},
        'Z': {i: None for i in range(START_INDEX, END_INDEX + 1)}
    }
# ----------------------------------------------

print("Logging gantry preset changes to SQL (change-only)...")

# ---------------- MAIN LOOP ----------------
while True:
    try:
        for system, cfg in SYSTEMS.items():
            for i in range(START_INDEX, END_INDEX + 1):
                for axis, base_tag in [('X', cfg["x_tag"]), ('Z', cfg["z_tag"])]:

                    r = plc.Read(f'{base_tag}[{i}].Position.Preset')
                    if r.Status != 'Success':
                        continue

                    new_val = float(r.Value)

                    if last_values[system][axis][i] is None:
                        last_values[system][axis][i] = new_val
                        continue

                    old_val = last_values[system][axis][i]

                    if abs(new_val - old_val) > DEADBAND:
                        now = datetime.now()

                        # INSERT INTO SQL
                        cursor.execute("""
                            INSERT INTO gantry_axis_log
                            (system, axis, location_name, old_preset, new_preset, timestamp)
                            VALUES (%s, %s, %s, %s, %s, %s)
                        """, (
                            system,
                            axis,
                            location_names[system][i],
                            old_val,
                            new_val,
                            now
                        ))

                        db.commit()

                        last_values[system][axis][i] = new_val

                        print(
                            f'{system} | {location_names[system][i]} | '
                            f'Axis {axis}: {old_val} → {new_val}'
                        )

        time.sleep(SCAN_TIME)

    except Exception as e:
        print(f'Logger error: {e}')
        time.sleep(2)