<?php
require_once '../config/database.php';

echo "<h2>Тест підключення до БД та перевірка адміна</h2>";

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        echo "<p style='color: red;'>❌ Помилка підключення до бази даних</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Підключення до БД успішне</p>";
    
    // Перевірка чи існує таблиця admins
    $check_table = $conn->query("SHOW TABLES LIKE 'admins'");
    if ($check_table->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Таблиця 'admins' не існує!</p>";
        echo "<p>Виконайте SQL з файлу database/schema.sql</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Таблиця 'admins' існує</p>";
    
    // Перевірка чи є адміністратор
    $sql = "SELECT * FROM admins";
    $stmt = $conn->query($sql);
    $admins = $stmt->fetchAll();
    
    if (count($admins) == 0) {
        echo "<p style='color: red;'>❌ Немає жодного адміністратора!</p>";
        echo "<p>Створюю адміністратора за замовчуванням...</p>";
        
        // Створення адміністратора
        $username = 'admin';
        $password = 'admin123';
        $email = 'admin@smartlock.ua';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_sql = "INSERT INTO admins (username, password_hash, email, created_at) VALUES (:username, :password_hash, :email, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':username', $username);
        $insert_stmt->bindParam(':password_hash', $password_hash);
        $insert_stmt->bindParam(':email', $email);
        
        if ($insert_stmt->execute()) {
            echo "<p style='color: green;'>✅ Адміністратор створений успішно!</p>";
            echo "<p><strong>Логін:</strong> admin</p>";
            echo "<p><strong>Пароль:</strong> admin123</p>";
        } else {
            echo "<p style='color: red;'>❌ Помилка створення адміністратора</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Знайдено адміністраторів: " . count($admins) . "</p>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . $admin['username'] . "</td>";
            echo "<td>" . $admin['email'] . "</td>";
            echo "<td>" . $admin['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Тест пароля
        echo "<h3>Тест верифікації пароля</h3>";
        $test_password = 'admin123';
        $admin = $admins[0];
        
        if (password_verify($test_password, $admin['password_hash'])) {
            echo "<p style='color: green;'>✅ Пароль 'admin123' правильний</p>";
        } else {
            echo "<p style='color: red;'>❌ Пароль 'admin123' не підходить</p>";
            echo "<p>Оновлюю пароль...</p>";
            
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE admins SET password_hash = :password_hash WHERE id = :id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':password_hash', $new_hash);
            $update_stmt->bindParam(':id', $admin['id']);
            
            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>✅ Пароль оновлено успішно!</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<p><a href='login.php'>Перейти на сторінку входу</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Помилка: " . $e->getMessage() . "</p>";
}
?>
