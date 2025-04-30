-- Create database
CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Create testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample data
INSERT INTO testimonials (client_name, text) VALUES
('John Doe', 'Sangat puas dengan hasil website yang dibuat. Profesional dan tepat waktu.'),
('Jane Smith', 'Pelayanan yang sangat baik dan hasil yang memuaskan.'),
('Mike Johnson', 'Tim yang sangat kompeten dan mudah diajak bekerja sama.'); 