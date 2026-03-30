


-- =========================
-- CREATE DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS gantrydb;
USE gantrydb;

-- =========================
-- CREATE TABLE
-- =========================

CREATE TABLE IF NOT EXISTS east_gantry_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    axis VARCHAR(10),
    location_name VARCHAR(50),
    old_preset FLOAT,
    new_preset FLOAT,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS west_gantry_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    axis VARCHAR(10),
    location_name VARCHAR(50),
    old_preset FLOAT,
    new_preset FLOAT,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS gear_fit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    axis VARCHAR(10),
    location_name VARCHAR(50),
    old_preset FLOAT,
    new_preset FLOAT,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS gantry_axis_log (
    id INT AUTO_INCREMENT PRIMARY KEY,

    system VARCHAR(20) NOT NULL,          -- West / East / Finish
    axis VARCHAR(10) NOT NULL,            -- X / Z / etc
    location_name VARCHAR(50) NOT NULL,   -- Station / Position name

    old_preset FLOAT NOT NULL,
    new_preset FLOAT NOT NULL,

    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- INDEXES (IMPORTANT FOR SPEED)
-- =========================
CREATE INDEX idx_system ON gantry_axis_log(system);
CREATE INDEX idx_timestamp ON gantry_axis_log(timestamp);
CREATE INDEX idx_axis ON gantry_axis_log(axis);

-- =========================
-- SAMPLE DATA (TESTING)
-- =========================
INSERT INTO gantry_axis_log 
(system, axis, location_name, old_preset, new_preset, timestamp)
VALUES
('West', 'X', 'Load Station', 100, 120, NOW()),
('West', 'Z', 'Unload Station', 50, 55, NOW()),
('East', 'X', 'Pick Point', 200, 210, NOW()),
('Finish', 'Z', 'Drop Zone', 75, 80, NOW());)