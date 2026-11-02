<?php
require_once __DIR__ . '/../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$id = (int)($_GET['id'] ?? 0);

$order = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order->bind_param("i", $id);
$order->execute();
$orderResult = $order->get_result()->fetch_assoc();

if (!$orderResult) {
    echo "<p>Order not found.</p>";
    exit;
}

$items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->bind_param("i", $id);
$items->execute();
$itemsResult = $items->get_result();
?>
<h4 style="margin-bottom:15px;">Order #<?php echo $orderResult['id']; ?></h4>
<table style="margin-bottom:20px;">
    <tr>
        <td style="padding:8px; font-weight:600;">Customer:</td>
        <td style="padding:8px;"><?php echo htmlspecialchars($orderResult['customer_name']); ?></td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:600;">Email:</td>
        <td style="padding:8px;"><?php echo htmlspecialchars($orderResult['customer_email']); ?></td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:600;">Phone:</td>
        <td style="padding:8px;"><?php echo htmlspecialchars($orderResult['customer_phone']); ?></td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:600;">Address:</td>
        <td style="padding:8px;"><?php echo htmlspecialchars($orderResult['shipping_address']); ?></td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:600;">Payment:</td>
        <td style="padding:8px;"><?php echo strtoupper($orderResult['payment_method']); ?></td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:600;">Status:</td>
        <td style="padding:8px;"><span class="status-badge status-<?php echo $orderResult['status']; ?>"><?php echo ucfirst($orderResult['status']); ?></span></td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:600;">Date:</td>
        <td style="padding:8px;"><?php echo date('d M Y, h:i A', strtotime($orderResult['created_at'])); ?></td>
    </tr>
</table>

<h4 style="margin-bottom:10px;">Order Items</h4>
<table>
    <thead>
        <tr>
            <th style="padding:10px;">Image</th>
            <th style="padding:10px;">Product</th>
            <th style="padding:10px;">Price</th>
            <th style="padding:10px;">Qty</th>
            <th style="padding:10px;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $itemsResult->fetch_assoc()): ?>
        <tr>
            <td style="padding:10px;"><img src="../<?php echo htmlspecialchars($item['product_image']); ?>" class="table-img" alt=""></td>
            <td style="padding:10px;"><?php echo htmlspecialchars($item['product_title']); ?></td>
            <td style="padding:10px;">₹<?php echo number_format($item['price'], 2); ?></td>
            <td style="padding:10px;"><?php echo $item['quantity']; ?></td>
            <td style="padding:10px;">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<div style="text-align:right; padding:15px; font-size:1.2rem; font-weight:700;">
    Total: ₹<?php echo number_format($orderResult['total_amount'], 2); ?>
</div>
