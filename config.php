<?php
// Database Configuration File
// Update these settings according to your XAMPP configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'community_fund_management');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_account']);
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to redirect if not logged in
function requireUserLogin() {
    if (!isUserLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Function to redirect if admin not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    }
}

// Function to generate unique account number
function generateAccountNumber($conn) {
    do {
        $accountNo = 'ACC' . rand(10000, 99999);
        $stmt = $conn->prepare("SELECT account_no FROM user_accounts WHERE account_no = ?");
        $stmt->bind_param("s", $accountNo);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $accountNo;
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>