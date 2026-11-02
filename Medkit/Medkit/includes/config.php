<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'medkit_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Medkit');
define('SITE_URL', 'http://localhost/Medkit/Medkit');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection function
function getDBConnection() {
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Helper: sanitize input
function sanitize($data) {
    $conn = getDBConnection();
    return htmlspecialchars(strip_tags($conn->real_escape_string(trim($data))));
}

// Helper: JSON response
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Helper: check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper: check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit;
}
