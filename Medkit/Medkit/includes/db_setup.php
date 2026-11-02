<?php
// Run this file once to set up the database, tables, and initial data
// Visit: http://localhost/atte/Medkit/Medkit/includes/db_setup.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'medkit_db';

// Connect without database name first to create it
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>Database '$DB_NAME' created successfully or already exists.</p>";
} else {
    die("<p style='color:red;'>Error creating database: " . $conn->error . "</p>");
}

$conn->select_db($DB_NAME);
$conn->set_charset('utf8mb4');

// ==========================================
// Table: users (customers)
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'users' ready.</p>";

// ==========================================
// Table: admin
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'admin' ready.</p>";

// Insert default admin (admin@gmail.com / admin)
$adminPass = password_hash('admin', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT IGNORE INTO admin (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $pass);
$email = 'admin@gmail.com';
$pass = $adminPass;
$stmt->execute();
echo "<p style='color:green;'>Default admin created: admin@gmail.com / admin</p>";

// ==========================================
// Table: categories
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'categories' ready.</p>";

// Insert categories
$categories = [
    ['Personal Care', 'personal-care', 'images/personal care.jpg'],
    ['Protein', 'protein', 'images/proitn.jpg'],
    ['Nutrition', 'nutrition', 'images/nutrition.jpg'],
    ['Ayurved', 'ayurved', 'images/ayurved.jpg'],
    ['Food & Drinks', 'food-drinks', 'images/food & drink.jpg'],
    ['Medical Devices', 'medical-devices', 'images/medical device.jpg'],
    ['Home Care & Hygiene', 'home-care-hygiene', 'images/home hygine.png'],
    ['New Launches', 'new-launches', 'images/new launch.webp'],
    ['Medicines', 'medicines', 'images/paracetamol.jpg'],
    ['Wellness', 'wellness', 'images/vitamin-c-supplements.jpg'],
    ['Devices', 'devices', 'images/DigitalThermometer.jpg'],
];
$stmt = $conn->prepare("INSERT IGNORE INTO categories (name, slug, image) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $slug, $image);
foreach ($categories as $cat) {
    $name = $cat[0];
    $slug = $cat[1];
    $image = $cat[2];
    $stmt->execute();
}
echo "<p style='color:green;'>Categories inserted.</p>";

// ==========================================
// Table: products
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2),
    rating DECIMAL(2,1) DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    discount VARCHAR(50),
    description TEXT,
    stock INT DEFAULT 100,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'products' ready.</p>";

// ==========================================
// Table: orders
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cod',
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'orders' ready.</p>";

// ==========================================
// Table: order_items
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_title VARCHAR(255) NOT NULL,
    product_image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'order_items' ready.</p>";

// ==========================================
// Table: contact_messages
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'contact_messages' ready.</p>";

// ==========================================
// Table: newsletter_subscribers
// ==========================================
$conn->query("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "<p>Table 'newsletter_subscribers' ready.</p>";

// ==========================================
// Insert Products
// ==========================================
$products = [
    [1, "Paracetamol Tablets", "medicines", 9.00, 12.45, 5, "images/paracetamol.jpg", "25% OFF", "Relieves mild to moderate pain and reduces fever."],
    [2, "Digital Thermometer", "devices", 158.00, 187.00, 4, "images/DigitalThermometer.jpg", "16% OFF", "Accurate temperature readings within seconds."],
    [3, "Vitamin C Supplements", "wellness", 324.00, 500, 4.5, "images/vitamin-c-supplements.jpg", "35% OFF", "Boosts immunity and supports overall health."],
    [4, "Sunscreen", "personal-care", 399.00, 499.00, 4.8, "images/sunscreen3.jpg", "20% OFF", "Broad-spectrum sunscreen for sun protection."],
    [5, "Ashwagandha", "ayurved", 15.00, 20.00, 4.8, "images/ashwagandha.jpg", "25% OFF", "Supports stress relief and enhances overall well-being."],
    [7, "Aspirin Tablets", "medicines", 91.50, 122.00, 4.5, "images/aspirin-dispersible-tablets.jpg", "25% OFF", "Relieves pain, reduces inflammation and fever."],
    [8, "Digital Pulse Oximeter", "devices", 795.00, 1589.00, 5, "images/DigitalPulseOximeter.jpg", "49% OFF", "Measures oxygen saturation levels in the blood."],
    [9, "Skin Brightening Cream", "wellness", 599.00, 699.00, 4.5, "images/herbal-skin-brightening-cream.jpg", "14% OFF", "Natural remedy for brightening skin."],
    [10, "Ibuprofen Tablets", "medicines", 99.00, 120.00, 4, "images/ibuprofen-tablets.jpg", "17% OFF", "Relieves pain and reduces inflammation."],
    [11, "Glucose Monitor", "devices", 2499.00, 2999.00, 5, "images/GlucoseMonitor.jpg", "17% OFF", "Monitors blood sugar levels for diabetics."],
    [12, "Vitamin D3 Supplements", "wellness", 399.00, 599.00, 4.5, "images/vitamin-d3-supplements.jpg", "33% OFF", "Supports bone health and immune function."],
    [13, "Nebulizer Machine", "devices", 1999.00, 2499.00, 5, "images/NebulizerMachine.jpg", "20% OFF", "Helps in treating respiratory conditions."],
    [14, "Electrolyte Powder", "wellness", 299.00, 399.00, 4, "images/electrolyte-powder.jpg", "25% OFF", "Replenishes electrolytes lost during exercise."],
    [15, "Pain Relief Gel", "medicines", 199.00, 250.00, 4.5, "images/pain-relief-gel.jpg", "20% OFF", "Provides fast relief from muscle and joint pain."],
    [16, "Thermal Scissors", "devices", 499.00, 599.00, 4.5, "images/ThermalScissors.jpg", "17% OFF", "Cuts through tough materials with ease."],
    [17, "Omega-3 Fish Oil", "wellness", 899.00, 1099.00, 5, "images/omega-3-fish-oil.jpg", "18% OFF", "Supports heart and brain health."],
    [18, "Inhaler", "devices", 799.00, 999.00, 4, "images/Inhaler.jpg", "20% OFF", "Helps in treating asthma and other respiratory issues."],
    [19, "Calcium Supplements", "wellness", 499.00, 599.00, 4.5, "images/calcium-supplements.jpg", "17% OFF", "Supports bone health and prevents osteoporosis."],
    [20, "Thermometer Strips", "devices", 199.00, 299.00, 4, "images/thermometer-strips.jpg", "33% OFF", "Quick and easy way to check temperature."],
    [21, "Electrolyte Tablets", "wellness", 299.00, 399.00, 4.5, "images/electrolyte-tablets.jpg", "25% OFF", "Replenishes electrolytes lost during exercise."],
    [22, "Cold Compress Pack", "wellness", 199.00, 299.00, 4, "images/cold-compress-pack.jpg", "33% OFF", "Provides relief from swelling and pain."],
    [23, "Hot Water Bottle", "wellness", 299.00, 399.00, 4.5, "images/hot-water-bottle.jpg", "25% OFF", "Provides warmth and comfort & Body Pain Relief."],
    [24, "Thermometer Gun", "devices", 999.00, 1299.00, 5, "images/thermometer-gun.jpg", "23% OFF", "Non-contact thermometer for quick readings."],
    [25, "Pain Relief Patches", "medicines", 399.00, 499.00, 4.5, "images/pain-relief-patches.jpg", "20% OFF", "Provides targeted relief from pain."],
    [26, "Pulse Oximeter Finger Clip", "devices", 799.00, 999.00, 4, "images/pulse-oximeter-finger-clip.jpg", "20% OFF", "Measures oxygen saturation levels in the blood."],
    [27, "Vitamin B12 Supplements", "wellness", 599.00, 799.00, 4.5, "images/vitamin-b12-supplements.jpg", "25% OFF", "Supports energy production and brain health."],
    [28, "Digital Body Fat Scale", "devices", 1999.00, 2499.00, 5, "images/digital-body-fat-scale.jpg", "20% OFF", "Measures body fat percentage and weight."],
    [29, "Herbal Cough Syrup", "medicines", 199.00, 299.00, 4, "images/herbal-cough-syrup.jpg", "33% OFF", "Natural remedy for cough and throat irritation."],
    [30, "Infrared Thermometer", "devices", 1299.00, 1599.00, 5, "images/infrared-thermometer.jpg", "19% OFF", "Non-contact thermometer for quick readings."],
    [31, "Probiotic Supplements", "wellness", 699.00, 899.00, 4.5, "images/probiotic-supplements.jpg", "22% OFF", "Supports gut health and digestion."],
    [32, "Digital Blood Glucose Meter", "devices", 2499.00, 2999.00, 5, "images/digital-blood-glucose-meter.jpg", "17% OFF", "Monitors blood sugar levels."],
    [33, "Herbal Pain Relief Balm", "medicines", 199.00, 299.00, 4.5, "images/herbal-pain-relief-balm.jpeg", "33% OFF", "Natural remedy for muscle and joint pain."],
    [34, "Digital Thermometer with Fever Alarm", "devices", 799.00, 999.00, 4, "images/digital-thermometer-with-fever-alarm.jpg", "20% OFF", "Temperature with fever alarm."],
    [35, "Vitamin E Oil", "wellness", 399.00, 499.00, 4.5, "images/vitamin-e-oil.jpg", "20% OFF", "Moisturizes skin and promotes healing."],
    [36, "Digital Weight Scale", "devices", 999.00, 1299.00, 5, "images/digital-weight-scale.jpg", "23% OFF", "Accurate weight measurements with easy-to-read display."],
    [37, "Herbal Sleep Aid", "wellness", 499.00, 599.00, 4, "images/herbal-sleep-aid.jpg", "17% OFF", "Natural remedy for better sleep."],
    [38, "Herbal Joint Pain Relief", "wellness", 499.00, 599.00, 4, "images/herbal-joint-pain-relief.jpg", "17% OFF", "Natural remedy for joint pain."],
    [39, "Herbal Energy Drink", "wellness", 299.00, 399.00, 4.5, "images/herbal-energy-drink.jpg", "25% OFF", "Natural energy booster."],
    [40, "Herbal Digestive Aid", "wellness", 399.00, 499.00, 4, "images/herbal-digestive-aid.jpg", "20% OFF", "Supports digestive health."],
    [41, "Herbal Immune Booster", "wellness", 599.00, 699.00, 4.5, "images/herbal-immune-booster.jpg", "14% OFF", "Supports immune system health."],
    [42, "Digital Kitchen Scale", "devices", 1299.00, 1599.00, 5, "images/digital-kitchen-scale.jpg", "19% OFF", "Accurate weight measurements for cooking."],
    [43, "Digital Food Thermometer", "devices", 799.00, 999.00, 4, "images/digital-food-thermometer.jpg", "20% OFF", "Temperature readings for cooking."],
    [44, "Digital Body Composition Scale", "devices", 2499.00, 2999.00, 5, "images/digital-body-composition-scale.jpg", "17% OFF", "Measures body fat percentage."],
    [45, "Herbal Stress Relief", "wellness", 499.00, 599.00, 4.5, "images/herbal-stress-relief.jpg", "17% OFF", "A triple-action formula designed to regulate stress."],
    [46, "Digital Caliper", "devices", 999.00, 1299.00, 5, "images/digital-caliper.jpg", "23% OFF", "Measures body fat percentage and weight."],
    [47, "Herbal Anti-Aging Cream", "wellness", 799.00, 999.00, 4.5, "images/herbal-anti-aging-cream.jpg", "20% OFF", "Natural remedy for youthful skin."],
    [48, "Digital Blood Pressure Monitor", "devices", 2499.00, 2999.00, 5, "images/digital-blood-pressure-monitor.jpg", "17% OFF", "Monitors blood pressure levels."],
    [49, "Herbal Hair Growth Oil", "wellness", 399.00, 499.00, 4, "images/herbal-hair-growth-oil.jpg", "20% OFF", "Adiwasi Hair Oil Original, Adibasi Nilambhari Herbal Kesh Oil Original."],
    [50, "Digital Pulse Oximeter with Bluetooth", "devices", 1999.00, 2499.00, 5, "images/digital-pulse-oximeter-with-bluetooth.jpg", "20% OFF", "Measures oxygen saturation levels in the blood."],
    [51, "Herbal Weight Loss Supplement", "wellness", 599.00, 699.00, 4.5, "images/herbal-weight-loss-supplement.jpg", "14% OFF", "Supports weight loss and metabolism."],
    [52, "Digital Body Fat Analyzer", "devices", 1999.00, 2499.00, 5, "images/digital-body-fat-analyzer.jpg", "20% OFF", "Digital Body Fat Analyzer, Electronic BMI Handheld Body Fat Monitor with LCD Display."],
    [53, "Face Wash", "personal-care", 199.00, 249.00, 4.5, "images/face-wash.jpg", "20% OFF", "Gentle face wash for all skin types."],
    [54, "Shampoo", "personal-care", 299.00, 349.00, 4, "images/shampoo1.jpg", "15% OFF", "Nourishing shampoo for healthy hair."],
    [55, "Body Lotion", "personal-care", 249.00, 299.00, 4.5, "images/body-lotion.jpg", "17% OFF", "Moisturizing body lotion for soft skin."],
    [56, "Sunscreen", "personal-care", 399.00, 499.00, 4.8, "images/sunscreen2.jpg", "20% OFF", "Broad-spectrum sunscreen for sun protection."],
    [57, "Licorice Root", "ayurved", 10.00, 12.00, 4.5, "images/licorice.jpg", "17% OFF", "Supports respiratory health and soothes the throat."],
    [58, "Curry Leaves Powder", "ayurved", 3.50, 5.00, 4.3, "images/curry_leaves.jpg", "30% OFF", "Promotes healthy hair and digestion."],
    [59, "Black Seed Oil", "ayurved", 18.00, 22.00, 4.9, "images/black_seed.jpg", "18% OFF", "Supports immune function and overall health."],
    [60, "Coconut Oil", "ayurved", 12.00, 15.00, 4.8, "images/coconut_oil.jpg", "20% OFF", "Moisturizes skin and promotes healthy hair."],
    [61, "Rosemary Oil", "ayurved", 11.00, 14.00, 4.6, "images/rosemary_oil.jpg", "21% OFF", "Enhances memory and concentration."],
    [62, "Himalayan Pink Salt", "ayurved", 4.00, 5.50, 4.7, "images/himalayan_salt.jpg", "27% OFF", "Rich in minerals and supports hydration."],
    [63, "Sunscreen", "personal-care", 399.00, 499.00, 4.8, "images/sunscreen1.jpg", "20% OFF", "Broad-spectrum sunscreen for sun protection."],
    [64, "Ashwagandha Capsules", "ayurved", 15.00, 20.00, 4.8, "images/ashwagandha1.jpg", "25% OFF", "Supports stress relief and enhances overall well-being."],
    [65, "Optimum Nutrition (ON) Gold Standard 100%", "Protein", 30.00, 40.00, 4.7, "images/whey_protein1.jpg", "25% OFF", "High-quality protein for muscle recovery and growth."],
    [66, "Vegan Protein Powder", "Nutrition", 25.00, 35.00, 4.6, "images/vegan_protein.jpg", "29% OFF", "Plant-based protein blend for a healthy lifestyle."],
    [67, "Superfood Smoothie Mix", "New Launches", 20.00, 25.00, 4.9, "images/superfood_smoothie.jpg", "20% OFF", "A blend of nutrient-rich superfoods for a quick boost."],
    [68, "Natural All-Purpose Cleaner", "Home Care & Hygiene", 10.00, 15.00, 4.5, "images/all_purpose_cleaner.jpg", "33% OFF", "Eco-friendly cleaner for a safe and clean home."],
    [69, "Organic Green Tea", "Food & Drinks", 12.00, 15.00, 4.8, "images/green_tea1.jpg", "20% OFF", "Rich in antioxidants, perfect for a refreshing drink."],
    [70, "Organic Green Tea", "Food & Drinks", 150.00, 200.00, 4.8, "images/green_tea.jpg", "25% OFF", "Rich in antioxidants, perfect for a refreshing drink."],
    [71, "Protein Bar", "Nutrition", 100.00, 150.00, 4.5, "images/protein_bar.jpg", "33% OFF", "Delicious protein bar for on-the-go nutrition."],
    [72, "Organic Chia Seeds", "Food & Drinks", 250.00, 300.00, 4.7, "images/chia_seeds.jpg", "17% OFF", "Rich in omega-3 fatty acids and fiber."],
    [73, "Turmeric Powder", "Food & Drinks", 80.00, 120.00, 4.6, "images/turmeric_powder.jpg", "33% OFF", "Natural anti-inflammatory spice."],
    [74, "Coconut Oil", "Food & Drinks", 200.00, 250.00, 4.8, "images/coconut_oil.jpg", "20% OFF", "Versatile oil for cooking and skin care."],
    [75, "Performance Whey Protein", "Protein", 2000.00, 2500.00, 4.9, "images/whey_protein.jpg", "20% OFF", "3 Grams of 100% Creatine Monohydrate per Serving, Zero Calories & Zero Carbohydrates."],
    [76, "Vegan Protein Powder", "Nutrition", 1500.00, 2000.00, 4.7, "images/vegan_protein1.jpg", "25% OFF", "Plant-based protein blend for a healthy lifestyle."],
    [77, "Omega-3 Fish Oil", "wellness", 1200.00, 1500.00, 4.8, "images/omega3.jpg", "20% OFF", "Supports heart health and cognitive function."],
    [78, "Vitamin C Tablets", "wellness", 300.00, 400.00, 4.6, "images/vitamin_c.jpg", "25% OFF", "Boosts immunity and supports overall health."],
    [79, "Herbal Tea Assortment", "Food & Drinks", 350.00, 450.00, 4.7, "images/herbal_tea.jpg", "22% OFF", "A variety of soothing herbal teas."],
    [80, "Natural Honey", "Food & Drinks", 400.00, 500.00, 4.9, "images/honey.jpg", "20% OFF", "Pure and natural honey for health benefits."],
    [81, "Aloe Vera Gel", "personal-care", 250.00, 300.00, 4.8, "images/aloe_vera.jpg", "17% OFF", "Soothes and hydrates skin."],
    [82, "Activated Charcoal Powder", "wellness", 500.00, 600.00, 4.5, "images/charcoal_powder.jpg", "17% OFF", "Detoxifies and purifies the body."],
    [83, "Matcha Green Tea Powder", "Food & Drinks", 800.00, 1000.00, 4.9, "images/matcha.jpg", "20% OFF", "Rich in antioxidants and boosts metabolism."],
    [84, "Cacao Nibs", "Food & Drinks", 600.00, 750.00, 4.6, "images/cacao_nibs.jpg", "20% OFF", "Raw chocolate pieces for a healthy snack."],
    [85, "Spirulina Powder", "Nutrition", 1200.00, 1500.00, 4.8, "images/spirulina.jpg", "20% OFF", "Nutrient-dense superfood for energy."],
    [86, "Beetroot Powder", "Nutrition", 700.00, 900.00, 4.7, "images/beetroot_powder.jpg", "22% OFF", "Supports cardiovascular health."],
    [87, "Cinnamon Powder", "Food & Drinks", 150.00, 200.00, 4.5, "images/cinnamon.jpg", "25% OFF", "Natural spice with numerous health benefits."],
    [88, "Ginger Powder", "Food & Drinks", 180.00, 220.00, 4.6, "images/ginger_powder.jpg", "18% OFF", "Supports digestion and has anti-inflammatory properties."],
    [89, "Flaxseed Meal", "Nutrition", 300.00, 400.00, 4.7, "images/flaxseed_meal.jpg", "25% OFF", "Rich in omega-3 fatty acids and fiber."],
    [90, "Almond Butter", "Food & Drinks", 1200.00, 1500.00, 4.8, "images/almond_butter.jpg", "20% OFF", "Nutritious spread for a healthy snack."],
    [91, "Peanut Butter", "Food & Drinks", 800.00, 1000.00, 4.6, "images/peanut_butter.jpg", "20% OFF", "Creamy and delicious peanut butter."],
    [92, "Quinoa", "Food & Drinks", 500.00, 600.00, 4.7, "images/quinoa.jpg", "17% OFF", "High-protein grain for healthy meals."],
    [93, "Brown Rice", "Food & Drinks", 300.00, 400.00, 4.5, "images/brown_rice.jpg", "25% OFF", "Nutritious whole grain for balanced meals."],
    [94, "Oats", "Food & Drinks", 200.00, 250.00, 4.8, "images/oats.jpg", "20% OFF", "Healthy whole grain for breakfast."],
    [95, "Chickpeas", "Food & Drinks", 150.00, 200.00, 4.6, "images/chickpeas.jpg", "25% OFF", "High-protein legume for various dishes."],
    [96, "Lentils", "Food & Drinks", 120.00, 150.00, 4.7, "images/lentils.jpg", "20% OFF", "Nutritious legume for soups and salads."],
    [97, "Couscous", "Food & Drinks", 250.00, 300.00, 4.5, "images/couscous.jpg", "17% OFF", "Quick-cooking grain for easy meals."],
    [98, "Pasta", "Food & Drinks", 200.00, 250.00, 4.8, "images/pasta.jpg", "20% OFF", "Versatile grain for various recipes."],
    [99, "Rice Noodles", "Food & Drinks", 180.00, 220.00, 4.6, "images/rice_noodles.jpg", "18% OFF", "Gluten-free noodles for Asian dishes."],
    [100, "Coconut Sugar", "Food & Drinks", 300.00, 350.00, 4.7, "images/coconut_sugar.jpg", "14% OFF", "Natural sweetener with a low glycemic index."],
    [101, "Maple Syrup", "Food & Drinks", 400.00, 500.00, 4.9, "images/maple_syrup.jpg", "20% OFF", "Pure maple syrup for pancakes and desserts."],
    [102, "Nut Mix", "Food & Drinks", 600.00, 700.00, 4.8, "images/nut_mix.jpg", "14% OFF", "Healthy snack mix of various nuts."],
    [103, "Dried Fruits", "Food & Drinks", 500.00, 600.00, 4.7, "images/dried_fruits.jpg", "17% OFF", "Nutritious and sweet dried fruit snacks."],
    [104, "Granola", "Food & Drinks", 350.00, 400.00, 4.6, "images/granola.jpg", "13% OFF", "Crunchy granola for breakfast or snacks."],
    [105, "Energy Drink", "Food & Drinks", 100.00, 150.00, 4.5, "images/energy_drink.jpg", "33% OFF", "Boost your energy with this refreshing drink."],
    [106, "Herbal Supplements", "wellness", 1200.00, 1500.00, 4.8, "images/herbal_supplements.jpg", "20% OFF", "Natural supplements for overall wellness."],
    [107, "Probiotic Capsules", "wellness", 800.00, 1000.00, 4.7, "images/probiotics.jpg", "20% OFF", "Supports gut health and digestion."],
    [108, "Electrolyte Powder", "Nutrition", 600.00, 750.00, 4.6, "images/electrolyte_powder.jpg", "20% OFF", "Replenishes electrolytes after workouts."],
    [109, "Detox Tea", "Food & Drinks", 250.00, 300.00, 4.5, "images/detox_tea.jpg", "17% OFF", "Supports detoxification and digestion."],
    [110, "Cleansing Face Wash", "personal-care", 350.00, 400.00, 4.8, "images/face_wash.jpg", "13% OFF", "Gentle cleanser for all skin types."],
    [111, "Sunscreen Lotion", "personal-care", 500.00, 600.00, 4.9, "images/sunscreen.jpg", "17% OFF", "Protects skin from harmful UV rays."],
    [112, "Moisturizing Cream", "personal-care", 700.00, 800.00, 4.6, "images/moisturizer.jpg", "13% OFF", "Hydrates and nourishes the skin."],
    [113, "Hair Oil", "personal-care", 400.00, 500.00, 4.7, "images/hair_oil.jpg", "20% OFF", "Promotes healthy hair growth."],
    [114, "Lip Balm", "personal-care", 150.00, 200.00, 4.5, "images/lip_balm.jpg", "25% OFF", "Keeps lips moisturized and soft."],
    [115, "Nail Care Kit", "personal-care", 300.00, 350.00, 4.8, "images/nail_care.jpg", "14% OFF", "Complete kit for nail grooming."],
    [116, "Foot Cream", "personal-care", 250.00, 300.00, 4.6, "images/foot_cream.jpg", "17% OFF", "Soothes and softens dry feet."],
    [117, "Body Scrub", "personal-care", 400.00, 500.00, 4.7, "images/body_scrub.jpg", "20% OFF", "Exfoliates and rejuvenates skin."],
    [118, "Shampoo", "personal-care", 350.00, 400.00, 4.8, "images/shampoo.jpg", "13% OFF", "Gentle formula for all hair types."],
    [119, "Conditioner", "personal-care", 300.00, 350.00, 4.6, "images/conditioner.jpg", "14% OFF", "Nourishes and detangles hair."],
];

$stmt = $conn->prepare("INSERT IGNORE INTO products (id, title, category, price, old_price, rating, image, discount, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issddssss", $pid, $title, $cat, $price, $oldPrice, $rating, $image, $discount, $desc);

$inserted = 0;
foreach ($products as $p) {
    $pid = $p[0];
    $title = $p[1];
    $cat = $p[2];
    $price = $p[3];
    $oldPrice = $p[4];
    $rating = $p[5];
    $image = $p[6];
    $discount = $p[7];
    $desc = $p[8];
    $stmt->execute();
    $inserted++;
}
echo "<p style='color:green;'>$inserted products inserted.</p>";

echo "<hr>";
echo "<h2 style='color:#27ae60;'>Database setup complete!</h2>";
echo "<p><strong>Admin Login:</strong> admin@gmail.com / admin</p>";
echo "<p><a href='../index.php' style='font-size:18px;'>Go to Homepage</a> | <a href='../admin/login.php' style='font-size:18px;'>Go to Admin Panel</a></p>";

$conn->close();
