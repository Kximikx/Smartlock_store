<?php
require_once '../config/database.php';

// Перевірка чи вже залогінений
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Будь ласка, заповніть всі поля';
    } else {
        try {
            $conn = getDBConnection();
            
            if (!$conn) {
                throw new Exception('Помилка підключення до бази даних');
            }
            
            $sql = "SELECT id, username, password_hash, email FROM admins WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Успішна авторизація
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                
                // Оновлення часу останнього входу
                $update_sql = "UPDATE admins SET last_login = NOW() WHERE id = :id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindParam(':id', $admin['id']);
                $update_stmt->execute();
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Невірний логін або пароль';
            }
        } catch(Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Виникла помилка при авторизації';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в адмін-панель - SmartLock</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        .login-box {
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #718096;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2d3748;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4169FF;
        }
        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: #4169FF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: #3451d9;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>SmartLock</h1>
                <p>Адмін-панель</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Логін</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Увійти</button>
            </form>
            
            <p style="margin-top: 1.5rem; text-align: center; color: #718096; font-size: 0.875rem;">
                За замовчуванням: admin / admin123
            </p>
        </div>
    </div>
</body>
</html>
