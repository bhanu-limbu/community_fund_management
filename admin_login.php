<?php
require_once 'config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT admin_id, password, name FROM admin_accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['name'];
                
                header("Location: admin_dashboard.php");
                exit();
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
    <title>Admin Login - Community Fund Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🔐 Admin Login</h1>
            <p class="tagline">Administrative Access</p>
        </header>

        <main class="main-content">
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- <div class="alert alert-info">
                    <strong>Default Admin Credentials:</strong><br>
                    Email: admin@bank.com<br>
                    Password: admin123
                </div> -->

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Admin Email</label>
                        <input type="email" id="email" name="email" required placeholder="Enter admin email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Admin Login</button>
                </form>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.html" style="color: #6c757d;">← Back to Home</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Community Fund Management. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>