<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = (int)$_POST['id'];
    $status = sanitize($_POST['status']);
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    echo "<script>alert('Order status updated!');</script>";
}

// Get orders
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$sql = "SELECT * FROM orders";
if ($statusFilter) {
    $sql .= " WHERE status = '$statusFilter'";
}
$sql .= " ORDER BY created_at DESC";
$orders = $conn->query($sql);
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-shopping-bag"></i> Orders (<?php echo $orders->num_rows; ?>)</h2>
        <div style="display:flex; gap:10px;">
            <select id="statusFilter" class="search-box" onchange="filterStatus()">
                <option value="">All Orders</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
    </div>
    <div class="panel-body">
        <?php if ($orders->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </td>
                    <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewOrder(<?php echo $order['id']; ?>)"><i class="fas fa-eye"></i></button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                            <select name="status" onchange="this.form.submit()" style="padding:5px; border-radius:5px; font-size:0.8rem; cursor:pointer;">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <p>No orders found</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal-overlay" id="orderModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="close-modal" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="modal-body" id="orderDetailsBody">
            <p>Loading...</p>
        </div>
    </div>
</div>

<script>
function filterStatus() {
    const val = document.getElementById('statusFilter').value;
    window.location.href = val ? '?status=' + val : 'orders.php';
}

function viewOrder(id) {
    fetch('get_order_details.php?id=' + id)
        .then(r => r.text())
        .then(html => {
            document.getElementById('orderDetailsBody').innerHTML = html;
            document.getElementById('orderModal').classList.add('show');
        });
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('show');
}

document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) closeOrderModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
