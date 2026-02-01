<?php
require_once 'config.php';
requireUserLogin();

$conn = getDBConnection();
$account_no = $_SESSION['user_account'];

// Get account details and balance
$stmt = $conn->prepare("
    SELECT u.account_no, u.name, u.email, u.contact, a.balance, a.last_updated
    FROM user_accounts u
    JOIN amount a ON u.account_no = a.account_no
    WHERE u.account_no = ?
");
$stmt->bind_param("s", $account_no);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Enquiry - Community Fund Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>💰 Balance Enquiry</h1>
            <p class="tagline">View your current account balance</p>
        </header>

        <main class="main-content">
            <div class="form-container" style="max-width: 600px;">
                <div class="dashboard-header">
                    <div class="balance-display" style="margin: 0 auto;">
                        <h3>Current Balance</h3>
                        <div class="balance-amount">NPR <?php echo number_format($account['balance'], 2); ?></div>
                        <p style="font-size: 0.9em; margin-top: 10px; opacity: 0.8;">
                            Last Updated: <?php echo date('d M Y, h:i A', strtotime($account['last_updated'])); ?>
                        </p>
                    </div>
                </div>

                <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-top: 30px;">
                    <h3 style="color: #1e3c72; margin-bottom: 15px;">Account Details</h3>
                    <p><strong>Account Number:</strong> <?php echo htmlspecialchars($account['account_no']); ?></p>
                    <p><strong>Account Holder:</strong> <?php echo htmlspecialchars($account['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($account['email']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($account['contact']); ?></p>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="user_dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Community Fund Management. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>