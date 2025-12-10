<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Функція для валідації email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Функція для очищення введених даних
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримання даних з форми
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Валідація обов'язкових полів
    $required_fields = ['company', 'name', 'email', 'phone'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            $errors[] = "Поле '{$field}' є обов'язковим";
        }
    }
    
    // Валідація email
    if (!empty($input['email']) && !validateEmail($input['email'])) {
        $errors[] = "Невірний формат email";
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Помилка валідації',
            'errors' => $errors
        ]);
        exit;
    }
    
    try {
        $conn = getDBConnection();
        
        if (!$conn) {
            throw new Exception('Помилка підключення до бази даних');
        }
        
        // Підготовка SQL запиту
        $sql = "INSERT INTO contact_requests (company, name, position, email, phone, quantity, message) 
                VALUES (:company, :name, :position, :email, :phone, :quantity, :message)";
        
        $stmt = $conn->prepare($sql);
        
        // Прив'язка параметрів
        $stmt->bindParam(':company', sanitizeInput($input['company']));
        $stmt->bindParam(':name', sanitizeInput($input['name']));
        $stmt->bindParam(':position', sanitizeInput($input['position'] ?? ''));
        $stmt->bindParam(':email', sanitizeInput($input['email']));
        $stmt->bindParam(':phone', sanitizeInput($input['phone']));
        $stmt->bindParam(':quantity', sanitizeInput($input['quantity'] ?? ''));
        $stmt->bindParam(':message', sanitizeInput($input['message'] ?? ''));
        
        // Виконання запиту
        $stmt->execute();
        
        // Відправка email сповіщення (опціонально)
        $to = 'sales@smartlock.ua';
        $subject = 'Новий запит з контактної форми SmartLock';
        $message_body = "
            Нова заявка від компанії: {$input['company']}\n
            Ім'я: {$input['name']}\n
            Email: {$input['email']}\n
            Телефон: {$input['phone']}\n
            Повідомлення: {$input['message']}
        ";
        
        mail($to, $subject, $message_body);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Дякуємо за ваш запит! Ми зв\'яжемося з вами найближчим часом.'
        ]);
        
    } catch(Exception $e) {
        error_log("Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Виникла помилка при обробці запиту. Спробуйте пізніше.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Метод не дозволено'
    ]);
}
?>
