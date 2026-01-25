<?php
require_once 'config.php';
requireAdminLogin();

$conn = getDBConnection();

// Get all transactions
$stmt = $conn->query("
    SELECT 
        t.transaction_id,
        t.sender_acc,
        t.receiver_acc,
        t.amount,
        t.transaction_date,
        t.transaction_type,
        t.status,
        s.name as sender_name,
        r.name as receiver_name
    FROM transaction_history t
    LEFT JOIN user_accounts s ON t.sender_acc = s.account_no
    LEFT JOIN user_accounts r ON t.receiver_acc = r.account_no
    ORDER BY t.transaction_date DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Transactions - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📊 All Transactions</h1>
            <p class="tagline">Complete transaction history</p>
        </header>

        <main class="main-content">
            <div style="margin-bottom: 20px;">
                <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <?php if ($stmt->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($transaction = $stmt->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($transaction['transaction_id']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($transaction['sender_name'] ?: 'N/A'); ?><br>
                                        <small style="color: #6c757d;"><?php echo htmlspecialchars($transaction['sender_acc']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($transaction['receiver_name'] ?: 'N/A'); ?><br>
                                        <small style="color: #6c757d;"><?php echo htmlspecialchars($transaction['receiver_acc']); ?></small>
                                    </td>
                                    <td><strong style="color: #28a745;">NPR <?php echo number_format($transaction['amount'], 2); ?></strong></td>
                                    <td><?php echo ucfirst($transaction['transaction_type']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $transaction['status'] === 'success' ? 'green' : 'red'; ?>; font-weight: 600;">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($transaction['transaction_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>No transactions found.</strong>
                </div>
            <?php endif; ?>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>