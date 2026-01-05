<?php
require_once '../config/database.php';

echo "<h2>Тест форми контактів</h2>";

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        echo "<p style='color: red;'>❌ Помилка підключення до бази даних</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Підключення до БД успішне</p>";
    
    // Перевірка чи існує таблиця contact_requests
    $check_table = $conn->query("SHOW TABLES LIKE 'contact_requests'");
    if ($check_table->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Таблиця 'contact_requests' не існує!</p>";
        echo "<p>Створюю таблицю...</p>";
        
        $create_table = "CREATE TABLE contact_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            position VARCHAR(255),
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            quantity VARCHAR(50),
            message TEXT,
            status ENUM('new', 'in_progress', 'completed', 'rejected') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->exec($create_table)) {
            echo "<p style='color: green;'>✅ Таблиця створена успішно!</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Таблиця 'contact_requests' існує</p>";
    }
    
    // Тестовий запис
    echo "<h3>Створення тестового запиту...</h3>";
    
    $test_data = [
        'company' => 'Тестова компанія',
        'name' => 'Іван Тестовий',
        'position' => 'Менеджер',
        'email' => 'test@example.com',
        'phone' => '+380501234567',
        'quantity' => '10',
        'message' => 'Тестове повідомлення'
    ];
    
    $sql = "INSERT INTO contact_requests (company, name, position, email, phone, quantity, message, status, created_at) 
            VALUES (:company, :name, :position, :email, :phone, :quantity, :message, 'new', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':company', $test_data['company']);
    $stmt->bindParam(':name', $test_data['name']);
    $stmt->bindParam(':position', $test_data['position']);
    $stmt->bindParam(':email', $test_data['email']);
    $stmt->bindParam(':phone', $test_data['phone']);
    $stmt->bindParam(':quantity', $test_data['quantity']);
    $stmt->bindParam(':message', $test_data['message']);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Тестовий запит створено успішно!</p>";
        echo "<p>ID нового запиту: " . $conn->lastInsertId() . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Помилка створення запиту</p>";
    }
    
    // Показати всі запити
    echo "<h3>Всі запити в базі:</h3>";
    $all_requests = $conn->query("SELECT * FROM contact_requests ORDER BY created_at DESC")->fetchAll();
    
    if (count($all_requests) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Компанія</th><th>Ім'я</th><th>Email</th><th>Телефон</th><th>Статус</th><th>Дата</th></tr>";
        foreach ($all_requests as $req) {
            echo "<tr>";
            echo "<td>" . $req['id'] . "</td>";
            echo "<td>" . $req['company'] . "</td>";
            echo "<td>" . $req['name'] . "</td>";
            echo "<td>" . $req['email'] . "</td>";
            echo "<td>" . $req['phone'] . "</td>";
            echo "<td>" . $req['status'] . "</td>";
            echo "<td>" . $req['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Запитів немає</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='../contact.php'>Перейти на сторінку контактів</a></p>";
    echo "<p><a href='../admin/login.php'>Перейти в адмін-панель</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Помилка: " . $e->getMessage() . "</p>";
}
?>
