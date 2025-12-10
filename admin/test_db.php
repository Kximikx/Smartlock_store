<?php
require_once '../config/database.php';

$conn = getDBConnection();

if ($conn) {
    echo "CONNECTED OK";
} else {
    echo "FAILED";
}
?>
