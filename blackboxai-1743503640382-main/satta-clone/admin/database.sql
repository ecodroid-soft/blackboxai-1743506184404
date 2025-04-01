-- Create database
CREATE DATABASE IF NOT EXISTS satta_king;
USE satta_king;

-- Games table
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL,
    time_slot TIME NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Results table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    number INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status ENUM('WIN', 'PENDING') DEFAULT 'WIN',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id),
    UNIQUE KEY unique_result (game_id, date)
);

-- Admin logs table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default games
INSERT INTO games (name, display_name, time_slot) VALUES
('faridabad', 'FARIDABAD', '18:00:00'),
('gaziyabad', 'GAZIYABAD', '20:00:00'),
('gali', 'GALI', '23:00:00'),
('ds', 'DS', '16:00:00');