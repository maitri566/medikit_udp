<?php
require_once __DIR__ . '/includes/config.php';
if (!isLoggedIn()) { redirect('login.php'); }

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Medkit</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ── Order form inside profile ── */
        .po-section-label {
            font-size: 0.76rem; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; color: var(--gray);
            margin: 18px 0 12px; display: flex; align-items: center; gap: 8px;
        }
        .po-section-label::after { content:''; flex:1; height:1px; background:#e9ecef; }
        .po-form-group  { margin-bottom: 14px; }
        .po-label { display:block; font-size:0.81rem; font-weight:600; color:#555; margin-bottom:5px; }
        .po-label span { color: var(--danger); margin-left:2px; }
        .po-input {
            width:100%; padding:10px 13px; border:1.5px solid #e0e4e8;
            border-radius:8px; font-size:0.93rem; font-family:inherit; outline:none;
            transition:border-color .2s, box-shadow .2s; background:#fafbfc;
        }
        .po-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(26,147,111,0.1); background:#fff; }
        .po-input.error { border-color: var(--danger); }
        textarea.po-input { min-height:75px; resize:vertical; }
        .po-row { display:flex; gap:12px; }
        .po-row .po-form-group { flex:1; }
        /* pay methods in profile */
        .po-pay-methods { display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
        .po-pay-method {
            flex:1; min-width:110px; display:flex; align-items:center; justify-content:center;
            gap:7px; padding:10px 12px; border:2px solid #e0e4e8; border-radius:10px;
            cursor:pointer; transition:var(--transition); font-weight:600; font-size:0.84rem; color:#555;
        }
        .po-pay-method:hover  { border-color:var(--primary); color:var(--primary); }
        .po-pay-method.active {
            border-color:var(--primary); color:var(--primary);
            background:rgba(26,147,111,0.06); box-shadow:0 0 0 3px rgba(26,147,111,0.1);
        }
        .po-pay-panel {
            display:none; margin-top:14px; background:#f4fdf9;
            border:1.5px solid #c8ead8; border-radius:10px; padding:18px;
        }
        .po-pay-panel.visible { display:block; animation:poFade .22s ease; }
        @keyframes poFade { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
        .po-upi-apps { display:flex; gap:7px; flex-wrap:wrap; margin-bottom:10px; }
        .po-upi-app { padding:4px 12px; border-radius:20px; border:1.5px solid #ddd; font-size:0.76rem; font-weight:600; cursor:pointer; background:#fff; transition:var(--transition); color:#555; }
        .po-upi-app:hover { border-color:var(--primary); color:var(--primary); }
        .po-upi-hint { font-size:0.74rem; color:#888; margin-top:5px; margin-bottom:0; }
        .po-card-row { display:flex; gap:12px; }
        .po-card-row .po-form-group { flex:1; }
        .po-secure { display:flex; align-items:center; gap:6px; font-size:0.74rem; color:#888; margin-top:8px; }
        .po-secure i { color:var(--primary); }
        .po-place-btn {
            width:100%; padding:14px; border:none; border-radius:10px;
            background:var(--gradient); color:#fff; font-size:1rem; font-weight:700;
            cursor:pointer; transition:var(--transition); margin-top:16px;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .po-place-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(26,147,111,0.3); }
        .po-place-btn:disabled { opacity:.7; cursor:not-allowed; transform:none; }
        .po-cart-empty { text-align:center; padding:40px 20px; }
        .po-cart-empty i { font-size:3rem; color:#ddd; display:block; margin-bottom:12px; }
        .po-success { background:#f0fdf6; border:1.5px solid #c8ead8; border-radius:12px; padding:24px; text-align:center; }
        .po-success i { font-size:2.8rem; color:var(--primary); display:block; margin-bottom:10px; }
        .po-success h4 { color:var(--primary); font-size:1.15rem; margin-bottom:6px; }
        @media(max-width:600px){ .po-row{flex-direction:column;} .po-card-row{flex-direction:column;} .po-pay-method{min-width:100%;} }
    </style>
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
                <a href="profile.php"><i class="fas fa-user-check"></i> <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></a>
                <a href="api/logout.php"><i class="fas fa-sign-out-alt"></i></a>
            </div>
            <div class="hamburger"><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>
        </div>
    </header>

    <section class="page-banner">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <p>Manage your account and orders</p>
        <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> Profile</div>
    </section>

    <section class="profile-section">
        <div class="container">
            <div class="profile-grid">
                <div class="profile-sidebar">
                    <div class="profile-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="profile-menu">
                        <a href="#profile-info" class="active" onclick="showTab('profile-info', this)"><i class="fas fa-id-card"></i> Profile Info</a>
                        <a href="#order-history" onclick="showTab('order-history', this)"><i class="fas fa-shopping-bag"></i> Order History</a>
                        <a href="#place-order" onclick="showTab('place-order', this)"><i class="fas fa-cart-plus"></i> Place Order</a>
                        <a href="#edit-profile" onclick="showTab('edit-profile', this)"><i class="fas fa-edit"></i> Edit Profile</a>
                        <a href="track-order.php"><i class="fas fa-truck-moving"></i> Track Order</a>
                        <a href="help-centre.php"><i class="fas fa-headset"></i> Help Centre</a>
                        <a href="api/logout.php" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <div class="profile-content">
                    <!-- Profile Info -->
                    <div id="profile-info" class="tab-pane">
                        <h3><i class="fas fa-id-card"></i> Profile Information</h3>
                        <div style="display:grid; gap:15px;">
                            <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f3f5;">
                                <span style="color:var(--gray); font-weight:600;">Full Name:</span>
                                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f3f5;">
                                <span style="color:var(--gray); font-weight:600;">Email:</span>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f3f5;">
                                <span style="color:var(--gray); font-weight:600;">Phone:</span>
                                <span><?php echo htmlspecialchars($user['phone']); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f3f5;">
                                <span style="color:var(--gray); font-weight:600;">Member Since:</span>
                                <span><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order History -->
                    <div id="order-history" class="tab-pane" style="display:none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                            <h3><i class="fas fa-shopping-bag"></i> Your Orders</h3>
                            <a href="index.php#products" class="btn primary-btn" style="padding:9px 20px; font-size:0.88rem; border-radius:25px;">
                                <i class="fas fa-plus"></i> Place New Order
                            </a>
                        </div>
                        <div id="orders-list"><p style="text-align:center; color:#999; padding:30px;"><i class="fas fa-spinner fa-spin"></i> Loading orders...</p></div>
                    </div>

                    <!-- ═══════════════════════════════
                         Place Order Tab
                    ════════════════════════════════ -->
                    <div id="place-order" class="tab-pane" style="display:none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:8px;">
                            <h3><i class="fas fa-cart-plus"></i> Place an Order</h3>
                            <a href="index.php#products" style="font-size:0.82rem; color:var(--primary); font-weight:600;">
                                <i class="fas fa-search"></i> Browse Products
                            </a>
                        </div>

                        <div id="po-cart-check">
                            <!-- Cart check injected by JS -->
                        </div>
                    </div>

                    <!-- Edit Profile -->
                    <div id="edit-profile" class="tab-pane" style="display:none;">
                        <h3><i class="fas fa-edit"></i> Edit Profile</h3>
                        <div id="edit-msg" style="display:none; padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:0.9rem;"></div>
                        <form id="edit-form">
                            <div class="form-group">
                                <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Full Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Phone</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Address</label>
                                <textarea name="address" style="min-height:80px;" placeholder="Enter your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn primary-btn"><i class="fas fa-save"></i> Save Changes</button>
                        </form>
                    </div>
                </div>
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
    function showTab(id, el) {
        document.querySelectorAll('.tab-pane').forEach(t => t.style.display = 'none');
        document.getElementById(id).style.display = 'block';
        document.querySelectorAll('.profile-menu a').forEach(a => a.classList.remove('active'));
        el.classList.add('active');
    }

    // Load orders
    const payIcons = { cod: '💵', upi: '📱', card: '💳' };
    const payLabels = { cod: 'Cash on Delivery', upi: 'UPI', card: 'Card' };
    const statusColors = {
        pending:    { bg:'#fff3cd', color:'#856404' },
        processing: { bg:'#cfe2ff', color:'#084298' },
        shipped:    { bg:'#d1ecf1', color:'#0c5460' },
        delivered:  { bg:'#d4edda', color:'#155724' },
        cancelled:  { bg:'#f8d7da', color:'#721c24' },
    };

    fetch('api/user_orders.php').then(r => r.json()).then(data => {
        const list = document.getElementById('orders-list');
        if (data.success && data.data.length > 0) {
            list.innerHTML = data.data.map(o => {
                const sc = statusColors[o.status] || { bg:'#f1f3f5', color:'#555' };
                const payIcon  = payIcons[o.payment_method]  || '💰';
                const payLabel = payLabels[o.payment_method] || o.payment_method.toUpperCase();
                const dateStr  = new Date(o.created_at).toLocaleDateString('en-IN', { day:'numeric', month:'short', year:'numeric' });
                const timeStr  = new Date(o.created_at).toLocaleTimeString('en-IN', { hour:'2-digit', minute:'2-digit' });

                const itemsHTML = o.order_items.slice(0,3).map(item => `
                    <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f1f3f5;">
                        <img src="${item.product_image}" alt="${item.product_title}"
                             style="width:44px; height:44px; border-radius:8px; object-fit:cover; border:1px solid #eee;"
                             onerror="this.src='images/placeholder.jpg'">
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:0.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${item.product_title}</p>
                            <p style="font-size:0.78rem; color:#888;">Qty: ${item.quantity} &times; ₹${parseFloat(item.price).toFixed(2)}</p>
                        </div>
                        <span style="font-size:0.88rem; font-weight:700; color:var(--primary); white-space:nowrap;">₹${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `).join('');

                const extraItems = o.order_items.length > 3
                    ? `<p style="font-size:0.78rem; color:#888; margin-top:6px;">+${o.order_items.length - 3} more item(s)</p>` : '';

                return `
                <div style="border:1px solid #e9ecef; border-radius:14px; overflow:hidden; margin-bottom:18px; box-shadow:0 2px 10px rgba(0,0,0,0.04);">
                    <!-- Order Header -->
                    <div style="background:#f8f9fa; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; border-bottom:1px solid #e9ecef;">
                        <div>
                            <span style="font-weight:700; font-size:0.95rem;">Order #${o.id}</span>
                            <span style="color:#aaa; font-size:0.78rem; margin-left:10px;">${dateStr} at ${timeStr}</span>
                        </div>
                        <span style="padding:4px 14px; border-radius:20px; font-size:0.75rem; font-weight:700; background:${sc.bg}; color:${sc.color};">
                            ${o.status.charAt(0).toUpperCase() + o.status.slice(1)}
                        </span>
                    </div>
                    <!-- Items -->
                    <div style="padding:14px 18px;">
                        ${itemsHTML}
                        ${extraItems}
                    </div>
                    <!-- Footer -->
                    <div style="background:#f8f9fa; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-top:1px solid #e9ecef;">
                        <div style="font-size:0.85rem; color:#666;">
                            ${payIcon} <strong>${payLabel}</strong>
                            &nbsp;&bull;&nbsp; ${o.order_items.length} item${o.order_items.length > 1 ? 's' : ''}
                        </div>
                        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            <span style="font-size:1rem; font-weight:700; color:var(--primary);">₹${parseFloat(o.total_amount).toFixed(2)}</span>
                            <a href="track-order.php?id=${o.id}" class="btn secondary-btn" style="padding:6px 14px; font-size:0.8rem; border-radius:20px;">
                                <i class="fas fa-truck"></i> Track
                            </a>
                        </div>
                    </div>
                </div>`;
            }).join('');
        } else {
            list.innerHTML = `
                <div style="text-align:center; padding:40px 20px; color:#999;">
                    <i class="fas fa-shopping-bag" style="font-size:3.5rem; color:#ddd; margin-bottom:15px; display:block;"></i>
                    <p style="font-size:1rem; margin-bottom:16px;">No orders yet.</p>
                    <a href="index.php#products" class="btn primary-btn" style="padding:10px 24px; border-radius:25px;">
                        <i class="fas fa-shopping-cart"></i> Shop Now
                    </a>
                </div>`;
        }
    }).catch(() => {
        document.getElementById('orders-list').innerHTML = '<p style="text-align:center; color:#e63946; padding:20px;">Failed to load orders. Please try again.</p>';
    });

    // Edit profile
    document.getElementById('edit-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch('api/update_profile.php', { method: 'POST', body: formData });
            const data = await response.json();
            const msg = document.getElementById('edit-msg');
            msg.style.display = 'block';
            msg.textContent = data.message;
            msg.style.background = data.success ? '#d4edda' : '#fee';
            msg.style.color = data.success ? '#155724' : '#721c24';
            if (data.success) setTimeout(() => location.reload(), 1500);
        } catch (err) { alert('Error updating profile.'); }
    });

    /* ═══════════════════════════════════════════════
       PLACE ORDER TAB — full shipping + payment form
    ═══════════════════════════════════════════════ */
    const statesList = ["Andhra Pradesh","Arunachal Pradesh","Assam","Bihar","Chhattisgarh","Goa","Gujarat","Haryana","Himachal Pradesh","Jharkhand","Karnataka","Kerala","Madhya Pradesh","Maharashtra","Manipur","Meghalaya","Mizoram","Nagaland","Odisha","Punjab","Rajasthan","Sikkim","Tamil Nadu","Telangana","Tripura","Uttar Pradesh","Uttarakhand","West Bengal","Andaman and Nicobar Islands","Chandigarh","Dadra and Nagar Haveli","Daman and Diu","Delhi","Lakshadweep","Puducherry","Jammu and Kashmir","Ladakh"];
    const statesOptions = statesList.map(s => `<option value="${s}">${s}</option>`).join('');

    function buildPlaceOrderForm(cartItems) {
        const subtotal = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
        const shipping = subtotal >= 500 ? 0 : 49;
        const total    = subtotal + shipping;

        return `
        <div style="background:#f0fdf6; border-radius:10px; padding:14px 18px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <span style="font-size:0.88rem; color:#444;"><i class="fas fa-shopping-cart" style="color:var(--primary);"></i> <strong>${cartItems.length}</strong> item(s) in cart &nbsp;|&nbsp; Subtotal: <strong>₹${subtotal.toFixed(2)}</strong> &nbsp;|&nbsp; Shipping: <strong>${shipping === 0 ? '🎉 FREE' : '₹'+shipping.toFixed(2)}</strong> &nbsp;|&nbsp; Total: <strong style="color:var(--primary);">₹${total.toFixed(2)}</strong></span>
            <a href="index.php#products" style="font-size:0.8rem; color:var(--primary); font-weight:600;"><i class="fas fa-plus"></i> Add More</a>
        </div>

        <!-- Items preview -->
        <div style="margin-bottom:18px;">
            ${cartItems.slice(0,4).map(item=>`
            <div style="display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid #f1f3f5;">
                <img src="${item.image}" style="width:38px;height:38px;border-radius:7px;object-fit:cover;" onerror="this.src='images/paracetamol.jpg'" alt="">
                <div style="flex:1;min-width:0;"><p style="font-size:0.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${item.title}</p><p style="font-size:0.76rem;color:#888;">Qty: ${item.quantity} × ₹${item.price.toFixed(2)}</p></div>
                <span style="font-size:0.84rem;font-weight:700;color:var(--primary);">₹${(item.price*item.quantity).toFixed(2)}</span>
            </div>`).join('')}
            ${cartItems.length > 4 ? `<p style="font-size:0.76rem;color:#888;margin-top:6px;">+${cartItems.length-4} more item(s)</p>` : ''}
        </div>

        <form id="po-form">

            <p class="po-section-label"><i class="fas fa-user" style="color:var(--primary);"></i> Personal Information</p>
            <div class="po-row">
                <div class="po-form-group">
                    <label class="po-label">Full Name <span>*</span></label>
                    <input class="po-input" type="text" name="customer_name" value="<?php echo addslashes(htmlspecialchars($user['full_name'], ENT_QUOTES)); ?>" required placeholder="Your full name">
                </div>
                <div class="po-form-group">
                    <label class="po-label">Phone Number <span>*</span></label>
                    <input class="po-input" type="tel" name="customer_phone" value="<?php echo addslashes(htmlspecialchars($user['phone'], ENT_QUOTES)); ?>" maxlength="10" pattern="[6-9][0-9]{9}" required placeholder="10-digit mobile">
                </div>
            </div>
            <div class="po-form-group">
                <label class="po-label">Email Address <span>*</span></label>
                <input class="po-input" type="email" name="customer_email" value="<?php echo addslashes(htmlspecialchars($user['email'], ENT_QUOTES)); ?>" required placeholder="you@example.com">
            </div>

            <p class="po-section-label"><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Shipping Address</p>
            <div class="po-row">
                <div class="po-form-group">
                    <label class="po-label">House / Flat / Floor No. <span>*</span></label>
                    <input class="po-input" type="text" name="house_no" required placeholder="e.g. B-204, 3rd Floor">
                </div>
                <div class="po-form-group">
                    <label class="po-label">Building / Society <span>*</span></label>
                    <input class="po-input" type="text" name="building" required placeholder="e.g. Sunshine Apartments">
                </div>
            </div>
            <div class="po-form-group">
                <label class="po-label">Street / Road Name <span>*</span></label>
                <input class="po-input" type="text" name="street" required placeholder="e.g. MG Road, Near Metro Station">
            </div>
            <div class="po-row">
                <div class="po-form-group">
                    <label class="po-label">Area / Colony / Locality <span>*</span></label>
                    <input class="po-input" type="text" name="area" required placeholder="e.g. Andheri West">
                </div>
                <div class="po-form-group">
                    <label class="po-label">Landmark</label>
                    <input class="po-input" type="text" name="landmark" placeholder="e.g. Near Big Bazaar">
                </div>
            </div>
            <div class="po-row">
                <div class="po-form-group">
                    <label class="po-label">City / District <span>*</span></label>
                    <input class="po-input" type="text" name="city" required placeholder="e.g. Mumbai">
                </div>
                <div class="po-form-group">
                    <label class="po-label">State <span>*</span></label>
                    <select class="po-input" name="state" required>
                        <option value="">-- Select State --</option>
                        ${statesOptions}
                    </select>
                </div>
            </div>
            <div class="po-row">
                <div class="po-form-group">
                    <label class="po-label">PIN Code <span>*</span></label>
                    <input class="po-input" type="text" name="pincode" maxlength="6" pattern="[1-9][0-9]{5}" required placeholder="6-digit PIN">
                </div>
                <div class="po-form-group">
                    <label class="po-label">Country</label>
                    <input class="po-input" type="text" value="India" readonly style="background:#f0f0f0;color:#888;">
                </div>
            </div>
            <div class="po-form-group">
                <label class="po-label">Delivery Instructions <small style="font-weight:400;">(optional)</small></label>
                <textarea class="po-input" name="delivery_notes" placeholder="e.g. Leave at door, call before delivery…"></textarea>
            </div>

            <p class="po-section-label"><i class="fas fa-credit-card" style="color:var(--primary);"></i> Payment Method</p>
            <div class="po-pay-methods">
                <div class="po-pay-method active" data-method="cod" onclick="poSelectPay(this)">
                    <i class="fas fa-money-bill-wave" style="color:#27ae60;"></i> Cash on Delivery
                </div>
                <div class="po-pay-method" data-method="upi" onclick="poSelectPay(this)">
                    <i class="fas fa-mobile-alt" style="color:#6c47ff;"></i> UPI
                </div>
                <div class="po-pay-method" data-method="card" onclick="poSelectPay(this)">
                    <i class="fas fa-credit-card" style="color:#2196f3;"></i> Card
                </div>
            </div>
            <input type="hidden" name="payment_method" id="po-payment-method" value="cod">

            <!-- UPI Panel -->
            <div class="po-pay-panel" id="po-upi-panel">
                <label class="po-label"><i class="fas fa-at"></i> UPI ID <span>*</span></label>
                <input class="po-input" type="text" id="po-upi-id" name="upi_id" placeholder="e.g. yourname@okaxis or 9876543210@paytm" autocomplete="off">
                <p class="po-upi-hint"><i class="fas fa-info-circle"></i> Enter your Google Pay, PhonePe, Paytm or any UPI ID</p>
                <div style="margin-top:12px;">
                    <p style="font-size:0.73rem; color:#888; margin-bottom:6px; font-weight:600;">Quick fill — tap your app:</p>
                    <div class="po-upi-apps">
                        <button type="button" class="po-upi-app" onclick="poFillUpi('@okaxis')">GPay</button>
                        <button type="button" class="po-upi-app" onclick="poFillUpi('@ybl')">PhonePe</button>
                        <button type="button" class="po-upi-app" onclick="poFillUpi('@paytm')">Paytm</button>
                        <button type="button" class="po-upi-app" onclick="poFillUpi('@upi')">BHIM</button>
                        <button type="button" class="po-upi-app" onclick="poFillUpi('@ibl')">ICICI</button>
                    </div>
                </div>
            </div>

            <!-- Card Panel -->
            <div class="po-pay-panel" id="po-card-panel">
                <label class="po-label"><i class="fas fa-credit-card"></i> Card Number <span>*</span></label>
                <input class="po-input" type="text" id="po-card-number" name="card_number" placeholder="1234  5678  9012  3456" maxlength="19" autocomplete="cc-number">
                <div class="po-row" style="margin-top:12px;">
                    <div class="po-form-group">
                        <label class="po-label">Card Holder Name <span>*</span></label>
                        <input class="po-input" type="text" id="po-card-name" name="card_name" placeholder="Name as on card" autocomplete="cc-name">
                    </div>
                    <div class="po-form-group">
                        <label class="po-label">Card Type</label>
                        <select class="po-input" name="card_type">
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                            <option value="rupay">RuPay</option>
                            <option value="amex">Amex</option>
                        </select>
                    </div>
                </div>
                <div class="po-card-row" style="margin-top:12px;">
                    <div class="po-form-group">
                        <label class="po-label">Expiry Date <span>*</span></label>
                        <input class="po-input" type="text" id="po-card-expiry" name="card_expiry" placeholder="MM / YY" maxlength="7" autocomplete="cc-exp">
                    </div>
                    <div class="po-form-group">
                        <label class="po-label">CVV <span>*</span></label>
                        <input class="po-input" type="password" id="po-card-cvv" name="card_cvv" placeholder="•••" maxlength="4" autocomplete="cc-csc">
                    </div>
                </div>
                <div class="po-secure"><i class="fas fa-lock"></i> 256-bit SSL &nbsp;|&nbsp; <i class="fas fa-shield-alt" style="color:#2196f3;"></i> PCI DSS Compliant</div>
            </div>

            <button type="submit" class="po-place-btn">
                <i class="fas fa-check-circle"></i> Place Order &nbsp;·&nbsp; ₹${total.toFixed(2)}
            </button>
        </form>`;
    }

    /* ── Render Place Order tab on first activation ── */
    let poRendered = false;
    const _origShowTab = showTab;
    showTab = function(id, el) {
        _origShowTab(id, el);
        if (id === 'place-order' && !poRendered) {
            poRendered = true;
            renderPlaceOrder();
        }
    };

    function renderPlaceOrder() {
        const cartItems = JSON.parse(localStorage.getItem('medkit_cart') || '[]');
        const box = document.getElementById('po-cart-check');
        if (!cartItems || cartItems.length === 0) {
            box.innerHTML = `
                <div class="po-cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p style="font-size:1rem; color:#888; margin-bottom:16px;">Your cart is empty. Add products first.</p>
                    <a href="index.php#products" class="btn primary-btn" style="border-radius:25px; padding:10px 26px;">
                        <i class="fas fa-search"></i> Shop Products
                    </a>
                </div>`;
            return;
        }
        box.innerHTML = buildPlaceOrderForm(cartItems);
        attachPoFormEvents(cartItems);
    }

    function attachPoFormEvents(cartItems) {
        const subtotal = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
        const shipping = subtotal >= 500 ? 0 : 49;
        const total    = subtotal + shipping;

        /* phone / pincode digit-only */
        document.getElementById('po-form').addEventListener('input', (e) => {
            if (e.target.name === 'customer_phone') e.target.value = e.target.value.replace(/\D/g,'').substring(0,10);
            if (e.target.name === 'pincode')         e.target.value = e.target.value.replace(/\D/g,'').substring(0,6);
            if (e.target.id   === 'po-card-number') {
                let v = e.target.value.replace(/\D/g,'').substring(0,16);
                e.target.value = v.replace(/(.{4})/g,'$1  ').trim();
            }
            if (e.target.id === 'po-card-expiry') {
                let v = e.target.value.replace(/\D/g,'').substring(0,4);
                if (v.length >= 2) v = v.substring(0,2) + ' / ' + v.substring(2);
                e.target.value = v;
            }
        });

        document.getElementById('po-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const method = fd.get('payment_method');

            /* Validations */
            const phone = fd.get('customer_phone').replace(/\D/g,'');
            if (phone.length !== 10) { poErr('customer_phone', 'Enter valid 10-digit number.'); return; }
            const pin = fd.get('pincode').replace(/\D/g,'');
            if (pin.length !== 6) { poErr('pincode', 'Enter valid 6-digit PIN code.'); return; }

            if (method === 'upi') {
                const uid = fd.get('upi_id').trim();
                if (!uid || !/@/.test(uid)) { poErr('upi_id', 'Enter a valid UPI ID (e.g. name@upi).'); return; }
            }
            if (method === 'card') {
                if (fd.get('card_number').replace(/\s/g,'').length < 15) { poErr('card_number', 'Enter valid card number.'); return; }
                if (!fd.get('card_name').trim()) { poErr('card_name', 'Enter card holder name.'); return; }
                if (!/^\d{2}\s*\/\s*\d{2}$/.test(fd.get('card_expiry'))) { poErr('card_expiry', 'Enter expiry as MM / YY.'); return; }
                if (fd.get('card_cvv').length < 3) { poErr('card_cvv', 'Enter valid CVV.'); return; }
            }

            const parts = [
                fd.get('house_no'), fd.get('building'), fd.get('street'), fd.get('area'),
                fd.get('landmark') ? 'Near ' + fd.get('landmark') : null,
                fd.get('city'), fd.get('state'), 'PIN: ' + fd.get('pincode'), 'India'
            ].filter(Boolean);
            const fullAddress = parts.join(', ');

            const orderData = {
                customer_name:    fd.get('customer_name'),
                customer_email:   fd.get('customer_email'),
                customer_phone:   fd.get('customer_phone'),
                shipping_address: fullAddress,
                house_no: fd.get('house_no'), building: fd.get('building'),
                street:   fd.get('street'),   area:     fd.get('area'),
                landmark: fd.get('landmark'), city:     fd.get('city'),
                state:    fd.get('state'),    pincode:  fd.get('pincode'),
                delivery_notes:  fd.get('delivery_notes'),
                payment_method:  method,
                total_amount:    total,
                items: cartItems.map(i => ({
                    id: i.id, title: i.title, image: i.image,
                    price: i.price, quantity: i.quantity
                }))
            };

            const btn = e.target.querySelector('.po-place-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order…';

            try {
                const res  = await fetch('api/checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem('medkit_cart');
                    const cc = document.getElementById('cart-count');
                    if (cc) cc.textContent = '0';

                    document.getElementById('po-cart-check').innerHTML = `
                        <div class="po-success">
                            <i class="fas fa-check-circle"></i>
                            <h4>Order Placed Successfully!</h4>
                            <p style="color:#555; font-size:0.9rem; margin-bottom:4px;">Order ID: <strong>#${data.data.order_id}</strong></p>
                            <p style="color:#555; font-size:0.9rem; margin-bottom:4px;">Total: <strong>₹${total.toFixed(2)}</strong></p>
                            <p style="color:#555; font-size:0.9rem; margin-bottom:14px;">Payment: <strong>${method === 'cod' ? 'Cash on Delivery' : method === 'upi' ? 'UPI' : 'Card'}</strong></p>
                            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                                <a href="track-order.php?id=${data.data.order_id}" class="btn primary-btn" style="padding:8px 20px; border-radius:25px; font-size:0.85rem;">
                                    <i class="fas fa-truck"></i> Track Order
                                </a>
                                <button onclick="showTab('order-history', document.querySelector('[href=\\'#order-history\\']')); location.reload();"
                                        class="btn secondary-btn" style="padding:8px 20px; border-radius:25px; font-size:0.85rem;">
                                    <i class="fas fa-list-alt"></i> My Orders
                                </button>
                            </div>
                        </div>`;
                    poRendered = false; /* allow re-render next time */
                } else {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="fas fa-check-circle"></i> Place Order &nbsp;·&nbsp; ₹${total.toFixed(2)}`;
                    alert(data.message || 'Failed to place order. Please try again.');
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-check-circle"></i> Place Order &nbsp;·&nbsp; ₹${total.toFixed(2)}`;
                alert('Network error. Please try again.');
            }
        });
    }

    function poSelectPay(el) {
        el.closest('.po-pay-methods').querySelectorAll('.po-pay-method').forEach(m => m.classList.remove('active'));
        el.classList.add('active');
        const method = el.getAttribute('data-method');
        document.getElementById('po-payment-method').value = method;
        document.getElementById('po-upi-panel').classList.toggle('visible',  method === 'upi');
        document.getElementById('po-card-panel').classList.toggle('visible', method === 'card');
    }
    function poFillUpi(suffix) {
        const f = document.getElementById('po-upi-id');
        if (!f) return;
        let base = f.value.includes('@') ? f.value.split('@')[0] : f.value;
        f.value = base + suffix; f.focus();
    }
    function poErr(name, msg) {
        const el = document.getElementById(name) || document.querySelector(`#po-form [name="${name}"]`);
        if (!el) { alert(msg); return; }
        el.classList.add('error'); el.focus();
        let err = el.nextElementSibling;
        if (!err || !err.classList.contains('po-field-err')) {
            err = document.createElement('p');
            err.className = 'po-field-err';
            err.style.cssText = 'color:var(--danger);font-size:0.74rem;margin-top:3px;';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
        err.textContent = msg;
        el.addEventListener('input', () => { el.classList.remove('error'); if(err) err.remove(); }, { once: true });
    }
    </script>
</body>
</html>
