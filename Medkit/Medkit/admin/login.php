<?php
require_once __DIR__ . '/../includes/config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'Admin not found';
        } else {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                redirect('dashboard.php');
            } else {
                $error = 'Incorrect password';
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
    <title>Admin Login - Medkit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h1>Admin Panel</h1>
                <p>Medkit Management System</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert error show"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="admin@gmail.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" placeholder="Enter password" required>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                <div class="login-back">
                    <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
