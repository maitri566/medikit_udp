<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$identifier = sanitize($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    jsonResponse(false, 'Please enter both email/phone and password');
}

$conn = getDBConnection();

// Check if identifier is email or phone
if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $identifier);
} else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $identifier);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(false, 'Account not found. Please register first.');
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    jsonResponse(false, 'Incorrect password');
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_email'] = $user['email'];

jsonResponse(true, 'Login successful!', [
    'user_id' => (int)$user['id'],
    'name' => $user['full_name'],
    'email' => $user['email']
]);
