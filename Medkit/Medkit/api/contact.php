<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    jsonResponse(false, 'Name, email and message are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email format');
}

$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $subject, $message);

if ($stmt->execute()) {
    jsonResponse(true, 'Message sent successfully! We will get back to you soon.');
} else {
    jsonResponse(false, 'Failed to send message. Please try again.');
}
