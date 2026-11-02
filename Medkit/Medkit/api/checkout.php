<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

/* ── Parse JSON body ── */
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { $data = $_POST; }

/* ── Required fields ── */
$customer_name    = trim($data['customer_name']    ?? '');
$customer_email   = trim($data['customer_email']   ?? '');
$customer_phone   = trim($data['customer_phone']   ?? '');
$shipping_address = trim($data['shipping_address'] ?? '');
$payment_method   = trim($data['payment_method']   ?? 'cod');
$items            = $data['items']         ?? [];
$total_amount     = (float)($data['total_amount'] ?? 0);

/* ── Extra address fields (stored for reference) ── */
$house_no       = trim($data['house_no']        ?? '');
$building       = trim($data['building']        ?? '');
$street         = trim($data['street']          ?? '');
$area           = trim($data['area']            ?? '');
$landmark       = trim($data['landmark']        ?? '');
$city           = trim($data['city']            ?? '');
$state          = trim($data['state']           ?? '');
$pincode        = trim($data['pincode']         ?? '');
$delivery_notes = trim($data['delivery_notes']  ?? '');

/* ── Basic validation ── */
if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($shipping_address)) {
    jsonResponse(false, 'Please fill in all required fields.');
}
if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}
if (!preg_match('/^[6-9][0-9]{9}$/', $customer_phone)) {
    jsonResponse(false, 'Please enter a valid 10-digit Indian mobile number.');
}
if ($pincode && !preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
    jsonResponse(false, 'Please enter a valid 6-digit PIN code.');
}
if (empty($items) || $total_amount <= 0) {
    jsonResponse(false, 'Cart is empty or total is invalid.');
}
$allowed_methods = ['cod', 'upi', 'card'];
if (!in_array($payment_method, $allowed_methods)) {
    $payment_method = 'cod';
}

/* ── If address fields sent individually but shipping_address is empty, build it ── */
if (empty($shipping_address) && (!empty($house_no) || !empty($street))) {
    $parts = array_filter([$house_no, $building, $street, $area,
                           ($landmark ? 'Near '.$landmark : ''),
                           $city, $state, 'PIN: '.$pincode, 'India']);
    $shipping_address = implode(', ', $parts);
}

$conn = getDBConnection();
$conn->begin_transaction();

try {
    $user_id = isLoggedIn() ? (int)$_SESSION['user_id'] : null;

    /* ── Insert order ── */
    $stmt = $conn->prepare(
        "INSERT INTO orders
            (user_id, customer_name, customer_email, customer_phone,
             shipping_address, payment_method, total_amount, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->bind_param(
        "isssssd",
        $user_id, $customer_name, $customer_email, $customer_phone,
        $shipping_address, $payment_method, $total_amount
    );
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    $order_id = $conn->insert_id;

    /* ── Insert order items ── */
    $iStmt = $conn->prepare(
        "INSERT INTO order_items
            (order_id, product_id, product_title, product_image, price, quantity)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($items as $item) {
        $pid   = (int)($item['id']       ?? 0);
        $title = substr(strip_tags($item['title'] ?? ''), 0, 255);
        $image = substr(strip_tags($item['image'] ?? ''), 0, 255);
        $price = (float)($item['price']    ?? 0);
        $qty   = max(1, (int)($item['quantity'] ?? 1));

        $iStmt->bind_param("iissdi", $order_id, $pid, $title, $image, $price, $qty);
        if (!$iStmt->execute()) {
            throw new Exception('Failed to insert order item: ' . $iStmt->error);
        }
    }

    /* ── Optionally save delivery notes in orders (if column exists, ignore gracefully) ── */
    if (!empty($delivery_notes)) {
        $conn->query(
            "UPDATE orders SET shipping_address = CONCAT(shipping_address, '\nNotes: " .
            $conn->real_escape_string($delivery_notes) . "') WHERE id = " . (int)$order_id
        );
    }

    $conn->commit();
    jsonResponse(true, 'Order placed successfully!', ['order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(false, 'Failed to place order: ' . $e->getMessage());
}
