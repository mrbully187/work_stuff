from pylogix import PLC
import mysql.connector
import time
from datetime import datetime
import random
value = random.uniform(0, 300)

# ---------------- CONFIG ----------------
PLC_IP = '192.168.1.10'

TAGS = {
    "EastGantryX": "EastGantryXAxis.ActualPosition",
    "EastGantryZ": "EastGantryZAxis.ActualPosition",
    "WestGantryX": "WestGantryXAxis.ActualPosition",
    "WestGantryZ": "WestGantryZAxis.ActualPosition",
    "GearFitX": "GearFitGantryXAxis.ActualPosition",
    "GearFitZ": "GearFitGantryZAxis.ActualPosition"
}

SCAN_TIME = 0.5   # seconds
DEADBAND = 0.01   # minimum change to log

DB_CONFIG = {
    "host": "localhost",
    "user": "gantryuser",
    "password": "password123",
    "database": "gantrydb"
}

# ---------------- SETUP ----------------
conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor()

last_values = {}

# ---------------- MAIN LOOP ----------------
with PLC() as plc:
    plc.IPAddress = PLC_IP
    
    result = plc.Read("EastGantryXAxis.ActualPosition")
    print(result.Status, result.Value)

    print("Starting logger...")

    while True:
        try:
            for name, tag in TAGS.items():
                result = plc.Read(tag)

                if result.Status != 'Success':
                    print(f"Read failed: {tag}")
                    continue

                value = float(result.Value)

                # Deadband check
                if name not in last_values or abs(value - last_values[name]) > DEADBAND:

                    cursor.execute(
                        "INSERT INTO gantry_axis_log (axis_name, position, timestamp) VALUES (%s, %s, %s)",
                        (name, value, datetime.now())
                    )
                    conn.commit()

                    last_values[name] = value

                    print(f"{name}: {value}")

            time.sleep(SCAN_TIME)

        except Exception as e:
            print("Error:", e)
            time.sleep(2)