-- =========================================
-- F.A.S.T System Database
-- Complete schema with sample data
-- =========================================

CREATE DATABASE IF NOT EXISTS fast_system;
USE fast_system;

-- =========================================
-- Table: users
-- =========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('Admin','Head Admin','User') DEFAULT 'User',
    profile_pic VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample users
INSERT INTO users (username, password, fullname, email, role, profile_pic) VALUES
('headadmin', 'password', 'John Doe', 'headadmin@example.com', 'Head Admin', NULL),
('admin1', 'password', 'Jane Smith', 'admin1@example.com', 'Admin', NULL),
('user1', 'password', 'Michael Johnson', 'user1@example.com', 'User', NULL);

-- =========================================
-- Table: documents
-- =========================================
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    signed TINYINT(1) DEFAULT 0,
    signed_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample documents
INSERT INTO documents (user_id, title, file_path, signed) VALUES
(3, 'Consent Form', '/uploads/consent.pdf', 1),
(3, 'Registration Form', '/uploads/register.pdf', 0);

-- =========================================
-- Table: events
-- =========================================
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    status ENUM('Pending','Verified') DEFAULT 'Pending',
    member_since DATE,
    signed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample events
INSERT INTO events (name, description, image_path, status, member_since, signed_date) VALUES
('AGMA 2025', 'Annual General Meeting 2025', '/images/agma1.jpg', 'Verified', '2020-05-01', '2025-08-10'),
('Safety Training', 'Fire and safety training', '/images/safety.jpg', 'Pending', '2022-03-15', NULL);

-- =========================================
-- Table: biometric_records
-- =========================================
CREATE TABLE IF NOT EXISTS biometric_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(100),
    type ENUM('PWD','Regular') DEFAULT 'Regular',
    registration_date DATE,
    last_verification DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample biometric records
INSERT INTO biometric_records (user_id, user_name, type, registration_date, last_verification) VALUES
(3, 'Michael Johnson', 'Regular', '2022-01-15', '2025-08-11 14:30:00');

-- =========================================
-- Table: notifications
-- =========================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    read_status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample notifications
INSERT INTO notifications (user_id, message, read_status) VALUES
(3, 'Your document has been verified.', 0),
(0, 'New AGMA event available!', 0);

-- =========================================
-- Table: carousel_images
-- =========================================
CREATE TABLE IF NOT EXISTS carousel_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample carousel images
INSERT INTO carousel_images (image_path, title, description) VALUES
('/images/carousel1.jpg', 'Welcome Event', 'Join our upcoming events'),
('/images/carousel2.jpg', 'Safety Training', 'Learn essential safety skills');

-- =========================================
-- End of Database Schema
-- =========================================
