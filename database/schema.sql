-- =============================================
-- Note Management Application - Database Schema
-- Môn: 503073 - Web Programming & Applications
-- =============================================

CREATE DATABASE IF NOT EXISTS note_app_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE note_app_db;

-- =============================================
-- 1. Bảng users - Quản lý tài khoản người dùng
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,          -- bcrypt hashed
    avatar VARCHAR(255) DEFAULT NULL,        -- đường dẫn file avatar
    is_activated TINYINT(1) DEFAULT 0,       -- 0: chưa kích hoạt, 1: đã kích hoạt
    activation_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(10) DEFAULT NULL,    -- OTP 6 số
    reset_expires DATETIME DEFAULT NULL,     -- thời hạn OTP
    font_size VARCHAR(10) DEFAULT 'medium',  -- small, medium, large
    note_color VARCHAR(7) DEFAULT '#ffffff', -- mã màu HEX
    theme VARCHAR(10) DEFAULT 'light',       -- light hoặc dark
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 2. Bảng notes - Lưu trữ ghi chú
-- =============================================
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT '',
    content TEXT DEFAULT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    pinned_at DATETIME DEFAULT NULL,
    lock_password VARCHAR(255) DEFAULT NULL,  -- bcrypt hashed, NULL = không khóa
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 3. Bảng note_images - Hình ảnh đính kèm
-- =============================================
CREATE TABLE IF NOT EXISTS note_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 4. Bảng labels - Nhãn (thuộc về từng user)
-- =============================================
CREATE TABLE IF NOT EXISTS labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 5. Bảng note_labels - Quan hệ nhiều-nhiều
-- =============================================
CREATE TABLE IF NOT EXISTS note_labels (
    note_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (note_id, label_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 6. Bảng note_shares - Chia sẻ ghi chú
-- =============================================
CREATE TABLE IF NOT EXISTS note_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    owner_id INT NOT NULL,
    shared_with_id INT NOT NULL,
    permission ENUM('read', 'edit') DEFAULT 'read',
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_share (note_id, shared_with_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Tài khoản demo (password: 123456)
-- =============================================
INSERT INTO users (email, display_name, password, is_activated) VALUES
('demo@example.com', 'Demo User', '$2y$10$AKxb/WNM3w4BgEUzzVDV0.t6CBP0nvzz8uVXG82mLrgrUUkd1XxFK', 1),
('demo2@example.com', 'Demo User 2', '$2y$10$AKxb/WNM3w4BgEUzzVDV0.t6CBP0nvzz8uVXG82mLrgrUUkd1XxFK', 1);
