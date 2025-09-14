-- =========================================
-- F.A.S.T System Database - Updated Clean Version
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
    role ENUM('Admin','User') DEFAULT 'User',
    profile_pic VARCHAR(255),
    remember_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account
-- Username: admin
-- Password: admin123 (hashed)
INSERT INTO users (username, password, fullname, email, role) VALUES
('admin', '$2y$10$wH0pIbz9OZT8m3gH3G3FQe6gT6CzmE7M4bYVf7M15VbYxsm5z5W1e', 'Administrator', 'admin@example.com', 'Admin');

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

-- =========================================
-- Table: notifications
-- =========================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    read_status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

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
