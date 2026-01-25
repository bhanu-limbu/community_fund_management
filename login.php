<?php
require_once 'config.php';

// Redirect if already logged in
if (isUserLoggedIn()) {
    header("Location: user_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $account_no = sanitizeInput($_POST['account_no']);
    $password = $_POST['password'];
    
    if (empty($account_no) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT account_no, password, name, is_approved FROM user_accounts WHERE account_no = ?");
        $stmt->bind_param("s", $account_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if account is approved
                if ($user['is_approved'] == 0) {
                    $error = "Your account is pending approval by the administrator. Please wait for admin approval before logging in.";
                } else {
                    // Set session variables
                    $_SESSION['user_account'] = $user['account_no'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    header("Location: user_dashboard.php");
                    exit();
                }
            } else {
                $error = "Invalid credentials!";
            }
        } else {
            $error = "Invalid credentials!";
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🏦 Customer Login</h1>
            <p class="tagline">Access your account securely</p>
        </header>

        <main class="main-content">
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="account_no">Account Number</label>
                        <input type="text" id="account_no" name="account_no" required placeholder="Enter your account number">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Login</button>
                </form>

                <div style="text-align: center; margin-top: 20px;">
                    <p>Don't have an account? <a href="register.php" style="color: #1e3c72; font-weight: 600;">Register here</a></p>
                    <p style="margin-top: 10px;"><a href="index.html" style="color: #6c757d;">← Back to Home</a></p>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>