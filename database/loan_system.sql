CREATE DATABASE IF NOT EXISTS loan_system_db;

USE loan_system_db;

CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    loan_amount DECIMAL(12,2) NOT NULL,
    duration INT NOT NULL,
    duration_unit VARCHAR(20) NOT NULL,
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
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    remaining_balance DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (loan_id)
        REFERENCES loans(loan_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);