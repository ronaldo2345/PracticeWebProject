<?php
// Start session (required for authentication)
session_start();

// Database Configuration
define('DB_HOST', 'localhost');      // Database server
define('DB_USER', 'u-240121378');           // Database username
define('DB_PASS', ' THiwTMsvC5qwDWF');               // Database password (empty for XAMPP/WAMP)
define('DB_NAME', 'u_240121378_virtualkitchen');       // Database name
define('BASE_URL', 'http://localhost/virtualkitchen/public'); // Base URL for links

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone Setting
date_default_timezone_set('UTC');

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Security Configuration
define('CSRF_TOKEN_SECRET', 'your-secret-key-here-12345'); // Change this!
define('PASSWORD_COST', 12); // BCrypt cost factor

// File Upload Settings
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);

// Initialize CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include helper functions
require_once __DIR__.'/../includes/functions.php';

// Auto-load classes (if you add OOP later)
spl_autoload_register(function ($class) {
    $file = __DIR__.'/../classes/'.str_replace('\\', '/', $class).'.php';
    if (file_exists($file)) {
        require $file;
    }
});