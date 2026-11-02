<?php
require_once __DIR__ . '/includes/config.php';
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
        $identifier = sanitize($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Check if admin login
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL) && $identifier === 'admin@gmail.com') {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    redirect('admin/dashboard.php');
                }
            }
        }
        
        // Regular user login
        $conn = getDBConnection();
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $identifier);
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
            $stmt->bind_param("s", $identifier);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $msg = 'Account not found. Please register first.';
            $msgType = 'error';
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                redirect('index.php');
            } else {
                $msg = 'Incorrect password';
                $msgType = 'error';
            }
        }
    } elseif ($action === 'register') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
            $msg = 'All fields are required';
            $msgType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Invalid email format';
            $msgType = 'error';
        } elseif (strlen($password) < 6) {
            $msg = 'Password must be at least 6 characters';
            $msgType = 'error';
        } elseif ($password !== $confirm_password) {
            $msg = 'Passwords do not match';
            $msgType = 'error';
        } else {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $msg = 'Email already registered. Please login.';
                $msgType = 'error';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['user_email'] = $email;
                    $msg = 'Registration successful! Redirecting...';
                    $msgType = 'success';
                    echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
                } else {
                    $msg = 'Registration failed. Try again.';
                    $msgType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - Medkit</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .php-msg {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: <?php echo $msg ? 'block' : 'none'; ?>;
        }
        .php-msg.error { background: #fee; color: #c0392b; border: 1px solid #f5c6cb; }
        .php-msg.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .login-container { overflow-y: auto; max-height: 90vh; }
        
        .reg-field { display: <?php echo 'none'; ?>; }
        
        .full-name-group { margin-bottom: 20px; }
        .full-name-group label { display:block; font-size:0.85rem; font-weight:500; margin-bottom:8px; color:#666; }
        .full-name-group input { width:100%; padding:15px 45px; border:1px solid #ddd; border-radius:8px; font-size:1rem; }
        .full-name-group input:focus { border-color:#4CAF50; outline:none; box-shadow:0 0 0 3px rgba(76,175,80,0.1); }
        .full-name-group { position: relative; }
        .full-name-group i { position:absolute; left:15px; top:38px; color:#666; }
        
        .phone-group { margin-bottom: 20px; position: relative; }
        .phone-group label { display:block; font-size:0.85rem; font-weight:500; margin-bottom:8px; color:#666; }
        .phone-group input { width:100%; padding:15px 45px; border:1px solid #ddd; border-radius:8px; font-size:1rem; }
        .phone-group input:focus { border-color:#4CAF50; outline:none; box-shadow:0 0 0 3px rgba(76,175,80,0.1); }
        .phone-group i { position:absolute; left:15px; top:38px; color:#666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <button class="close-btn" id="close-login">&times;</button>
            <div class="login-header">
                <a href="index.php" class="logo">
                    <i class="fas fa-clinic-medical"></i>
                    <h1>Medkit</h1>
                </a>
                <p>Welcome! Please login or create an account.</p>
            </div>
            
            <?php if ($msg): ?>
                <div class="php-msg <?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>
            
            <div class="login-tabs">
                <button class="tab-btn active" data-tab="login-tab">Login</button>
                <button class="tab-btn" data-tab="register-tab">Register</button>
            </div>

            <!-- Login Form -->
            <div class="tab-content active" id="login-tab">
                <form method="POST" action="" class="login-form">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="identifier" placeholder="Email or Phone Number" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Password" required id="login-pass">
                            <i class="fas fa-eye-slash toggle-password" data-target="login-pass"></i>
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox">
                            <span>Remember me</span>
                        </label>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>

            <!-- Register Form -->
            <div class="tab-content" id="register-tab">
                <form method="POST" action="" class="login-form">
                    <input type="hidden" name="action" value="register">
                    <div class="full-name-group">
                        <label>Full Name</label>
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Email Address" required>
                        </div>
                    </div>
                    <div class="phone-group">
                        <label>Phone Number</label>
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Password (min 6 characters)" required id="reg-pass">
                            <i class="fas fa-eye-slash toggle-password" data-target="reg-pass"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" required id="confirm-pass">
                            <i class="fas fa-eye-slash toggle-password" data-target="confirm-pass"></i>
                        </div>
                    </div>
                    <button type="submit" class="login-btn">Create Account</button>
                </form>
            </div>
        </div>
    </div>
    <script>
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(button.getAttribute('data-tab')).classList.add('active');
        });
    });

    // Password toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Close button
    document.getElementById('close-login').addEventListener('click', () => {
        window.location.href = 'index.php';
    });
    </script>
</body>
</html>
