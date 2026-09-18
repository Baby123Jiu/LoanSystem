CREATE DATABASE IF NOT EXISTS loan_system_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE loan_system_db;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    loan_amount DECIMAL(12,2) NOT NULL,
    duration INT NOT NULL,
    duration_unit ENUM('years', 'months') NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    interest_method ENUM('flat', 'reducing') NOT NULL,
    repayment_frequency ENUM('monthly', 'yearly') NOT NULL,
    total_interest DECIMAL(12,2) DEFAULT 0,
    total_payable DECIMAL(12,2) DEFAULT 0,
    installment_amount DECIMAL(12,2) DEFAULT 0,
    remaining_balance DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending', 'approved', 'active', 'completed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id)
        REFERENCES customers(customer_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    remaining_balance DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (loan_id)
        REFERENCES loans(loan_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4;

-- Archive tables: where deleted records actually live.
-- These are NOT linked with foreign keys back to the live tables,
-- since the whole point is they can outlive the record they came from.

CREATE TABLE archived_customers (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    address VARCHAR(255),
    created_at TIMESTAMP NOT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE TABLE archived_loans (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    customer_id INT NOT NULL,
    loan_amount DECIMAL(12,2) NOT NULL,
    duration INT NOT NULL,
    duration_unit ENUM('years', 'months') NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    interest_method ENUM('flat', 'reducing') NOT NULL,
    repayment_frequency ENUM('monthly', 'yearly') NOT NULL,
    total_interest DECIMAL(12,2) DEFAULT 0,
    total_payable DECIMAL(12,2) DEFAULT 0,
    installment_amount DECIMAL(12,2) DEFAULT 0,
    remaining_balance DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending', 'approved', 'active', 'completed', 'rejected') NOT NULL,
    created_at TIMESTAMP NOT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE TABLE archived_payments (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    loan_id INT NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    remaining_balance DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4;