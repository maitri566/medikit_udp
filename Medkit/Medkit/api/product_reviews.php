<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

if ($method === 'GET') {
    // Return approved reviews for a product
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    if ($product_id <= 0) {
        jsonResponse(false, 'Product ID is required');
    }

    $stmt = $conn->prepare("SELECT id, product_id, user_name, rating, comment, created_at FROM product_reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC");
    $stmt->bind_param("i", $product_id);

    if (!$stmt->execute()) {
        jsonResponse(false, 'Failed to retrieve reviews');
    }

    $result = $stmt->get_result();

    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    jsonResponse(true, 'Reviews retrieved successfully', $reviews);

} elseif ($method === 'POST') {
    // Submit a new review (pending approval)
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    if (!$data) {
        $data = $_POST;
    }

    $product_id = (int)($data['product_id'] ?? 0);
    $user_name = sanitize($data['user_name'] ?? '');
    $rating = (int)($data['rating'] ?? 0);
    $comment = sanitize($data['comment'] ?? '');

    if ($product_id <= 0) {
        jsonResponse(false, 'Valid product ID is required');
    }
    if (empty($user_name)) {
        jsonResponse(false, 'Name is required');
    }
    if ($rating < 1 || $rating > 5) {
        jsonResponse(false, 'Rating must be between 1 and 5');
    }
    if (empty($comment)) {
        jsonResponse(false, 'Comment is required');
    }

    // If user is logged in, attach their user_id
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, user_name, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("iisis", $product_id, $user_id, $user_name, $rating, $comment);

    if ($stmt->execute()) {
        jsonResponse(true, 'Review submitted successfully! It will appear after admin approval.');
    } else {
        jsonResponse(false, 'Failed to submit review. Please try again.');
    }

} else {
    jsonResponse(false, 'Method not allowed');
}
