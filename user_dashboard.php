<?php
require_once 'config.php';
requireUserLogin();

$conn = getDBConnection();
$account_no = $_SESSION['user_account'];
$name = $_SESSION['user_name'];

// Get current balance
$stmt = $conn->prepare("SELECT balance FROM amount WHERE account_no = ?");
$stmt->bind_param("s", $account_no);
$stmt->execute();
$result = $stmt->get_result();
$balance = $result->fetch_assoc()['balance'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🏦 Customer Dashboard</h1>
            <p class="tagline">Welcome back, <?php echo htmlspecialchars($name); ?>!</p>
        </header>

        <main class="main-content">
            <div class="dashboard-header">
                <div class="user-info">
                    <div>
                        <h2>Account Information</h2>
                        <p><strong>Account Number:</strong> <?php echo htmlspecialchars($account_no); ?></p>
                        <p><strong>Account Holder:</strong> <?php echo htmlspecialchars($name); ?></p>
                    </div>
                    <div class="balance-display">
                        <h3>Current Balance</h3>
                        <div class="balance-amount">NPR <?php echo number_format($balance, 2); ?></div>
                    </div>
                </div>
            </div>

            <h3 style="margin-bottom: 20px; color: #1e3c72;">Quick Actions</h3>
            <div class="nav-menu">
                <a href="balance_enquiry.php" class="nav-item">
                    <span class="nav-item-icon">💰</span>
                    <strong>Balance Enquiry</strong>
                </a>
                <a href="send_money.php" class="nav-item">
                    <span class="nav-item-icon">💸</span>
                    <strong>Send Money</strong>
                </a>
                <a href="transaction_history.php" class="nav-item">
                    <span class="nav-item-icon">📊</span>
                    <strong>Transaction History</strong>
                </a>
                <a href="view_profile.php" class="nav-item">
                    <span class="nav-item-icon">👤</span>
                    <strong>View Profile</strong>
                </a>
                <a href="logout.php" class="nav-item" style="background: #dc3545; color: white;">
                    <span class="nav-item-icon">🚪</span>
                    <strong>Logout</strong>
                </a>
            </div>

            <div class="alert alert-info" style="margin-top: 30px;">
                <strong>💡 Quick Tip:</strong> Keep your account credentials secure and never share them with anyone.
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>