-- PROPLEDGER Database Setup Script
-- This will create the database and all required tables

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS propledger_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE propledger_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    user_type VARCHAR(50) NOT NULL CHECK (user_type IN ('investor', 'property_owner', 'agent', 'developer')),
    password_hash VARCHAR(255) NOT NULL,
    newsletter_subscribed BOOLEAN DEFAULT FALSE,
    wallet_address VARCHAR(255),
    oauth_provider VARCHAR(50),
    oauth_id VARCHAR(255),
    profile_picture_url VARCHAR(500),
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_oauth (oauth_provider, oauth_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agents table
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_number VARCHAR(100) NOT NULL,
    experience VARCHAR(100) NOT NULL,
    specialization VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    agency VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    status VARCHAR(20) DEFAULT 'approved' CHECK (status IN ('pending', 'approved', 'rejected')),
    commission_rate DECIMAL(5,2),
    total_sales DECIMAL(15,2),
    rating DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    token_price DECIMAL(10,2) NOT NULL,
    total_tokens INT NOT NULL,
    available_tokens INT NOT NULL,
    property_type VARCHAR(100) NOT NULL,
    owner_id INT NOT NULL,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (owner_id) REFERENCES users(id),
    INDEX idx_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Manager messages table
CREATE TABLE IF NOT EXISTS manager_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    manager_name VARCHAR(255) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority VARCHAR(20) DEFAULT 'normal' CHECK (priority IN ('normal', 'high', 'urgent')),
    status VARCHAR(20) DEFAULT 'unread' CHECK (status IN ('unread', 'read', 'replied')),
    sender_type VARCHAR(20) DEFAULT 'user' CHECK (sender_type IN ('user', 'agent')),
    receiver_type VARCHAR(20) DEFAULT 'agent' CHECK (receiver_type IN ('user', 'agent')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP,
    reply_message TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_manager (manager_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAuth states table (for OAuth security)
CREATE TABLE IF NOT EXISTS oauth_states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_token VARCHAR(255) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_state (state_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert demo users for testing
INSERT INTO users (full_name, email, phone, country, user_type, password_hash, newsletter_subscribed) VALUES
('John Investor', 'investor@propledger.com', '+92-300-1234567', 'Pakistan', 'investor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE),
('Sarah Agent', 'agent@propledger.com', '+92-300-7654321', 'Pakistan', 'agent', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE)
ON DUPLICATE KEY UPDATE id=id;

-- Insert demo agent data
INSERT INTO agents (user_id, license_number, experience, specialization, city, agency, phone, status, rating) 
SELECT users.id, 'LIC-2024-001', '5 years', 'Residential Properties', 'Islamabad', 'PropLedger Realty', '+92-300-7654321', 'approved', 4.8
FROM users WHERE email = 'agent@propledger.com'
ON DUPLICATE KEY UPDATE agents.id=agents.id;

-- Success message
SELECT 'Database setup completed successfully!' AS message;
SELECT 'Demo accounts created:' AS info;
SELECT 'Investor: investor@propledger.com / password' AS account1;
SELECT 'Agent: agent@propledger.com / password' AS account2;
