<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'You must be logged in to view your orders');
}

$user_id = (int)$_SESSION['user_id'];
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, user_id, customer_name, customer_email, customer_phone, shipping_address, payment_method, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    jsonResponse(false, 'Failed to retrieve orders');
}

$result = $stmt->get_result();

$orders = [];
while ($order = $result->fetch_assoc()) {
    $order_id = (int)$order['id'];

    // Fetch order items for this order
    $itemStmt = $conn->prepare("SELECT id, product_id, product_title, product_image, price, quantity FROM order_items WHERE order_id = ?");
    $itemStmt->bind_param("i", $order_id);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    $items = [];
    while ($item = $itemResult->fetch_assoc()) {
        $items[] = $item;
    }

    $order['order_items'] = $items;
    $orders[] = $order;
}

jsonResponse(true, 'Orders retrieved successfully', $orders);
