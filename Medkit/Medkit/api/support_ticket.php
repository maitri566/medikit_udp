<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

// Parse JSON body or form data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!$data) {
    $data = $_POST;
}

$name = sanitize($data['name'] ?? '');
$email = sanitize($data['email'] ?? '');
$subject = sanitize($data['subject'] ?? '');
$message = sanitize($data['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    jsonResponse(false, 'All fields (name, email, subject, message) are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email format');
}

// If user is logged in, attach their user_id
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO support_tickets (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);

if ($stmt->execute()) {
    jsonResponse(true, 'Support ticket submitted successfully! We will get back to you soon.');
} else {
    jsonResponse(false, 'Failed to submit support ticket. Please try again.');
}
