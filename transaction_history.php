<?php
require_once 'config.php';
requireUserLogin();

$conn = getDBConnection();
$account_no = $_SESSION['user_account'];

// Get transaction history
$stmt = $conn->prepare("
    SELECT 
        t.transaction_id,
        t.sender_acc,
        t.receiver_acc,
        t.amount,
        t.transaction_date,
        t.transaction_type,
        CASE 
            WHEN t.sender_acc = ? THEN 'Debit'
            WHEN t.receiver_acc = ? THEN 'Credit'
        END as transaction_mode
    FROM transaction_history t
    WHERE t.sender_acc = ? OR t.receiver_acc = ?
    ORDER BY t.transaction_date DESC
");
$stmt->bind_param("ssss", $account_no, $account_no, $account_no, $account_no);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Community Fund Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📊 Transaction History</h1>
            <p class="tagline">View your transaction records</p>
        </header>

        <main class="main-content">
            <div style="margin-bottom: 20px;">
                <a href="user_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Type</th>
                                <th>From Account</th>
                                <th>To Account</th>
                                <th>Amount</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($transaction = $result->fetch_assoc()): ?>
                                <tr style="<?php echo $transaction['transaction_mode'] === 'Credit' ? 'background: #d4edda;' : 'background: #f8d7da;'; ?>">
                                    <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                    <td>
                                        <strong style="color: <?php echo $transaction['transaction_mode'] === 'Credit' ? 'green' : 'red'; ?>">
                                            <?php echo $transaction['transaction_mode']; ?>
                                        </strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($transaction['sender_acc']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['receiver_acc']); ?></td>
                                    <td>
                                        <strong>
                                            <?php echo $transaction['transaction_mode'] === 'Credit' ? '+' : '-'; ?>
                                            NPR <?php echo number_format($transaction['amount'], 2); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($transaction['transaction_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>No transactions found.</strong> Start sending money to see your transaction history.
                </div>
            <?php endif; ?>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Community Fund Management. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>