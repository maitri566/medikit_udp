<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Care &amp; Hygiene - Medkit</title>
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
        <h1><i class="fas fa-home"></i> Home Care &amp; Hygiene</h1>
        <p>Eco-friendly cleaning and hygiene products</p>
        <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> Home Care &amp; Hygiene</div>
    </section>

    <section class="products" id="products">
        <div class="container">
            <div class="section-header"><h2 class="section-title">Home Care &amp; Hygiene Products</h2></div>
            <div class="product-grid" id="product-grid"></div>
        </div>
    </section>

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
</body>
</html>
