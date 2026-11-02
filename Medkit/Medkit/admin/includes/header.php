<?php
require_once __DIR__ . '/../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();

// Get unread messages count
$unreadMsgs = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")->fetch_assoc()['count'];
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];

$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Medkit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-clinic-medical"></i>
            <h2>Medkit Admin</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="products.php" class="<?php echo $page === 'products' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Products
            </a>
            <a href="orders.php" class="<?php echo $page === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> Orders
                <?php if ($pendingOrders > 0): ?>
                    <span class="nav-badge"><?php echo $pendingOrders; ?></span>
                <?php endif; ?>
            </a>
            <a href="users.php" class="<?php echo $page === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="messages.php" class="<?php echo $page === 'messages' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Messages
                <?php if ($unreadMsgs > 0): ?>
                    <span class="nav-badge"><?php echo $unreadMsgs; ?></span>
                <?php endif; ?>
            </a>
            <a href="support.php" class="<?php echo $page === 'support' ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i> Support Tickets
                <?php
                    $openTickets = $conn->query("SELECT COUNT(*) as count FROM support_tickets WHERE ticket_status = 'open'")->fetch_assoc()['count'];
                    if ($openTickets > 0): ?>
                    <span class="nav-badge"><?php echo $openTickets; ?></span>
                <?php endif; ?>
            </a>
            <a href="faq.php" class="<?php echo $page === 'faq' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i> FAQ
            </a>
            <a href="reviews.php" class="<?php echo $page === 'reviews' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Reviews
            </a>
            <a href="newsletter.php" class="<?php echo $page === 'newsletter' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i> Newsletter
            </a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar">
            <h1 id="page-title"><?php echo ucfirst($page); ?></h1>
            <div class="topbar-right">
                <div class="admin-info">
                    <div class="avatar">A</div>
                    <span><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></span>
                </div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="content-area">
