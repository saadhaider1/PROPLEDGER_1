-- Direct SQL to create oauth_states table
-- Run this in phpMyAdmin or MySQL command line

USE propledger_db;

-- Drop existing table if it exists
DROP TABLE IF EXISTS oauth_states;

-- Create oauth_states table
CREATE TABLE oauth_states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_token VARCHAR(255) NOT NULL UNIQUE,
    provider VARCHAR(50) NOT NULL,
    redirect_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_state_token (state_token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test insert to verify table works
INSERT INTO oauth_states (state_token, provider, expires_at) 
VALUES ('test_token_12345', 'google', DATE_ADD(NOW(), INTERVAL 1 HOUR));

-- Clean up test data
DELETE FROM oauth_states WHERE state_token = 'test_token_12345';

-- Show table structure
DESCRIBE oauth_states;
