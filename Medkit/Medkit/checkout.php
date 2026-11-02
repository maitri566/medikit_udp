<?php
require_once __DIR__ . '/includes/config.php';

$userName  = '';
$userEmail = '';
$userPhone = '';
$userAddress = '';

if (isLoggedIn()) {
    $userName  = $_SESSION['user_name'];
    $userEmail = $_SESSION['user_email'];
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT phone, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $userPhone   = $row['phone'];
        $userAddress = $row['address'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Medkit</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ── Layout ── */
        .checkout-section { padding: 40px 0 60px; min-height: 70vh; }
        .checkout-grid    { display: grid; grid-template-columns: 1fr 390px; gap: 28px; align-items: start; }

        /* ── Card base ── */
        .co-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            padding: 28px 30px; margin-bottom: 22px;
        }
        .co-card h3 {
            font-size: 1.05rem; font-weight: 700; color: var(--dark);
            display: flex; align-items: center; gap: 10px;
            padding-bottom: 14px; margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-light);
        }
        .co-card h3 i { color: var(--primary); font-size: 1rem; }

        /* ── Form elements ── */
        .form-group  { margin-bottom: 16px; }
        .form-label  { display: block; font-size: 0.82rem; font-weight: 600;
                       color: #555; margin-bottom: 6px; }
        .form-label span { color: var(--danger); margin-left: 2px; }
        .form-input  {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e0e4e8; border-radius: 8px;
            font-size: 0.95rem; font-family: inherit; outline: none;
            transition: border-color .2s, box-shadow .2s;
            background: #fafbfc;
        }
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,147,111,0.12);
            background: #fff;
        }
        .form-input.error { border-color: var(--danger); }
        textarea.form-input { min-height: 80px; resize: vertical; }
        .form-row   { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; }

        /* ── Section divider ── */
        .section-label {
            font-size: 0.78rem; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; color: var(--gray);
            margin: 4px 0 14px; display: flex; align-items: center; gap: 8px;
        }
        .section-label::after {
            content:''; flex:1; height:1px; background:#e9ecef;
        }

        /* ── Payment methods ── */
        .pay-methods { display: flex; gap: 10px; flex-wrap: wrap; }
        .pay-method  {
            flex: 1; min-width: 130px; display: flex; align-items: center;
            justify-content: center; gap: 8px; padding: 12px 14px;
            border: 2px solid #e0e4e8; border-radius: 10px;
            cursor: pointer; transition: var(--transition);
            font-weight: 600; font-size: 0.88rem; color: #555;
        }
        .pay-method:hover  { border-color: var(--primary); color: var(--primary); }
        .pay-method.active {
            border-color: var(--primary); color: var(--primary);
            background: rgba(26,147,111,0.06);
            box-shadow: 0 0 0 3px rgba(26,147,111,0.1);
        }
        .pay-method i { font-size: 1.1rem; }

        /* ── Payment detail panels ── */
        .pay-panel {
            display: none; margin-top: 16px;
            background: #f4fdf9; border: 1.5px solid #c8ead8;
            border-radius: 10px; padding: 20px 22px;
        }
        .pay-panel.visible { display: block; animation: slideDown .22s ease; }
        @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .pay-panel .form-label { color: #444; }
        .pay-panel .form-input { background: #fff; }
        .upi-apps { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .upi-app-btn {
            padding: 5px 13px; border-radius: 20px; border: 1.5px solid #ddd;
            font-size: 0.78rem; font-weight: 600; cursor: pointer;
            background: #fff; transition: var(--transition); color: #555;
        }
        .upi-app-btn:hover { border-color: var(--primary); color: var(--primary); }
        .upi-hint  { font-size: 0.77rem; color: #888; margin-top: 6px; margin-bottom: 0; }
        .card-row  { display: flex; gap: 12px; }
        .card-row .form-group { flex: 1; }
        .secure-badge {
            display: flex; align-items: center; gap: 6px;
            font-size: 0.77rem; color: #888; margin-top: 10px;
        }
        .secure-badge i { color: var(--primary); }

        /* ── Order summary ── */
        .order-summary { position: sticky; top: 90px; }
        .summary-item  {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid #f1f3f5;
        }
        .summary-item img { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; }
        .summary-item .s-info { flex: 1; }
        .summary-item .s-info h4 { font-size: 0.88rem; font-weight: 600; margin-bottom: 2px; }
        .summary-item .s-info p  { font-size: 0.78rem; color: var(--gray); }
        .summary-item .s-price   { font-weight: 700; color: var(--primary); font-size: 0.9rem; white-space: nowrap; }
        .summary-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; font-size: 0.88rem; color: #555;
        }
        .summary-row.total {
            font-size: 1.1rem; font-weight: 700; color: var(--dark);
            border-top: 2px solid #f1f3f5; margin-top: 6px; padding-top: 12px;
        }
        .summary-row.total span:last-child { color: var(--primary); }

        /* ── Place order button ── */
        .place-order-btn {
            width: 100%; padding: 15px; border: none; border-radius: 10px;
            background: var(--gradient); color: #fff;
            font-size: 1.05rem; font-weight: 700; cursor: pointer;
            transition: var(--transition); margin-top: 16px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .place-order-btn:hover  { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,147,111,0.3); }
        .place-order-btn:disabled { opacity: .7; cursor: not-allowed; transform: none; }

        /* ── Empty cart ── */
        .empty-checkout      { text-align: center; padding: 70px 20px; }
        .empty-checkout i    { font-size: 4.5rem; color: #ddd; margin-bottom: 16px; display: block; }
        .empty-checkout h3   { font-size: 1.4rem; margin-bottom: 8px; }
        .empty-checkout p    { color: #888; margin-bottom: 20px; }

        /* ── Success screen ── */
        .success-box      { text-align: center; padding: 60px 30px; max-width: 560px; margin: 0 auto; }
        .success-box i    { font-size: 5rem; color: var(--primary); margin-bottom: 20px; display: block; }
        .success-box h2   { font-size: 1.8rem; color: var(--primary); margin-bottom: 12px; }
        .success-box p    { font-size: 0.95rem; color: #666; margin-bottom: 6px; }
        .success-detail   {
            background: var(--primary-light); border-radius: 12px;
            padding: 18px 22px; margin: 20px 0; text-align: left;
        }
        .success-detail p { margin-bottom: 6px; font-size: 0.9rem; color: #444; }
        .success-detail strong { color: var(--dark); }
        .success-actions  { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 24px; }

        /* ── Responsive ── */
        @media (max-width: 900px)  { .checkout-grid { grid-template-columns: 1fr; } }
        @media (max-width: 600px)  { .form-row { flex-direction: column; } .card-row { flex-direction: column; } .pay-method { min-width: 100%; } }
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
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php"><i class="fas fa-user-check"></i> <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?></a>
                    <a href="api/logout.php"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-user"></i> Login</a>
                <?php endif; ?>
            </div>
            <div class="hamburger"><div class="bar"></div><div class="bar"></div><div class="bar"></div></div>
        </div>
    </header>

    <section class="page-banner">
        <h1><i class="fas fa-shopping-bag"></i> Checkout</h1>
        <p>Complete your order securely</p>
        <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> Checkout</div>
    </section>

    <section class="checkout-section">
        <div class="container">
            <div id="checkout-content"></div>
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
    /* ─────────────────────────────────────────
       Cart data
    ───────────────────────────────────────── */
    const cartData = JSON.parse(sessionStorage.getItem('checkout_cart') || '[]');

    /* ─────────────────────────────────────────
       Empty cart
    ───────────────────────────────────────── */
    if (cartData.length === 0) {
        document.getElementById('checkout-content').innerHTML = `
            <div class="empty-checkout">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Add some products before checking out.</p>
                <a href="index.php#products" class="btn primary-btn" style="padding:12px 32px; border-radius:30px;">Shop Now</a>
            </div>`;
    } else {

        /* ── Totals ── */
        const subtotal  = cartData.reduce((s, i) => s + i.price * i.quantity, 0);
        const shipping  = subtotal >= 500 ? 0 : 49;
        const total     = subtotal + shipping;

        /* ── Pre-filled user values from PHP ── */
        const preName    = `<?php echo addslashes(htmlspecialchars($userName,  ENT_QUOTES)); ?>`;
        const preEmail   = `<?php echo addslashes(htmlspecialchars($userEmail, ENT_QUOTES)); ?>`;
        const prePhone   = `<?php echo addslashes(htmlspecialchars($userPhone, ENT_QUOTES)); ?>`;

        /* ── Build HTML ── */
        document.getElementById('checkout-content').innerHTML = `
        <div class="checkout-grid">

            <!-- ═══════════ LEFT COLUMN ═══════════ -->
            <div>
                <!-- Personal Info -->
                <div class="co-card">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <form id="checkout-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span>*</span></label>
                            <input class="form-input" type="text" name="customer_name"
                                   value="${preName}" placeholder="e.g. Rohan Mehta" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number <span>*</span></label>
                            <input class="form-input" type="tel" name="customer_phone"
                                   value="${prePhone}" placeholder="10-digit mobile number"
                                   maxlength="10" pattern="[6-9][0-9]{9}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span>*</span></label>
                        <input class="form-input" type="email" name="customer_email"
                               value="${preEmail}" placeholder="you@example.com" required>
                    </div>

                    <!-- Shipping Address -->
                    <div class="section-label" style="margin-top:8px;"><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Shipping Address</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">House / Flat / Floor No. <span>*</span></label>
                            <input class="form-input" type="text" name="house_no"
                                   placeholder="e.g. B-204, 3rd Floor" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Building / Society Name <span>*</span></label>
                            <input class="form-input" type="text" name="building"
                                   placeholder="e.g. Sunshine Apartments" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Street / Road Name <span>*</span></label>
                        <input class="form-input" type="text" name="street"
                               placeholder="e.g. MG Road, Near Metro Station" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Area / Colony / Locality <span>*</span></label>
                            <input class="form-input" type="text" name="area"
                                   placeholder="e.g. Andheri West" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Landmark</label>
                            <input class="form-input" type="text" name="landmark"
                                   placeholder="e.g. Near Big Bazaar">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">City / District <span>*</span></label>
                            <input class="form-input" type="text" name="city"
                                   placeholder="e.g. Mumbai" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State <span>*</span></label>
                            <select class="form-input" name="state" required>
                                <option value="">-- Select State --</option>
                                <option>Andhra Pradesh</option><option>Arunachal Pradesh</option>
                                <option>Assam</option><option>Bihar</option>
                                <option>Chhattisgarh</option><option>Goa</option>
                                <option>Gujarat</option><option>Haryana</option>
                                <option>Himachal Pradesh</option><option>Jharkhand</option>
                                <option>Karnataka</option><option>Kerala</option>
                                <option>Madhya Pradesh</option><option>Maharashtra</option>
                                <option>Manipur</option><option>Meghalaya</option>
                                <option>Mizoram</option><option>Nagaland</option>
                                <option>Odisha</option><option>Punjab</option>
                                <option>Rajasthan</option><option>Sikkim</option>
                                <option>Tamil Nadu</option><option>Telangana</option>
                                <option>Tripura</option><option>Uttar Pradesh</option>
                                <option>Uttarakhand</option><option>West Bengal</option>
                                <option>Andaman and Nicobar Islands</option>
                                <option>Chandigarh</option><option>Dadra and Nagar Haveli</option>
                                <option>Daman and Diu</option><option>Delhi</option>
                                <option>Lakshadweep</option><option>Puducherry</option>
                                <option>Jammu and Kashmir</option><option>Ladakh</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">PIN Code <span>*</span></label>
                            <input class="form-input" type="text" name="pincode"
                                   placeholder="6-digit PIN" maxlength="6"
                                   pattern="[1-9][0-9]{5}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input class="form-input" type="text" name="country"
                                   value="India" readonly style="background:#f0f0f0; color:#888;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Delivery Instructions <small style="font-weight:400;">(optional)</small></label>
                        <textarea class="form-input" name="delivery_notes"
                                  placeholder="e.g. Leave at door, call before delivery, ring bell 2x…"></textarea>
                    </div>

                    <!-- Payment -->
                    <div class="section-label" style="margin-top:8px;"><i class="fas fa-credit-card" style="color:var(--primary);"></i> Payment Method</div>

                    <div class="pay-methods">
                        <div class="pay-method active" data-method="cod" onclick="selectPayment(this)">
                            <i class="fas fa-money-bill-wave" style="color:#27ae60;"></i> Cash on Delivery
                        </div>
                        <div class="pay-method" data-method="upi" onclick="selectPayment(this)">
                            <i class="fas fa-mobile-alt" style="color:#6c47ff;"></i> UPI
                        </div>
                        <div class="pay-method" data-method="card" onclick="selectPayment(this)">
                            <i class="fas fa-credit-card" style="color:#2196f3;"></i> Card
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="payment_method" value="cod">

                    <!-- UPI Panel -->
                    <div class="pay-panel" id="upi-panel">
                        <label class="form-label"><i class="fas fa-at"></i> UPI ID <span style="color:var(--danger);">*</span></label>
                        <input class="form-input" type="text" id="upi_id" name="upi_id"
                               placeholder="e.g. yourname@okaxis or 9876543210@paytm" autocomplete="off">
                        <p class="upi-hint"><i class="fas fa-info-circle"></i>&nbsp;Enter your Google Pay, PhonePe, Paytm or any UPI ID</p>
                        <div style="margin-top:12px;">
                            <p style="font-size:0.76rem; color:#888; margin-bottom:6px; font-weight:600;">Quick fill — tap your app:</p>
                            <div class="upi-apps">
                                <button type="button" class="upi-app-btn" onclick="fillUpiSuffix('@okaxis')">GPay</button>
                                <button type="button" class="upi-app-btn" onclick="fillUpiSuffix('@ybl')">PhonePe</button>
                                <button type="button" class="upi-app-btn" onclick="fillUpiSuffix('@paytm')">Paytm</button>
                                <button type="button" class="upi-app-btn" onclick="fillUpiSuffix('@upi')">BHIM</button>
                                <button type="button" class="upi-app-btn" onclick="fillUpiSuffix('@ibl')">ICICI</button>
                            </div>
                        </div>
                    </div>

                    <!-- Card Panel -->
                    <div class="pay-panel" id="card-panel">
                        <label class="form-label"><i class="fas fa-credit-card"></i> Card Number <span style="color:var(--danger);">*</span></label>
                        <input class="form-input" type="text" id="card_number" name="card_number"
                               placeholder="1234  5678  9012  3456" maxlength="19" autocomplete="cc-number">
                        <div class="form-row" style="margin-top:14px;">
                            <div class="form-group">
                                <label class="form-label">Card Holder Name <span style="color:var(--danger);">*</span></label>
                                <input class="form-input" type="text" id="card_name" name="card_name"
                                       placeholder="Name as on card" autocomplete="cc-name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Card Type</label>
                                <select class="form-input" id="card_type" name="card_type">
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="rupay">RuPay</option>
                                    <option value="amex">Amex</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-row" style="margin-top:14px;">
                            <div class="form-group">
                                <label class="form-label">Expiry Date <span style="color:var(--danger);">*</span></label>
                                <input class="form-input" type="text" id="card_expiry" name="card_expiry"
                                       placeholder="MM / YY" maxlength="7" autocomplete="cc-exp">
                            </div>
                            <div class="form-group">
                                <label class="form-label">CVV <span style="color:var(--danger);">*</span></label>
                                <input class="form-input" type="password" id="card_cvv" name="card_cvv"
                                       placeholder="•••" maxlength="4" autocomplete="cc-csc">
                            </div>
                        </div>
                        <div class="secure-badge">
                            <i class="fas fa-lock"></i> Secured with 256-bit SSL encryption &nbsp;|&nbsp;
                            <i class="fas fa-shield-alt" style="color:#2196f3;"></i> PCI DSS Compliant
                        </div>
                    </div>

                    <button type="submit" class="place-order-btn">
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>
                    </form>
                </div>
            </div>

            <!-- ═══════════ RIGHT COLUMN ═══════════ -->
            <div class="order-summary">
                <div class="co-card">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    <div id="summary-items">
                        ${cartData.map(item => `
                            <div class="summary-item">
                                <img src="${item.image}" alt="${item.title}"
                                     onerror="this.src='images/paracetamol.jpg'">
                                <div class="s-info">
                                    <h4>${item.title}</h4>
                                    <p>Qty: ${item.quantity} &times; ₹${item.price.toFixed(2)}</p>
                                </div>
                                <div class="s-price">₹${(item.price * item.quantity).toFixed(2)}</div>
                            </div>`).join('')}
                    </div>
                    <div style="margin-top:12px;">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>₹${subtotal.toFixed(2)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span style="color:${shipping === 0 ? 'var(--primary)' : 'inherit'};">
                                ${shipping === 0 ? '🎉 FREE' : '₹' + shipping.toFixed(2)}
                            </span>
                        </div>
                        ${shipping > 0 ? `<p style="font-size:0.75rem; color:#aaa; margin-top:-4px;">Free shipping on orders above ₹500</p>` : ''}
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>₹${total.toFixed(2)}</span>
                        </div>
                    </div>
                </div>

                <!-- Trust badges -->
                <div class="co-card" style="padding:18px 22px;">
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; align-items:center; gap:10px; font-size:0.82rem; color:#555;">
                            <i class="fas fa-truck" style="color:var(--primary); width:18px;"></i>
                            <span><strong>Fast Delivery</strong> – 24–48 hrs in city, 3–5 days elsewhere</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; font-size:0.82rem; color:#555;">
                            <i class="fas fa-undo" style="color:var(--primary); width:18px;"></i>
                            <span><strong>Easy Returns</strong> – 7-day return on unopened products</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; font-size:0.82rem; color:#555;">
                            <i class="fas fa-lock" style="color:var(--primary); width:18px;"></i>
                            <span><strong>Secure Payments</strong> – All transactions are encrypted</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; font-size:0.82rem; color:#555;">
                            <i class="fas fa-certificate" style="color:var(--primary); width:18px;"></i>
                            <span><strong>100% Genuine</strong> – Sourced from licensed manufacturers</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>`;  /* end checkout-grid */

        /* ─────────────────────────────────────────
           Form submit
        ───────────────────────────────────────── */
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const method = fd.get('payment_method');

            /* Validate phone */
            const phone = fd.get('customer_phone').replace(/\D/g,'');
            if (phone.length !== 10) { showError('customer_phone', 'Enter a valid 10-digit mobile number.'); return; }

            /* Validate PIN */
            const pin = fd.get('pincode').replace(/\D/g,'');
            if (pin.length !== 6) { showError('pincode', 'Enter a valid 6-digit PIN code.'); return; }

            /* Validate UPI */
            if (method === 'upi') {
                const uid = fd.get('upi_id').trim();
                if (!uid) { showError('upi_id', 'Please enter your UPI ID.'); return; }
                if (!/@/.test(uid)) { showError('upi_id', 'Invalid UPI ID – must contain @'); return; }
            }

            /* Validate Card */
            if (method === 'card') {
                const cn = fd.get('card_number').replace(/\s/g,'');
                if (cn.length < 15) { showError('card_number', 'Enter a valid card number.'); return; }
                if (!fd.get('card_name').trim()) { showError('card_name', 'Enter card holder name.'); return; }
                if (!/^\d{2}\s*\/\s*\d{2}$/.test(fd.get('card_expiry'))) { showError('card_expiry', 'Enter expiry as MM / YY.'); return; }
                if (fd.get('card_cvv').length < 3) { showError('card_cvv', 'Enter a valid CVV.'); return; }
            }

            /* Build full address string */
            const parts = [
                fd.get('house_no'),
                fd.get('building'),
                fd.get('street'),
                fd.get('area'),
                fd.get('landmark') ? 'Near ' + fd.get('landmark') : null,
                fd.get('city'),
                fd.get('state'),
                'PIN: ' + fd.get('pincode'),
                fd.get('country')
            ].filter(Boolean);
            const fullAddress = parts.join(', ');

            const orderData = {
                customer_name:    fd.get('customer_name'),
                customer_email:   fd.get('customer_email'),
                customer_phone:   fd.get('customer_phone'),
                shipping_address: fullAddress,
                house_no:  fd.get('house_no'),
                building:  fd.get('building'),
                street:    fd.get('street'),
                area:      fd.get('area'),
                landmark:  fd.get('landmark'),
                city:      fd.get('city'),
                state:     fd.get('state'),
                pincode:   fd.get('pincode'),
                delivery_notes: fd.get('delivery_notes'),
                payment_method: method,
                total_amount:   total,
                items: cartData.map(i => ({
                    id: i.id, title: i.title, image: i.image,
                    price: i.price, quantity: i.quantity
                }))
            };

            const btn = e.target.querySelector('.place-order-btn');
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
                    sessionStorage.removeItem('checkout_cart');
                    const cc = document.getElementById('cart-count');
                    if (cc) cc.textContent = '0';

                    document.getElementById('checkout-content').innerHTML = `
                        <div class="success-box">
                            <i class="fas fa-check-circle"></i>
                            <h2>Order Placed Successfully!</h2>
                            <p>Thank you for shopping with <strong>Medkit</strong>.</p>
                            <div class="success-detail">
                                <p><strong>Order ID:</strong> #${data.data.order_id}</p>
                                <p><strong>Total Paid:</strong> ₹${total.toFixed(2)}</p>
                                <p><strong>Payment:</strong> ${method === 'cod' ? 'Cash on Delivery' : method === 'upi' ? 'UPI' : 'Card'}</p>
                                <p><strong>Deliver to:</strong> ${fullAddress}</p>
                            </div>
                            <p style="font-size:0.85rem; color:#aaa;">A confirmation will be sent to <strong>${orderData.customer_email}</strong></p>
                            <div class="success-actions">
                                <a href="index.php" class="btn primary-btn" style="border-radius:30px; padding:11px 26px;">
                                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                                </a>
                                <a href="profile.php" class="btn secondary-btn" style="border-radius:30px; padding:11px 26px;">
                                    <i class="fas fa-list-alt"></i> My Orders
                                </a>
                                <a href="track-order.php?id=${data.data.order_id}" class="btn secondary-btn" style="border-radius:30px; padding:11px 26px;">
                                    <i class="fas fa-truck"></i> Track Order
                                </a>
                            </div>
                        </div>`;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
                    alert(data.message || 'Failed to place order. Please try again.');
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
                alert('Network error. Please check your connection and try again.');
            }
        });

    } /* end else */

    /* ─────────────────────────────────────────
       Payment method switcher
    ───────────────────────────────────────── */
    function selectPayment(el) {
        document.querySelectorAll('.pay-method').forEach(m => m.classList.remove('active'));
        el.classList.add('active');
        const method = el.getAttribute('data-method');
        document.getElementById('payment_method').value = method;
        document.getElementById('upi-panel').classList.toggle('visible',  method === 'upi');
        document.getElementById('card-panel').classList.toggle('visible', method === 'card');
        const uid = document.getElementById('upi_id');
        if (uid) uid.required = (method === 'upi');
        ['card_number','card_name','card_expiry','card_cvv'].forEach(id => {
            const el2 = document.getElementById(id);
            if (el2) el2.required = (method === 'card');
        });
    }

    /* ── UPI quick-fill ── */
    function fillUpiSuffix(suffix) {
        const f = document.getElementById('upi_id');
        if (!f) return;
        let base = f.value.includes('@') ? f.value.split('@')[0] : f.value;
        f.value = base + suffix;
        f.focus();
    }

    /* ── Card number auto-spacing ── */
    document.addEventListener('input', (e) => {
        if (e.target.id === 'card_number') {
            let v = e.target.value.replace(/\D/g,'').substring(0,16);
            e.target.value = v.replace(/(.{4})/g,'$1  ').trim();
        }
        if (e.target.id === 'card_expiry') {
            let v = e.target.value.replace(/\D/g,'').substring(0,4);
            if (v.length >= 2) v = v.substring(0,2) + ' / ' + v.substring(2);
            e.target.value = v;
        }
        if (e.target.name === 'pincode') {
            e.target.value = e.target.value.replace(/\D/g,'').substring(0,6);
        }
        if (e.target.name === 'customer_phone') {
            e.target.value = e.target.value.replace(/\D/g,'').substring(0,10);
        }
    });

    /* ── Inline error helper ── */
    function showError(fieldName, msg) {
        const el = document.getElementById(fieldName) ||
                   document.querySelector(`[name="${fieldName}"]`);
        if (!el) { alert(msg); return; }
        el.classList.add('error');
        el.focus();
        let err = el.nextElementSibling;
        if (!err || !err.classList.contains('field-error')) {
            err = document.createElement('p');
            err.className = 'field-error';
            err.style.cssText = 'color:var(--danger);font-size:0.76rem;margin-top:4px;';
            el.parentNode.insertBefore(err, el.nextSibling);
        }
        err.textContent = msg;
        el.addEventListener('input', () => {
            el.classList.remove('error');
            if (err) err.remove();
        }, { once: true });
    }
    </script>
</body>
</html>
