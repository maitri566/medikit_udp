<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Medkit</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><a href="index.php"><h1><i class="fas fa-clinic-medical"></i> Medkit</h1></a></div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#products">Products</a></li>
                    <li><a href="index.php#categories">Categories</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="help-centre.php">Help Centre</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="header-icons">
                <a href="#" id="cart-icon"><i class="fas fa-shopping-cart"></i> <span id="cart-count">0</span></a>
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php"><i class="fas fa-user-check"></i> <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></a>
                    <a href="api/logout.php"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            </div>
            <div class="hamburger"><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>
        </div>
    </header>

    <section class="page-banner">
        <h1><i class="fas fa-truck-moving"></i> Track Your Order</h1>
        <p>Enter your order ID to check the delivery status</p>
        <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> Track Order</div>
    </section>

    <section class="track-section">
        <div class="container">
            <div class="track-box">
                <div class="track-input-group">
                    <input type="text" id="order-id-input" placeholder="Enter your Order ID (e.g. 101)" value="<?php echo isset($_GET['id']) ? (int)$_GET['id'] : ''; ?>">
                    <button class="btn primary-btn" onclick="trackOrder()"><i class="fas fa-search"></i> Track</button>
                </div>
                <div id="track-result"></div>
            </div>
        </div>
    </section>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-header"><h3>Your Cart</h3><span class="close-cart"><i class="fas fa-times"></i></span></div>
        <div class="cart-items" id="cart-items"></div>
        <div class="cart-footer">
            <div class="cart-total"><span>Total:</span><span id="cart-total-price">₹0.00</span></div>
            <button class="btn primary-btn checkout-btn">Checkout</button>
            <button class="btn secondary-btn continue-shopping">Continue Shopping</button>
        </div>
    </div>
    <div class="overlay"></div>

    <?php include 'footer.php'; ?>
    <script src="js/main.js"></script>
    <script>
    const statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
    const statusLabels = { pending: 'Order Placed', processing: 'Processing', shipped: 'Shipped', delivered: 'Delivered' };
    const statusIcons = { pending: 'fa-check', processing: 'fa-cog', shipped: 'fa-truck', delivered: 'fa-home' };

    async function trackOrder() {
        const orderId = document.getElementById('order-id-input').value.trim();
        const resultDiv = document.getElementById('track-result');
        
        if (!orderId) { resultDiv.innerHTML = '<p style="color:var(--danger); text-align:center;">Please enter an order ID.</p>'; return; }
        
        resultDiv.innerHTML = '<p style="text-align:center; color:#999; padding:30px;">Tracking your order...</p>';
        
        try {
            const response = await fetch('api/track_order.php?order_id=' + orderId);
            const data = await response.json();
            
            if (data.success) {
                const order = data.data;
                const currentStatusIdx = statusOrder.indexOf(order.status);
                const isCancelled = order.status === 'cancelled';
                
                let timelineHTML = '';
                if (isCancelled) {
                    timelineHTML = '<div style="text-align:center; color:var(--danger); padding:20px;"><i class="fas fa-times-circle" style="font-size:2rem;"></i><p style="margin-top:10px;">This order has been cancelled.</p></div>';
                } else {
                    timelineHTML = '<div class="order-status-timeline">';
                    statusOrder.forEach((s, i) => {
                        timelineHTML += `<div class="status-step ${i <= currentStatusIdx ? 'active' : ''}"><div class="step-icon"><i class="fas ${statusIcons[s]}"></i></div><p>${statusLabels[s]}</p></div>`;
                    });
                    timelineHTML += '</div>';
                }
                
                resultDiv.innerHTML = `
                    <div style="background:var(--primary-light); padding:15px; border-radius:12px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                        <div><strong>Order #${order.id}</strong><p style="font-size:0.85rem; color:var(--gray);">${new Date(order.created_at).toLocaleDateString('en-IN', {day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'})}</p></div>
                        <span class="status-badge status-${order.status}" style="padding:6px 15px; border-radius:20px; font-size:0.8rem; font-weight:600;">${order.status.charAt(0).toUpperCase()+order.status.slice(1)}</span>
                    </div>
                    ${timelineHTML}
                    <div class="order-detail-box">
                        <h4><i class="fas fa-shipping-fast"></i> Delivery Details</h4>
                        <p style="font-size:0.9rem; margin-bottom:5px;"><strong>Name:</strong> ${order.customer_name}</p>
                        <p style="font-size:0.9rem; margin-bottom:5px;"><strong>Phone:</strong> ${order.customer_phone}</p>
                        <p style="font-size:0.9rem; margin-bottom:5px;"><strong>Address:</strong> ${order.shipping_address}</p>
                        <p style="font-size:0.9rem; margin-bottom:5px;"><strong>Payment:</strong> ${order.payment_method.toUpperCase()}</p>
                    </div>
                    <div class="order-detail-box" style="margin-top:15px;">
                        <h4><i class="fas fa-box"></i> Order Items (${order.order_items.length})</h4>
                        ${order.order_items.map(item => `
                            <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f1f3f5;">
                                <img src="${item.product_image}" style="width:50px; height:50px; border-radius:8px; object-fit:cover;" alt="">
                                <div style="flex:1;">
                                    <p style="font-weight:600; font-size:0.9rem;">${item.product_title}</p>
                                    <p style="font-size:0.85rem; color:var(--gray);">Qty: ${item.quantity} × ₹${parseFloat(item.price).toFixed(2)}</p>
                                </div>
                                <span style="font-weight:700; color:var(--primary);">₹${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                            </div>
                        `).join('')}
                        <div style="text-align:right; padding-top:10px; font-size:1.1rem; font-weight:700;">Total: ₹${parseFloat(order.total_amount).toFixed(2)}</div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fas fa-search" style="font-size:3rem; color:#ddd;"></i><p style="margin-top:15px; color:#999;">Order not found. Please check your Order ID.</p></div>';
            }
        } catch (err) {
            resultDiv.innerHTML = '<p style="color:var(--danger); text-align:center;">Error tracking order. Please try again.</p>';
        }
    }

    // Auto-track if ID in URL
    <?php if (isset($_GET['id'])): ?>
    trackOrder();
    <?php endif; ?>
    </script>
</body>
</html>
