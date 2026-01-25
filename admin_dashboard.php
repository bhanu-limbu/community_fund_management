<?php
require_once 'config.php';
requireAdminLogin();

$conn = getDBConnection();

// Get statistics
$stmt = $conn->query("SELECT COUNT(*) as total_users FROM user_accounts");
$total_users = $stmt->fetch_assoc()['total_users'];

$stmt = $conn->query("SELECT COUNT(*) as approved_users FROM user_accounts WHERE is_approved = 1");
$approved_users = $stmt->fetch_assoc()['approved_users'];

$stmt = $conn->query("SELECT COUNT(*) as pending_users FROM user_accounts WHERE is_approved = 0");
$pending_users = $stmt->fetch_assoc()['pending_users'];

$stmt = $conn->query("SELECT COUNT(*) as total_transactions FROM transaction_history");
$total_transactions = $stmt->fetch_assoc()['total_transactions'];

$stmt = $conn->query("SELECT SUM(balance) as total_balance FROM amount");
$total_balance = $stmt->fetch_assoc()['total_balance'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🔐 Admin Dashboard</h1>
            <p class="tagline">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
        </header>

        <main class="main-content">
            <?php if ($pending_users > 0): ?>
                <div class="alert" style="background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; margin-bottom: 30px;">
                    <strong>⚠️ Attention:</strong> You have <strong><?php echo $pending_users; ?></strong> pending account approval<?php echo $pending_users > 1 ? 's' : ''; ?>. 
                    <a href="admin_view_users.php" style="color: #856404; font-weight: 600; text-decoration: underline;">Review now</a>
                </div>
            <?php endif; ?>

            <h3 style="margin-bottom: 20px; color: #1e3c72;">System Statistics</h3>
            
            <div class="card-container">
                <div class="card">
                    <div class="card-icon">👥</div>
                    <h3>Total Users</h3>
                    <p style="font-size: 2em; font-weight: bold; color: #1e3c72;"><?php echo $total_users; ?></p>
                </div>

                <div class="card">
                    <div class="card-icon">✓</div>
                    <h3>Approved Users</h3>
                    <p style="font-size: 2em; font-weight: bold; color: #28a745;"><?php echo $approved_users; ?></p>
                </div>

                <div class="card">
                    <div class="card-icon">⏳</div>
                    <h3>Pending Approvals</h3>
                    <p style="font-size: 2em; font-weight: bold; color: #ffc107;"><?php echo $pending_users; ?></p>
                </div>

                <div class="card">
                    <div class="card-icon">💸</div>
                    <h3>Total Transactions</h3>
                    <p style="font-size: 2em; font-weight: bold; color: #1e3c72;"><?php echo $total_transactions; ?></p>
                </div>

                <div class="card">
                    <div class="card-icon">💰</div>
                    <h3>Total Balance</h3>
                    <p style="font-size: 2em; font-weight: bold; color: #28a745;">NPR <?php echo number_format($total_balance, 2); ?></p>
                </div>
            </div>

            <h3 style="margin: 30px 0 20px; color: #1e3c72;">Administrative Actions</h3>
            <div class="nav-menu">
                <a href="admin_view_users.php" class="nav-item">
                    <span class="nav-item-icon">👥</span>
                    <strong>View All Users</strong>
                    <?php if ($pending_users > 0): ?>
                        <span style="background: #ffc107; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; margin-top: 5px; display: inline-block;"><?php echo $pending_users; ?> Pending</span>
                    <?php endif; ?>
                </a>
                <a href="admin_view_transactions.php" class="nav-item">
                    <span class="nav-item-icon">📊</span>
                    <strong>View All Transactions</strong>
                </a>
                <a href="admin_close_account.php" class="nav-item">
                    <span class="nav-item-icon">🗑️</span>
                    <strong>Close User Account</strong>
                </a>
                <a href="admin_logout.php" class="nav-item" style="background: #dc3545; color: white;">
                    <span class="nav-item-icon">🚪</span>
                    <strong>Logout</strong>
                </a>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>