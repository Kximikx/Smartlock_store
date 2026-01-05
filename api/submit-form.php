<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

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
    //
    file_put_contents(
    __DIR__ . '/debug.txt',
    print_r([
        'raw' => file_get_contents('php://input'),
        '_POST' => $_POST
    ], true)
);

    //
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
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        $conn = getDBConnection();
        
        if (!$conn) {
            throw new Exception('Помилка підключення до бази даних');
        }
        
        $company = sanitizeInput($input['company']);
        $name = sanitizeInput($input['name']);
        $position = sanitizeInput($input['position'] ?? '');
        $email = sanitizeInput($input['email']);
        $phone = sanitizeInput($input['phone']);
        $quantity = sanitizeInput($input['quantity'] ?? '');
        $message = sanitizeInput($input['message'] ?? '');
        
        // Підготовка SQL запиту
        $sql = "INSERT INTO contact_requests (company, name, position, email, phone, quantity, message, status, created_at) 
                VALUES (:company, :name, :position, :email, :phone, :quantity, :message, 'new', NOW())";
        
        $stmt = $conn->prepare($sql);
        
        // Прив'язка параметрів
        $stmt->bindParam(':company', $company);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':message', $message);
        
        // Виконання запиту
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Дякуємо за ваш запит! Ми зв\'яжемося з вами найближчим часом.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception('Помилка виконання запиту');
        }
        
    } catch(Exception $e) {
        error_log("Error in submit-form.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Виникла помилка при обробці запиту. Спробуйте пізніше.',
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Метод не дозволено'
    ], JSON_UNESCAPED_UNICODE);
}
?>
