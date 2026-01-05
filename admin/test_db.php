<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест підключення до БД - SmartLock</title>
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
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔍 Тест підключення до бази даних</h1>
        
        <?php
        require_once '../config/database.php';
        
        try {
            $conn = getDBConnection();
            
            if ($conn) {
                echo '<div class="result success">';
                echo '<strong>✅ Підключення до бази даних успішне!</strong>';
                echo '</div>';
                
                echo '<div class="info">';
                echo '<strong>Інформація про базу даних:</strong>';
                echo '<div class="detail">База даних: ' . DB_NAME . '</div>';
                echo '<div class="detail">Хост: ' . DB_HOST . '</div>';
                echo '<div class="detail">Користувач: ' . DB_USER . '</div>';
                echo '</div>';
                
                // Перевірка таблиць
                $tables_sql = "SHOW TABLES";
                $tables_stmt = $conn->query($tables_sql);
                $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($tables) > 0) {
                    echo '<div class="result success">';
                    echo '<strong>✅ Таблиці знайдено:</strong>';
                    foreach ($tables as $table) {
                        echo '<div class="detail">' . $table . '</div>';
                    }
                    echo '</div>';
                    
                    // Перевірка структури таблиць
                    if (in_array('contact_requests', $tables)) {
                        $count_sql = "SELECT COUNT(*) as count FROM contact_requests";
                        $count_stmt = $conn->query($count_sql);
                        $count = $count_stmt->fetch()['count'];
                        echo '<div class="info">';
                        echo '<strong>📊 Статистика:</strong>';
                        echo '<div class="detail">Запитів в базі: ' . $count . '</div>';
                        echo '</div>';
                    }
                    
                    if (in_array('admins', $tables)) {
                        $admin_count_sql = "SELECT COUNT(*) as count FROM admins";
                        $admin_count_stmt = $conn->query($admin_count_sql);
                        $admin_count = $admin_count_stmt->fetch()['count'];
                        echo '<div class="info">';
                        echo '<div class="detail">Адміністраторів в базі: ' . $admin_count . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="result error">';
                    echo '<strong>⚠️ Таблиці не знайдено!</strong>';
                    echo '<div class="detail">Виконайте SQL скрипт для створення таблиць</div>';
                    echo '</div>';
                }
                
            } else {
                throw new Exception('Не вдалося створити підключення');
            }
            
        } catch(PDOException $e) {
            echo '<div class="result error">';
            echo '<strong>❌ Помилка підключення до бази даних!</strong>';
            echo '<div class="detail">Помилка: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<div class="detail">Перевірте налаштування в config/database.php</div>';
            echo '</div>';
        }
        ?>
        
        <a href="login.php" class="back-link">← Повернутися до входу</a>
    </div>
</body>
</html>
