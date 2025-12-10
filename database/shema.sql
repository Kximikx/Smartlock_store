-- Створення бази даних
CREATE DATABASE IF NOT EXISTS smartlock_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartlock_db;

-- Таблиця для запитів з контактної форми
CREATE TABLE IF NOT EXISTS contact_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    quantity VARCHAR(50),
    message TEXT,
    status ENUM('new', 'in_progress', 'completed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблиця для адміністраторів
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Створення адміна за замовчуванням (username: admin, password: admin123)
-- ВАЖЛИВО: Змініть пароль після першого входу!
INSERT INTO admins (username, password_hash, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@smartlock.ua')
ON DUPLICATE KEY UPDATE username=username;
