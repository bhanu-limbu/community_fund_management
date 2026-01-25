<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    // Sanitize and validate inputs
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact = sanitizeInput($_POST['contact']);
    $address = sanitizeInput($_POST['address']);
    $opening_balance = floatval($_POST['opening_balance']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($contact) || empty($address)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $password)) {
        $error = "Password must contain only letters and/or numbers!";
    } elseif (!preg_match('/^[0-9]{10}$/', $contact)) {
        $error = "Contact number must be exactly 10 digits and contain only numbers!";
    } elseif ($opening_balance < 0) {
        $error = "Opening balance cannot be negative!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "This email address is already registered!";
        } else {
            // Check if contact number already exists
            $stmt = $conn->prepare("SELECT contact FROM user_accounts WHERE contact = ?");
            $stmt->bind_param("s", $contact);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "This contact number is already registered!";
            } else {
                // Generate unique account number
                $account_no = generateAccountNumber($conn);
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Insert user account with is_approved = 0 (inactive)
                    $stmt = $conn->prepare("INSERT INTO user_accounts (account_no, name, email, password, contact, address, is_approved) VALUES (?, ?, ?, ?, ?, ?, 0)");
                    $stmt->bind_param("ssssss", $account_no, $name, $email, $hashed_password, $contact, $address);
                    $stmt->execute();
                    
                    // Insert initial balance
                    $stmt = $conn->prepare("INSERT INTO amount (account_no, balance) VALUES (?, ?)");
                    $stmt->bind_param("sd", $account_no, $opening_balance);
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $success = "Account created successfully! Your Account Number is: <strong>$account_no</strong><br><br>
                    <div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-top: 10px;'>
                        <strong>⚠️ Important:</strong> Your account is pending approval by the administrator. You will be able to login once your account is approved.
                    </div>";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Registration failed! Please try again.";
                }
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
    <title>Register - Bank Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🏦 Open New Account</h1>
            <p class="tagline">Join us and start banking today</p>
        </header>

        <main class="main-content">
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>📋 Registration Requirements:</strong>
                        <ul style="margin: 10px 0 0 20px; text-align: left;">
                            <li>Password: At least 6 characters (letters and/or numbers only)</li>
                            <li>Contact: Exactly 10 digits (numbers only)</li>
                            <li>Account will be activated after admin approval</li>
                        </ul>
                    </div>

                    <form method="POST" action="" id="registerForm">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required minlength="6" pattern="[a-zA-Z0-9]{6,}" title="Password must be at least 6 characters and contain only letters and/or numbers">
                            <small style="color: #6c757d;">At least 6 characters (letters and/or numbers only)</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="form-group">
                            <label for="contact">Contact Number *</label>
                            <input type="text" id="contact" name="contact" required pattern="[0-9]{10}" maxlength="10" title="Contact number must be exactly 10 digits">
                            <small style="color: #6c757d;">Exactly 10 digits (numbers only)</small>
                        </div>

                        <div class="form-group">
                            <label for="address">Address *</label>
                            <textarea id="address" name="address" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="opening_balance">Opening Balance (NPR) *</label>
                            <input type="number" id="opening_balance" name="opening_balance" step="0.01" min="0" value="0" required>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 10px;">Create Account</button>
                    </form>

                    <p style="text-align: center; margin-top: 20px;">
                        Already have an account? <a href="login.php" style="color: #1e3c72; font-weight: 600;">Login here</a>
                    </p>
                <?php endif; ?>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2026 Bank Management System. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Client-side validation
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const contact = document.getElementById('contact').value;
            const openingBalance = parseFloat(document.getElementById('opening_balance').value);
            
            // Password validation
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            if (!/^[a-zA-Z0-9]{6,}$/.test(password)) {
                e.preventDefault();
                alert('Password must contain only letters and/or numbers!');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            // Contact validation
            if (!/^[0-9]{10}$/.test(contact)) {
                e.preventDefault();
                alert('Contact number must be exactly 10 digits and contain only numbers!');
                return false;
            }
            
            // Balance validation
            if (openingBalance < 0) {
                e.preventDefault();
                alert('Opening balance cannot be negative!');
                return false;
            }
        });

        // Real-time contact validation
        document.getElementById('contact')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    </script>
</body>
</html>