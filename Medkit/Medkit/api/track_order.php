<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    jsonResponse(false, 'Valid order ID is required');
}

$conn = getDBConnection();

// Fetch the order (any order, for tracking purposes)
$stmt = $conn->prepare("SELECT id, user_id, customer_name, customer_email, customer_phone, shipping_address, payment_method, total_amount, status, created_at FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);

if (!$stmt->execute()) {
    jsonResponse(false, 'Failed to retrieve order');
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(false, 'Order not found');
}

$order = $result->fetch_assoc();

// Fetch order items
$itemStmt = $conn->prepare("SELECT id, product_id, product_title, product_image, price, quantity FROM order_items WHERE order_id = ?");
$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

$items = [];
while ($item = $itemResult->fetch_assoc()) {
    $items[] = $item;
}

$order['order_items'] = $items;

jsonResponse(true, 'Order details retrieved successfully', $order);
