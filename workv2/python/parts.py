from pylogix import PLC
import time
from typing import Optional

last_part: Optional[str] = None
current_part_count = 0

# Track previous values
prev_pass = None
prev_fail = None
prev_reset = False

# Total counters
total_pass = 0
total_fail = 0

# Per-part tracking
part_counts = {
    "3311A": 0,
    "3311B": 0,
    "3311C": 0,
    "3311D": 0,
    "A": 0,
    "B": 0,
    "C": 0,
    "D": 0
}

def get_active_part(tags):
    if tags['Part_3311A_Selected']: return "3311A"
    if tags['Part_3311B_Selected']: return "3311B"
    if tags['Part_3311C_Selected']: return "3311C"
    if tags['Part_3311D_Selected']: return "3311D"
    if tags['Part_A_Selected']: return "A"
    if tags['Part_B_Selected']: return "B"
    if tags['Part_C_Selected']: return "C"
    if tags['Part_D_Selected']: return "D"
    return None

last_part = None

with PLC() as comm:
    comm.IPAddress = '192.168.1.10'  # 🔴 change this

    while True:
        tags = comm.Read([
            'Cell.PartCounts.Pass',
            'Cell.PartCounts.Fail',
            'Cell.PartCounts.ResetPB',
            'Part_3311A_Selected',
            'Part_3311B_Selected',
            'Part_3311C_Selected',
            'Part_3311D_Selected',
            'Part_A_Selected',
            'Part_B_Selected',
            'Part_C_Selected',
            'Part_D_Selected'
        ])

        # Convert to dict
        data = {t.TagName: t.Value for t in tags}

        current_pass = int(data['Cell.PartCounts.Pass'])
        current_fail = int(data['Cell.PartCounts.Fail'])
        reset = data['Cell.PartCounts.ResetPB']

        active_part = get_active_part(data)
        
        if active_part != last_part and active_part is not None:
            print(f"🔄 Switched to {active_part}")
            last_part = active_part

        # --- RESET LOGIC (one-shot style) ---
        if reset and not prev_reset:
            print("🔄 RESET PRESSED")

            total_pass = 0
            total_fail = 0

            for key in part_counts:
                part_counts[key] = 0

        prev_reset = reset

        # --- PASS COUNT DELTA ---
        if prev_pass is not None:
            if current_pass > prev_pass:
                delta = current_pass - prev_pass
                total_pass += delta

                if active_part == last_part and active_part is not None:
                    current_part_count += delta
                    part_counts[active_part] += delta

                print(f"PASS +{delta} | Total: {total_pass} | Part: {active_part}")

            elif current_pass < prev_pass:
                print("⚠️ Pass counter reset detected")
                total_pass = current_pass

        # --- FAIL COUNT DELTA ---
        if prev_fail is not None:
            if current_fail > prev_fail:
                delta = current_fail - prev_fail
                total_fail += delta

                print(f"FAIL +{delta} | Total: {total_fail}")

            elif current_fail < prev_fail:
                print("⚠️ Fail counter reset detected")
                total_fail = current_fail

        prev_pass = current_pass
        prev_fail = current_fail

        # --- DEBUG OUTPUT ---
        print("Current Part Counts:", part_counts)

        time.sleep(0.1)