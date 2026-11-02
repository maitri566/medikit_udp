<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Medkit</title>
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
                    <li><a href="faq.php" class="active">FAQ</a></li>
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
        <h1><i class="fas fa-question-circle"></i> Frequently Asked Questions</h1>
        <p>Find quick answers to common questions</p>
        <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> FAQ</div>
    </section>

    <section class="faq-section">
        <div class="container">
            <div class="faq-category-tabs" id="faq-tabs">
                <button class="faq-tab active" data-cat="all">All</button>
                <button class="faq-tab" data-cat="ordering">Ordering</button>
                <button class="faq-tab" data-cat="payment">Payment</button>
                <button class="faq-tab" data-cat="delivery">Delivery</button>
                <button class="faq-tab" data-cat="returns">Returns</button>
                <button class="faq-tab" data-cat="products">Products</button>
                <button class="faq-tab" data-cat="support">Support</button>
            </div>
            <div class="faq-accordion" id="faq-accordion">
                <p style="text-align:center; color:#999; padding:40px;">Loading FAQs...</p>
            </div>
            <div style="text-align:center; margin-top:40px;">
                <p style="color: var(--gray); margin-bottom: 15px;">Still have questions?</p>
                <a href="help-centre.php" class="btn primary-btn"><i class="fas fa-headset"></i> Contact Support</a>
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
    let allFaqs = [];
    
    async function loadFaqs(category) {
        const url = category === 'all' ? 'api/get_faq.php' : 'api/get_faq.php?category=' + category;
        const response = await fetch(url);
        const data = await response.json();
        
        const container = document.getElementById('faq-accordion');
        if (data.success && data.data.length > 0) {
            container.innerHTML = data.data.map((faq, i) => `
                <div class="faq-item ${i === 0 ? 'active' : ''}">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        ${faq.question}
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>${faq.answer}</p>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p style="text-align:center; color:#999; padding:40px;">No FAQs found in this category.</p>';
        }
    }
    
    function toggleFaq(el) {
        const item = el.parentElement;
        item.classList.toggle('active');
    }
    
    document.querySelectorAll('.faq-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.faq-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            loadFaqs(this.getAttribute('data-cat'));
        });
    });
    
    loadFaqs('all');
    </script>
</body>
</html>
