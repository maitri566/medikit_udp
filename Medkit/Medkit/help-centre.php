<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Centre - Medkit</title>
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
                    <li><a href="help-centre.php" class="active">Help Centre</a></li>
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
        <h1><i class="fas fa-hands-helping"></i> Help Centre</h1>
        <p>We're here to help you with anything you need</p>
        <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> Help Centre</div>
    </section>

    <section class="help-centre">
        <div class="container">
            <div class="help-grid">
                <div class="help-card" onclick="window.location.href='faq.php'">
                    <i class="fas fa-question-circle"></i>
                    <h3>FAQ</h3>
                    <p>Find answers to frequently asked questions about orders, payments, delivery and returns.</p>
                </div>
                <div class="help-card" onclick="window.location.href='track-order.php'">
                    <i class="fas fa-truck-moving"></i>
                    <h3>Track Your Order</h3>
                    <p>Enter your order ID to check the current status and estimated delivery time.</p>
                </div>
                <div class="help-card" onclick="window.location.href='#support-form'">
                    <i class="fas fa-headset"></i>
                    <h3>Contact Support</h3>
                    <p>Submit a support ticket and our team will get back to you within 24 hours.</p>
                </div>
                <div class="help-card" onclick="window.location.href='index.php#contact'">
                    <i class="fas fa-phone-alt"></i>
                    <h3>Call Us</h3>
                    <p>Speak to our customer care team directly at +91 63510 79049. Mon-Sat 9am-8pm.</p>
                </div>
                <div class="help-card" onclick="window.location.href='profile.php'">
                    <i class="fas fa-user-cog"></i>
                    <h3>Manage Account</h3>
                    <p>Update your profile, view order history, and manage your account settings.</p>
                </div>
                <div class="help-card" onclick="window.location.href='index.php#contact'">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Visit Us</h3>
                    <p>Medkit Motibag, 123 Medical Plaza, Junagadh - 362001. Open Monday to Saturday.</p>
                </div>
            </div>

            <div id="support-form">
                <div class="support-form">
                    <h3><i class="fas fa-paper-plane"></i> Submit a Support Ticket</h3>
                    <div id="support-msg" style="display:none; padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:0.9rem;"></div>
                    <form id="ticket-form">
                        <div class="form-row" style="display:flex; gap:15px;">
                            <div class="form-group" style="flex:1;">
                                <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Your Name *</label>
                                <input type="text" name="name" placeholder="Enter your name" required value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Email *</label>
                                <input type="email" name="email" placeholder="Enter your email" required value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Subject *</label>
                            <input type="text" name="subject" placeholder="Brief subject of your issue" required>
                        </div>
                        <div class="form-group">
                            <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Message *</label>
                            <textarea name="message" placeholder="Describe your issue in detail" required style="width:100%; min-height:120px; padding:12px 16px; border:2px solid #f1f3f5; border-radius:8px; resize:vertical; outline:none; font-family:inherit;" onfocus="this.style.borderColor='#1a936f'" onblur="this.style.borderColor='#f1f3f5'"></textarea>
                        </div>
                        <button type="submit" class="btn primary-btn" style="width:100%;"><i class="fas fa-paper-plane"></i> Submit Ticket</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <span class="close-cart"><i class="fas fa-times"></i></span>
        </div>
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
    document.getElementById('ticket-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch('api/support_ticket.php', { method: 'POST', body: formData });
            const data = await response.json();
            const msgEl = document.getElementById('support-msg');
            msgEl.style.display = 'block';
            msgEl.textContent = data.message;
            msgEl.style.background = data.success ? '#d4edda' : '#fee';
            msgEl.style.color = data.success ? '#155724' : '#721c24';
            if (data.success) e.target.reset();
        } catch (err) {
            alert('Error submitting ticket. Please try again.');
        }
    });
    </script>
</body>
</html>
