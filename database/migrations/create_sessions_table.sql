-- Create sessions table if it doesn't exist
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_year VARCHAR(20) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed default sessions (2020-2030) if table is empty
INSERT IGNORE INTO sessions (session_year, is_active) VALUES 
('2020-2021', 1),
('2021-2022', 1),
('2022-2023', 1),
('2023-2024', 1),
('2024-2025', 1),
('2025-2026', 1),
('2026-2027', 1),
('2027-2028', 1),
('2028-2029', 1),
('2029-2030', 1);
