-- Community Fund Management Database
-- Run this script in phpMyAdmin to create the database and tables

CREATE DATABASE IF NOT EXISTS community_fund_management;
USE community_fund_management;

-- Table: user_accounts
CREATE TABLE IF NOT EXISTS user_accounts (
    account_no VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(10) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: admin_accounts
CREATE TABLE IF NOT EXISTS admin_accounts (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: amount (stores current balance)
CREATE TABLE IF NOT EXISTS amount (
    account_no VARCHAR(20) PRIMARY KEY,
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_no) REFERENCES user_accounts(account_no) ON DELETE CASCADE
);

-- Table: transaction_history
CREATE TABLE IF NOT EXISTS transaction_history (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_acc VARCHAR(20) NOT NULL,
    receiver_acc VARCHAR(20) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_type ENUM('transfer', 'deposit', 'withdrawal') DEFAULT 'transfer',
    status ENUM('success', 'failed') DEFAULT 'success'
);

-- Insert default admin account
-- Username: admin@bank.com | Password: admin123
INSERT INTO admin_accounts (name, email, password) VALUES 
('System Admin', 'admin@bank.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO amount (account_no, balance) VALUES 
('ACC1001', 5000.00);