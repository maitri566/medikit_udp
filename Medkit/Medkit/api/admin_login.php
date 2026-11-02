<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    jsonResponse(false, 'Please enter both email and password');
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(false, 'Admin not found');
}

$admin = $result->fetch_assoc();

if (!password_verify($password, $admin['password'])) {
    jsonResponse(false, 'Incorrect password');
}

$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_email'] = $admin['email'];

jsonResponse(true, 'Login successful!', [
    'admin_id' => (int)$admin['id'],
    'email' => $admin['email']
]);
