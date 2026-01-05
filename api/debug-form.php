<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => true,
    'message' => 'Debug endpoint working',
    'server_info' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'],
        'SCRIPT_FILENAME' => __FILE__,
        'raw_input' => file_get_contents('php://input'),
        'POST' => $_POST,
        'GET' => $_GET
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
