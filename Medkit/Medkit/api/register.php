<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$full_name = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
    jsonResponse(false, 'All fields are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email format');
}

if (strlen($password) < 6) {
    jsonResponse(false, 'Password must be at least 6 characters');
}

if ($password !== $confirm_password) {
    jsonResponse(false, 'Passwords do not match');
}

$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    jsonResponse(false, 'Email already registered. Please login.');
}

// Insert new user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $full_name, $email, $phone, $hashedPassword);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    jsonResponse(true, 'Registration successful!', [
        'user_id' => (int)$_SESSION['user_id'],
        'name' => $full_name,
        'email' => $email
    ]);
} else {
    jsonResponse(false, 'Registration failed. Please try again.');
}
