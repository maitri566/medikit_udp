<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

if (!isLoggedIn()) {
    jsonResponse(false, 'You must be logged in to update your profile');
}

// Parse JSON body or form data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!$data) {
    $data = $_POST;
}

$full_name = sanitize($data['full_name'] ?? '');
$email = sanitize($data['email'] ?? '');
$phone = sanitize($data['phone'] ?? '');
$address = sanitize($data['address'] ?? '');

if (empty($full_name) || empty($email)) {
    jsonResponse(false, 'Full name and email are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email format');
}

$user_id = (int)$_SESSION['user_id'];
$conn = getDBConnection();

// Check if email is already used by another user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    jsonResponse(false, 'Email is already in use by another account');
}

$stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
$stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);

if ($stmt->execute()) {
    // Keep session data in sync
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;

    jsonResponse(true, 'Profile updated successfully', [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address
    ]);
} else {
    jsonResponse(false, 'Failed to update profile. Please try again.');
}
