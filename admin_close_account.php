<?php
require_once 'config.php';
requireAdminLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $account_no = sanitizeInput($_POST['account_no']);
    
    if (empty($account_no)) {
        $error = "Please enter account number!";
    } else {
        // Check if account exists
        $stmt = $conn->prepare("SELECT account_no, name FROM user_accounts WHERE account_no = ?");
        $stmt->bind_param("s", $account_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Account not found!";
        } else {
            $user = $result->fetch_assoc();
            
            // Delete account (CASCADE will delete from amount table)
            $stmt = $conn->prepare("DELETE FROM user_accounts WHERE account_no = ?");
            $stmt->bind_param("s", $account_no);
            
            if ($stmt->execute()) {
                $success = "Account " . htmlspecialchars($account_no) . " belonging to " . htmlspecialchars($user['name']) . " has been successfully closed.";
            } else {
                $error = "Failed to close account. Please try again.";
            }
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
    <title>Close Account - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🗑️ Close User Account</h1>
            <p class="tagline">Administrative Action</p>
        </header>

        <main class="main-content">
            <div class="form-container">
                <div class="alert" style="background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">
                    <strong>⚠️ Warning:</strong> This action is permanent and cannot be undone. All account data will be deleted.
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="admin_view_users.php" class="btn btn-primary">View All Users</a>
                        <a href="admin_close_account.php" class="btn btn-secondary">Close Another Account</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" id="closeAccountForm">
                        <div class="form-group">
                            <label for="account_no">Account Number to Close *</label>
                            <input type="text" id="account_no" name="account_no" required placeholder="Enter account number">
                        </div>

                        <button type="submit" class="btn btn-danger" style="width: 100%; margin-top: 10px;">Close Account</button>
                    </form>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="admin_dashboard.php" style="color: #6c757d;">← Back to Dashboard</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.getElementById('closeAccountForm')?.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to close this account? This action cannot be undone!')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>