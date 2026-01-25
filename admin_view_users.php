<?php
require_once 'config.php';
requireAdminLogin();

$success = '';
$error = '';

// Handle account approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $conn = getDBConnection();
    $account_no = sanitizeInput($_POST['account_no']);
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $stmt = $conn->prepare("UPDATE user_accounts SET is_approved = 1 WHERE account_no = ?");
        $stmt->bind_param("s", $account_no);
        if ($stmt->execute()) {
            $success = "Account $account_no has been approved successfully!";
        } else {
            $error = "Failed to approve account!";
        }
    } elseif ($action == 'reject') {
        // Delete the account if rejected
        $stmt = $conn->prepare("DELETE FROM user_accounts WHERE account_no = ?");
        $stmt->bind_param("s", $account_no);
        if ($stmt->execute()) {
            $success = "Account $account_no has been rejected and deleted!";
        } else {
            $error = "Failed to reject account!";
        }
    }
    
    $conn->close();
}

$conn = getDBConnection();

// Get all users with their balances
$stmt = $conn->query("
    SELECT 
        u.account_no,
        u.name,
        u.email,
        u.contact,
        u.address,
        u.is_approved,
        a.balance,
        u.created_at
    FROM user_accounts u
    LEFT JOIN amount a ON u.account_no = a.account_no
    ORDER BY u.is_approved ASC, u.created_at DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>👥 All User Accounts</h1>
            <p class="tagline">Manage customer accounts</p>
        </header>

        <main class="main-content">
            <div style="margin-bottom: 20px;">
                <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($stmt->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Account No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $stmt->fetch_assoc()): ?>
                                <tr style="<?php echo $user['is_approved'] == 0 ? 'background: #fff3cd;' : ''; ?>">
                                    <td><strong><?php echo htmlspecialchars($user['account_no']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['contact']); ?></td>
                                    <td><?php echo htmlspecialchars($user['address']); ?></td>
                                    <td><strong style="color: green;">NPR <?php echo number_format($user['balance'], 2); ?></strong></td>
                                    <td>
                                        <?php if ($user['is_approved'] == 1): ?>
                                            <span style="color: green; font-weight: 600;">✓ Approved</span>
                                        <?php else: ?>
                                            <span style="color: orange; font-weight: 600;">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['is_approved'] == 0): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="account_no" value="<?php echo htmlspecialchars($user['account_no']); ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-success" style="padding: 5px 10px; margin-right: 5px;" onclick="return confirm('Approve this account?')">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Reject and delete this account?')">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>No users found.</strong>
                </div>
            <?php endif; ?>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>