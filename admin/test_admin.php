<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест адмін-доступу - SmartLock</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .test-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            margin: 0 0 1.5rem 0;
            color: #1a202c;
        }
        .result {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            border: 2px solid #f59e0b;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            border: 2px solid #3b82f6;
        }
        .detail {
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: #f3f4f6;
            border-radius: 4px;
        }
        .admin-card {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-left: 4px solid #4169FF;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #4169FF;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .back-link:hover {
            background: #3451d9;
        }
        .test-password {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔐 Тест адмін-доступу</h1>
        
        <?php
        require_once '../config/database.php';
        
        try {
            $conn = getDBConnection();
            
            if (!$conn) {
                throw new Exception('Не вдалося підключитися до бази даних');
            }
            
            // Перевірка наявності таблиці admins
            $tables_sql = "SHOW TABLES LIKE 'admins'";
            $tables_stmt = $conn->query($tables_sql);
            $table_exists = $tables_stmt->rowCount() > 0;
            
            if (!$table_exists) {
                echo '<div class="result error">';
                echo '<strong>❌ Таблиця admins не знайдена!</strong>';
                echo '<div class="detail">Виконайте SQL скрипт для створення таблиць</div>';
                echo '</div>';
            } else {
                // Отримання всіх адміністраторів
                $stmt = $conn->query("SELECT id, username, email, created_at, last_login FROM admins");
                $admins = $stmt->fetchAll();
                
                if (count($admins) > 0) {
                    echo '<div class="result success">';
                    echo '<strong>✅ Адміністратори знайдені!</strong>';
                    echo '<div class="detail">Всього адміністраторів: ' . count($admins) . '</div>';
                    echo '</div>';
                    
                    echo '<div class="info">';
                    echo '<strong>📋 Список адміністраторів:</strong>';
                    
                    foreach ($admins as $admin) {
                        echo '<div class="admin-card">';
                        echo '<div><strong>ID:</strong> ' . htmlspecialchars($admin['id']) . '</div>';
                        echo '<div><strong>Логін:</strong> ' . htmlspecialchars($admin['username']) . '</div>';
                        echo '<div><strong>Email:</strong> ' . htmlspecialchars($admin['email']) . '</div>';
                        echo '<div><strong>Створено:</strong> ' . date('d.m.Y H:i', strtotime($admin['created_at'])) . '</div>';
                        if ($admin['last_login']) {
                            echo '<div><strong>Останній вхід:</strong> ' . date('d.m.Y H:i', strtotime($admin['last_login'])) . '</div>';
                        } else {
                            echo '<div><strong>Останній вхід:</strong> Ще не входив</div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    
                    // Тест перевірки пароля
                    echo '<div class="test-password">';
                    echo '<strong>🔑 Тест перевірки пароля (admin/admin123):</strong>';
                    
                    $test_user = 'admin';
                    $test_password = 'admin123';
                    
                    $sql = "SELECT password_hash FROM admins WHERE username = :username";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':username', $test_user);
                    $stmt->execute();
                    $admin = $stmt->fetch();
                    
                    if ($admin && password_verify($test_password, $admin['password_hash'])) {
                        echo '<div style="color: #065f46; margin-top: 0.5rem;">✅ Пароль перевірено успішно! Можете входити.</div>';
                    } else {
                        echo '<div style="color: #991b1b; margin-top: 0.5rem;">❌ Помилка перевірки пароля!</div>';
                    }
                    echo '</div>';
                    
                } else {
                    echo '<div class="result warning">';
                    echo '<strong>⚠️ Адміністратори не знайдені!</strong>';
                    echo '<div class="detail">Потрібно додати адміністратора через SQL:</div>';
                    echo '<pre style="background: #1f2937; color: #10b981; padding: 1rem; border-radius: 4px; overflow-x: auto; margin-top: 1rem;">';
                    echo "INSERT INTO admins (username, password_hash, email) \n";
                    echo "VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@smartlock.ua');";
                    echo '</pre>';
                    echo '</div>';
                }
            }
            
        } catch(Exception $e) {
            echo '<div class="result error">';
            echo '<strong>❌ Помилка!</strong>';
            echo '<div class="detail">' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '</div>';
        }
        ?>
        
        <a href="login.php" class="back-link">← Повернутися до входу</a>
    </div>
</body>
</html>
