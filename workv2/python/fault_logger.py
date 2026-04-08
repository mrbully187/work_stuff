import time
import xml.etree.ElementTree as ET
from datetime import datetime
from pylogix import PLC
import mysql.connector

# ---------------- USER SETTINGS ----------------
PLC_IP = '192.168.1.10'
XML_FILE = r'C:\Users\fingram\Desktop\vt info\finish end test\py\fault_config.xml'
SCAN_TIME = 0.25

DB_CONFIG = {
    "host": "localhost",
    "user": "gantry",
    "password": "gantry123",
    "database": "gantry_db"
}

# ---------------- LOAD XML ----------------
def load_faults(xml_file):
    tree = ET.parse(xml_file)
    root = tree.getroot()

    faults = {}

    for signal in root.findall('.//Signal'):
        tag = signal.find('TagName').text.strip()
        bit = int(signal.find('Bit').text)

        if tag not in faults:
            faults[tag] = {}

        faults[tag][bit] = {
            "name": signal.find('Name').text.strip(),
            "desc": signal.find('Description').text.strip()
        }

    return faults

# ---------------- DATABASE ----------------
def connect_db():
    return mysql.connector.connect(**DB_CONFIG)

def start_fault(cursor, tag, bit, name, desc):
    sql = """
    INSERT INTO faults (tag_name, bit, fault_name, description, start_time)
    VALUES (%s, %s, %s, %s, NOW())
    """
    cursor.execute(sql, (tag, bit, name, desc))


def end_fault(cursor, tag, bit):
    sql = """
    UPDATE faults
    SET 
        end_time = NOW(),
        duration_seconds = TIMESTAMPDIFF(SECOND, start_time, NOW())
    WHERE tag_name = %s AND bit = %s AND end_time IS NULL
    """
    cursor.execute(sql, (tag, bit))

# ---------------- PLC READ ----------------
def get_active_faults(plc, fault_map):
    active_faults = []

    for tag in fault_map:
        result = plc.Read(tag)

        if result.Status != 'Success':
            print(f"PLC read failed: {tag}")
            continue

        value = result.Value

        for bit, info in fault_map[tag].items():
            if value & (1 << bit):
                active_faults.append((tag, bit))

    return active_faults

# ---------------- CHANGE DETECTION ----------------
previous_faults = set()

def detect_changes(current_faults):
    global previous_faults

    current_set = set(current_faults)

    new_faults = current_set - previous_faults
    cleared_faults = previous_faults - current_set

    previous_faults = current_set

    return new_faults, cleared_faults

# ---------------- MAIN ----------------
def main():
    print("Starting Fault Logger...")

    fault_map = load_faults(XML_FILE)
    db = connect_db()
    cursor = db.cursor()

    with PLC() as plc:
        plc.IPAddress = PLC_IP

        while True:
            try:
                active_faults = get_active_faults(plc, fault_map)

                new_faults, cleared_faults = detect_changes(active_faults)

                # NEW faults
                for tag, bit in new_faults:
                    info = fault_map[tag][bit]
                    print(f"NEW: {info['desc']}")
                    start_fault(cursor, tag, bit, info["name"], info["desc"])

                # CLEARED faults
                for tag, bit in cleared_faults:
                    info = fault_map[tag][bit]
                    print(f"CLEARED: {info['desc']}")
                    end_fault(cursor, tag, bit)
                
                db.commit()
                time.sleep(SCAN_TIME)
                
            except Exception as e:
                print(f"ERROR: {e}")
                time.sleep(1)

# ---------------- RUN ----------------
if __name__ == "__main__":
    main()