<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Replace with your MySQL username
define('DB_PASS',''); // Replace with your MySQL password
define('DB_NAME', 'scmirt_library');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session for user authentication
session_start();
?>