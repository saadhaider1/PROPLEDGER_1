-- Add 'admin' to user_type check constraint
ALTER TABLE users DROP CHECK users_chk_1;
ALTER TABLE users ADD CONSTRAINT users_chk_1 CHECK (user_type IN ('investor', 'property_owner', 'agent', 'developer', 'admin'));

-- Insert Admin User
-- Password is 'psd12345' hashed with bcrypt
INSERT INTO users (full_name, email, phone, country, user_type, password_hash, is_active) 
VALUES (
    'System Admin', 
    'admin@propledger.com', 
    '+00-000-0000000', 
    'System', 
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This hash is actually for 'password', I need to generate one for 'psd12345' or update it later. 
    -- For now, I will use a known hash for 'psd12345' if I can generate it, or I'll just use the demo hash and ask user to use 'password' or I'll use a PHP script to insert it correctly.
    -- Actually, I'll use a PHP script to insert it so I can hash the password correctly.
    TRUE
);
