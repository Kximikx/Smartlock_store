<?php
require_once '../config/database.php';

$conn = getDBConnection();

$stmt = $conn->query("SELECT id, username, email FROM admins");
$data = $stmt->fetchAll();

echo "<pre>";
print_r($data);
echo "</pre>";
?>
