<?php
// Конфігурація бази даних
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartlock_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Створення підключення до БД
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        // ВАЖЛИВО: показуємо точну причину помилки!
        die("DB ERROR: " . $e->getMessage());
    }
}

// Налаштування сесії (правильно для localhost)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
//ini_set('session.cookie_secure', 0); // <-- МАЄ бути 0 на localhost

session_start();
?>
