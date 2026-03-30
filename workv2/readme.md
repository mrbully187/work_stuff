CREATE TABLE IF NOT EXISTS east_gantry_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    axis VARCHAR(10),
    location_name VARCHAR(50),
    old_preset FLOAT,
    new_preset FLOAT,
    timestamp DATETIME
);

CREATE TABLE IF NOT EXISTS west_gantry_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    axis VARCHAR(10),
    location_name VARCHAR(50),
    old_preset FLOAT,
    new_preset FLOAT,
    timestamp DATETIME
);

CREATE TABLE IF NOT EXISTS gear_fit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    axis VARCHAR(10),
    location_name VARCHAR(50),
    old_preset FLOAT,
    new_preset FLOAT,
    timestamp DATETIME
);