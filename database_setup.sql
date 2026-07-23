-- Drop existing tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS MyGuest;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'collector', 'customer') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create MyGuest table (for loans)
CREATE TABLE IF NOT EXISTS MyGuest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    collector_id INT,
    loanamount DECIMAL(10,2) NOT NULL,
    closingbalance DECIMAL(10,2) NOT NULL,
    monthlyemi DECIMAL(10,2) NOT NULL,
    closingdate DATE NOT NULL,
    next_payment_date DATE NOT NULL,
    ad TEXT,
    pc VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (collector_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    customer_id INT NOT NULL,
    collector_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_type ENUM('EMI', 'EARLY_PAYMENT', 'LATE_PAYMENT') NOT NULL,
    status ENUM('PENDING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES MyGuest(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (collector_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$8K1p/a0dR1Ux5Y5Y5Y5Y5O5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y', 'admin@example.com', 'admin')
ON DUPLICATE KEY UPDATE id=id; 
