<?php
// Run this to add new tables for Help Centre, FAQ, Support Tickets
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'medkit_db';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// ==========================================
// Table: faq
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    is_active TINYINT DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'faq' ready.</p>";

// Insert default FAQs
$faqs = [
    ['How do I place an order?', 'Browse products, add them to your cart, and click Checkout. Fill in your shipping details and select a payment method. Your order will be confirmed and delivered within 24-48 hours.', 'ordering'],
    ['What payment methods are available?', 'We accept Cash on Delivery (COD), UPI (Google Pay, PhonePe, Paytm), and Credit/Debit Cards. All payments are secure and encrypted.', 'payment'],
    ['How long does delivery take?', 'Standard delivery takes 24-48 hours within city limits and 3-5 business days for other locations. Express delivery is available in select cities.', 'delivery'],
    ['Can I return a product?', 'Yes, you can return products within 7 days of delivery if they are unopened and in original packaging. Refunds are processed within 5-7 business days.', 'returns'],
    ['How do I track my order?', 'Login to your account and visit the Order Tracking page. Enter your order ID to see real-time status updates.', 'delivery'],
    ['Are the medicines genuine?', 'Yes, all our medicines are 100% genuine and sourced directly from licensed manufacturers. Every product comes with a quality guarantee.', 'products'],
    ['Do I need a prescription?', 'Prescription medicines require a valid doctor\'s prescription. You can upload it during checkout or email it to us after placing the order.', 'ordering'],
    ['How do I contact customer support?', 'You can reach us through the Help Centre page, by phone at +91 63510 79049, or email at harshitrank329@gmail.com. Support is available 9am-8pm Monday to Saturday.', 'support'],
    ['Can I cancel my order?', 'Orders can be cancelled before they are shipped. Go to your order history and click Cancel, or contact support immediately.', 'ordering'],
    ['Do you offer discounts?', 'Yes! We offer regular discounts, festive sales, and a loyalty program. Subscribe to our newsletter for the latest offers.', 'payment'],
];

$stmt = $conn->prepare("INSERT IGNORE INTO faq (question, answer, category, display_order) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $q, $a, $cat, $order);
$order = 0;
foreach ($faqs as $faq) {
    $q = $faq[0];
    $a = $faq[1];
    $cat = $faq[2];
    $order++;
    $stmt->execute();
}
echo "<p style='color:green;'>FAQs inserted.</p>";

// ==========================================
// Table: support_tickets
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    ticket_status VARCHAR(50) DEFAULT 'open',
    priority VARCHAR(50) DEFAULT 'normal',
    admin_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'support_tickets' ready.</p>";

// ==========================================
// Table: user_addresses
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) DEFAULT 'Home',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    is_default TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'user_addresses' ready.</p>";

// ==========================================
// Table: product_reviews
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NULL,
    user_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL DEFAULT 5,
    comment TEXT,
    is_approved TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'product_reviews' ready.</p>";

// Insert some sample reviews
$reviews = [
    [1, 'Jay Solanki', 5, 'Great product, fast delivery!'],
    [3, 'Samir Kikani', 4, 'Good quality and packaging.'],
    [75, 'Ronak Thummar', 5, 'Best protein supplement I have used.'],
    [2, 'Priya Patel', 4, 'Accurate readings, value for money.'],
    [69, 'Amit Shah', 5, 'Refreshing green tea, love it!'],
];
$stmt = $conn->prepare("INSERT IGNORE INTO product_reviews (product_id, user_name, rating, comment, is_approved) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("isis", $pid, $uname, $rating, $comment);
foreach ($reviews as $r) {
    $pid = $r[0];
    $uname = $r[1];
    $rating = $r[2];
    $comment = $r[3];
    $stmt->execute();
}
echo "<p style='color:green;'>Sample reviews inserted.</p>";

echo "<hr><h2 style='color:#27ae60;'>Database update complete!</h2>";
echo "<p><a href='../index.php'>Go to Homepage</a> | <a href='../admin/login.php'>Admin Panel</a></p>";
$conn->close();
