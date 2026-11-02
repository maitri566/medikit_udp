<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medkit - Online Medical Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <h1><i class="fas fa-clinic-medical"></i> Medkit</h1>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="help-centre.php">Help Centre</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="header-icons">
                <a href="#" id="cart-icon"><i class="fas fa-shopping-cart"></i> <span id="cart-count">0</span></a>
                <?php if (isLoggedIn()): ?>
                    <a href="#" id="user-icon" title="<?php echo htmlspecialchars($_SESSION['user_name']); ?>"><i class="fas fa-user-check"></i> <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></a>
                    <a href="api/logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" id="user-icon"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            </div>
            <div class="hamburger">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Your Health, Our Priority</h1>
                <p>Get medicines delivered at your doorstep</p>
                <div class="hero-buttons">
                    <a href="#products" class="btn primary-btn">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="feature-card">
                <i class="fas fa-truck"></i>
                <h3>Fast Delivery</h3>
                <p>Medicines delivered within 24 hours</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-certificate"></i>
                <h3>Genuine Products</h3>
                <p>100% authentic medicines</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-tag"></i>
                <h3>Best Prices</h3>
                <p>Discounts up to 30% on medicines</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Customer service always available</p>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories" id="categories">
        <div class="container">
            <h2 class="section-title">Shop by Categories</h2>
            <div class="category-grid">
                <div class="category-card"><a href="personal-care.php">
                    <img src="images/personal care.jpg" alt="Personal Care" loading="lazy">
                    <h3>Personal care</h3></a>
                </div>
                <div class="category-card"><a href="protein.php">
                    <img src="images/proitn.jpg" alt="Protein" loading="lazy">
                    <h3>Protein</h3></a>
                </div>
                <div class="category-card"><a href="nutrition.php">
                    <img src="images/nutrition.jpg" alt="Nutrition" loading="lazy">
                    <h3>Nutrition</h3></a>
                </div>
                <div class="category-card"><a href="ayurvedic.php">
                    <img src="images/ayurved.jpg" alt="Ayurved" loading="lazy">
                    <h3>Ayurved</h3></a>
                </div>
                <div class="category-card"><a href="food-drinks.php">
                    <img src="images/food & drink.jpg" alt="Food & Drinks" loading="lazy">
                    <h3>Food & Drinks</h3></a>
                </div>
                <div class="category-card"><a href="medical-devices.php">
                    <img src="images/medical device.jpg" alt="Medical Devices" loading="lazy">
                    <h3>Medical Devices</h3></a>
                </div>
                <div class="category-card"><a href="home-care-hygiene.php">
                    <img src="images/home hygine.png" alt="Home Care & Hygiene" loading="lazy">
                    <h3>Home Care & Hygiene</h3></a>
                </div>
                <div class="category-card"><a href="new-launches.php">
                    <img src="images/new launch.webp" alt="Newly Launches" loading="lazy">
                    <h3>Newly Launches</h3></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products" id="products">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Popular Products</h2>
                <div class="product-filter">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="medicines">Medicines</button>
                    <button class="filter-btn" data-filter="wellness">Wellness</button>
                    <button class="filter-btn" data-filter="devices">Devices</button>
                </div>
            </div>
            <div class="product-grid" id="product-grid">
                <!-- Products loaded dynamically from PHP API -->
            </div>
            <div class="view-more-container">
                <button class="btn secondary-btn" id="view-all-btn">View All Products</button>
                <button class="btn secondary-btn" id="view-less-btn">View Less Products</button>
            </div>
        </div>
    </section>

    <!-- Special Offers Section -->
    <section class="offers">
        <div class="container">
            <h2 class="section-title">Special Offers</h2>
            <div class="offers-grid">
                <div class="offer-card">
                    <div class="offer-content">
                        <h3>New User Offer</h3>
                        <p>Get 20% off on your first order</p>
                        <span class="offer-code">Use code: MEDI20</span>
                        <a href="#products" class="btn primary-btn">Shop Now</a>
                    </div>
                </div>
                <div class="offer-card">
                    <div class="offer-content">
                        <h3>Summer Season Sale</h3>
                        <p>Flat 15% off on all products</p>
                        <span class="offer-code">Use code: FEST15</span>
                        <a href="#products" class="btn primary-btn">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="testimonial-slider">
                <div class="testimonial-slide">
                    <div class="testimonial-content">
                        <p>"Medkit has been a lifesaver for me. The medicines are delivered on time and the prices are reasonable."</p>
                        <div class="customer-info">
                            <h4>Jay Solanki</h4>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slide">
                    <div class="testimonial-content">
                        <p>"I love the user-friendly interface and the vast range of products available. The delivery is always on time!"</p>
                        <div class="customer-info">
                            <h4>Samir Kikani</h4>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slide">
                    <div class="testimonial-content">
                        <p>"The customer service is exceptional. They helped me find the right medication quickly. Highly recommended!"</p>
                        <div class="customer-info">
                            <h4>Ronak Thummar</h4>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">About Medkit</h2>
                    <p>Medkit is a trusted online pharmacy that aims to provide high-quality healthcare products and medicines at affordable prices. Our mission is to make healthcare accessible to everyone.</p>
                    <p>Founded in 2020, we have grown to become one of the leading online medical stores, serving thousands of customers across the country.</p>
                    <div class="about-stats">
                        <div class="stat">
                            <h3>10,000+</h3>
                            <p>Products</p>
                        </div>
                        <div class="stat">
                            <h3>50,000+</h3>
                            <p>Happy Customers</p>
                        </div>
                        <div class="stat">
                            <h3>100+</h3>
                            <p>Cities</p>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="images/about img.jpeg" alt="About Medkit">
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get updates on new products, special offers, and health tips.</p>
                <form id="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn primary-btn">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Address</h3>
                            <p>Medkit Motibag, 123 Medical Plaza, junagadh - 362001</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa fa-phone"></i>
                        <div>
                            <h3>Phone</h3>
                            <p>+91 63510 79049</p>
                            <p>+91 63562 47202</p>
                            <p>+91 63521 09112</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>harshitrank329@gmail.com</p>
                            <p>piyubhesaniya061@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Working Hours</h3>
                            <p>Monday - Saturday: 9am - 8pm</p>
                            <p>Sunday: 10am - 6pm</p>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="https://www.instagram.com/harshit_rank_?igsh=dmF4a2Q4NWM0eTZz"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/harshit_rank_?igsh=dmF4a2Q4NWM0eTZz"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/harshit_rank_?igsh=dmF4a2Q4NWM0eTZz"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.instagram.com/harshit_rank_?igsh=dmF4a2Q4NWM0eTZz"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <form id="contact-form">
                        <div class="form-group">
                            <input type="text" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Subject">
                        </div>
                        <div class="form-group">
                            <textarea placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn primary-btn">Send Message</button>
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
        <div class="cart-items" id="cart-items">
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cart-total-price">₹0.00</span>
            </div>
            <button class="btn primary-btn checkout-btn">Checkout</button>
            <button class="btn secondary-btn continue-shopping">Continue Shopping</button>
        </div>
    </div>
    <div class="overlay"></div>

    <?php include 'footer.php'; ?>

    <script src="js/main.js"></script>
</body>
</html>
