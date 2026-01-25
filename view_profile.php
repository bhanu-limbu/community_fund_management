<?php
require_once 'config.php';
requireUserLogin();

$conn = getDBConnection();
$account_no = $_SESSION['user_account'];

// Get user profile
$stmt = $conn->prepare("
    SELECT u.*, a.balance, u.created_at
    FROM user_accounts u
    JOIN amount a ON u.account_no = a.account_no
    WHERE u.account_no = ?
");
$stmt->bind_param("s", $account_no);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>👤 My Profile</h1>
            <p class="tagline">View your account information</p>
        </header>

        <main class="main-content">
            <div class="form-container" style="max-width: 700px;">
                <div class="dashboard-header">
                    <div style="text-align: center;">
                        <div style="font-size: 4em; margin-bottom: 10px;">👤</div>
                        <h2><?php echo htmlspecialchars($profile['name']); ?></h2>
                        <p style="opacity: 0.8;">Account No: <?php echo htmlspecialchars($profile['account_no']); ?></p>
                    </div>
                </div>

                <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; margin-top: 30px;">
                    <h3 style="color: #1e3c72; margin-bottom: 20px;">Personal Information</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                        <div><strong>Full Name:</strong></div>
                        <div><?php echo htmlspecialchars($profile['name']); ?></div>
                        
                        <div><strong>Email:</strong></div>
                        <div><?php echo htmlspecialchars($profile['email']); ?></div>
                        
                        <div><strong>Contact Number:</strong></div>
                        <div><?php echo htmlspecialchars($profile['contact']); ?></div>
                        
                        <div><strong>Address:</strong></div>
                        <div><?php echo htmlspecialchars($profile['address']); ?></div>
                        
                        <div><strong>Account Number:</strong></div>
                        <div><?php echo htmlspecialchars($profile['account_no']); ?></div>
                        
                        <div><strong>Current Balance:</strong></div>
                        <div><strong style="color: green;">NPR <?php echo number_format($profile['balance'], 2); ?></strong></div>
                        
                        <div><strong>Account Created:</strong></div>
                        <div><?php echo date('d M Y', strtotime($profile['created_at'])); ?></div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="user_dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>