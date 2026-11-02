<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$email = sanitize($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address');
}

$conn = getDBConnection();

// Check if already subscribed
$stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    jsonResponse(true, 'You are already subscribed!');
}

$stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    jsonResponse(true, 'Thank you for subscribing to our newsletter!');
} else {
    jsonResponse(false, 'Subscription failed. Please try again.');
}
