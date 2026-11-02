<?php
require_once __DIR__ . '/includes/header.php';
$conn = getDBConnection();

// Get statistics
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalMessages = $conn->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'];
$totalTickets = $conn->query("SELECT COUNT(*) as count FROM support_tickets")->fetch_assoc()['count'];
$pendingReviews = $conn->query("SELECT COUNT(*) as count FROM product_reviews WHERE is_approved = 0")->fetch_assoc()['count'];

// Get total revenue
$revenueResult = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'");
$totalRevenue = $revenueResult->fetch_assoc()['revenue'] ?? 0;

// Get recent orders
$recentOrders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");

// Get low stock products
$lowStock = $conn->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
            <h3>₹<?php echo number_format($totalRevenue, 0); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalProducts; ?></h3>
            <p>Products</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalOrders; ?></h3>
            <p>Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalUsers; ?></h3>
            <p>Users</p>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-clock"></i> Recent Orders</h2>
        <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="panel-body">
        <?php if ($recentOrders->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recentOrders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <p>No orders yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Low Stock Alert -->
<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Products</h2>
        <a href="products.php" class="btn btn-primary btn-sm">Manage Products</a>
    </div>
    <div class="panel-body">
        <?php if ($lowStock->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $lowStock->fetch_assoc()): ?>
                <tr>
                    <td><img src="../<?php echo htmlspecialchars($product['image']); ?>" class="table-img" alt=""></td>
                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                    <td><span style="color: #e74c3c; font-weight: 600;"><?php echo $product['stock']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-check-circle" style="color: #27ae60;"></i>
            <p>All products are well stocked</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-headset"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalTickets; ?></h3>
            <p>Support Tickets</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-star"></i></div>
        <div class="stat-info">
            <h3><?php echo $pendingReviews; ?></h3>
            <p>Pending Reviews</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalMessages; ?></h3>
            <p>Contact Messages</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <?php $subCount = $conn->query("SELECT COUNT(*) as count FROM newsletter_subscribers")->fetch_assoc()['count']; ?>
            <h3><?php echo $subCount; ?></h3>
            <p>Newsletter Subscribers</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
