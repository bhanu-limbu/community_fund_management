<?php
require_once 'config.php';
requireUserLogin();

$error = '';
$success = '';
$sender_account = $_SESSION['user_account'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $receiver_account = sanitizeInput($_POST['receiver_account']);
    $amount = floatval($_POST['amount']);
    
    // Validation
    if (empty($receiver_account) || $amount <= 0) {
        $error = "Please enter valid receiver account and amount!";
    } elseif ($sender_account === $receiver_account) {
        $error = "Cannot transfer to your own account!";
    } else {
        // Check if receiver account exists
        $stmt = $conn->prepare("SELECT account_no, name FROM user_accounts WHERE account_no = ?");
        $stmt->bind_param("s", $receiver_account);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Receiver account does not exist!";
        } else {
            $receiver = $result->fetch_assoc();
            
            // Check sender's balance
            $stmt = $conn->prepare("SELECT balance FROM amount WHERE account_no = ?");
            $stmt->bind_param("s", $sender_account);
            $stmt->execute();
            $result = $stmt->get_result();
            $sender_balance = $result->fetch_assoc()['balance'];
            
            if ($sender_balance < $amount) {
                $error = "Insufficient balance! Your current balance is NPR " . number_format($sender_balance, 2);
            } else {
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Deduct from sender
                    $stmt = $conn->prepare("UPDATE amount SET balance = balance - ? WHERE account_no = ?");
                    $stmt->bind_param("ds", $amount, $sender_account);
                    $stmt->execute();
                    
                    // Add to receiver
                    $stmt = $conn->prepare("UPDATE amount SET balance = balance + ? WHERE account_no = ?");
                    $stmt->bind_param("ds", $amount, $receiver_account);
                    $stmt->execute();
                    
                    // Record transaction
                    $stmt = $conn->prepare("INSERT INTO transaction_history (sender_acc, receiver_acc, amount, transaction_type) VALUES (?, ?, ?, 'transfer')");
                    $stmt->bind_param("ssd", $sender_account, $receiver_account, $amount);
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $success = "NPR " . number_format($amount, 2) . " transferred successfully to " . htmlspecialchars($receiver['name']) . " (Account: " . htmlspecialchars($receiver_account) . ")";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Transaction failed! Please try again.";
                }
            }
        }
    }
    
    $conn->close();
}

// Get current balance
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT balance FROM amount WHERE account_no = ?");
$stmt->bind_param("s", $sender_account);
$stmt->execute();
$result = $stmt->get_result();
$current_balance = $result->fetch_assoc()['balance'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>💸 Send Money</h1>
            <p class="tagline">Transfer funds securely</p>
        </header>

        <main class="main-content">
            <div class="form-container">
                <div class="alert alert-info">
                    <strong>Available Balance:</strong> NPR <?php echo number_format($current_balance, 2); ?>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="transaction_history.php" class="btn btn-primary">View Transactions</a>
                        <a href="send_money.php" class="btn btn-secondary">Send Again</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" id="sendMoneyForm">
                        <div class="form-group">
                            <label for="receiver_account">Receiver Account Number *</label>
                            <input type="text" id="receiver_account" name="receiver_account" required placeholder="Enter receiver's account number">
                        </div>

                        <div class="form-group">
                            <label for="amount">Amount (NPR) *</label>
                            <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="Enter amount to transfer">
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 10px;">Transfer Money</button>
                    </form>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="user_dashboard.php" style="color: #6c757d;">← Back to Dashboard</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.getElementById('sendMoneyForm')?.addEventListener('submit', function(e) {
            const amount = parseFloat(document.getElementById('amount').value);
            const availableBalance = <?php echo $current_balance; ?>;
            
            if (amount <= 0) {
                e.preventDefault();
                alert('Amount must be greater than zero!');
                return false;
            }
            
            if (amount > availableBalance) {
                e.preventDefault();
                alert('Insufficient balance!');
                return false;
            }
            
            if (!confirm(`Confirm transfer of NPR ${amount.toFixed(2)}?`)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
