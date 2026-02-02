-- ============================================
-- PARADISE HOTEL & RESORT DATABASE SETUP
-- Complete Database Schema with All Tables and Data
-- ============================================

CREATE DATABASE IF NOT EXISTS hotel_reservation;
USE hotel_reservation;

-- ============================================
-- MAIN TABLES
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reservations table with payment support and guest booking capability
CREATE TABLE IF NOT EXISTS reservations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,  -- Allow NULL for guest bookings
    guest_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    checkin_date DATE NOT NULL,
    checkout_date DATE NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    guests INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    options TEXT,
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_percentage INT DEFAULT 0,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Room prices table for admin-controlled pricing
CREATE TABLE IF NOT EXISTS room_prices (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(50) NOT NULL,
    pax_group INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_room_pax (room_type, pax_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Website photos table for admin photo management
CREATE TABLE IF NOT EXISTS website_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    INDEX idx_section (section),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Individual room images table (SUPPORTS MULTIPLE IMAGES PER ROOM)
CREATE TABLE IF NOT EXISTS room_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    pax_group INT(11) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    INDEX idx_room_number (room_number),
    INDEX idx_room_type (room_type),
    INDEX idx_pax_group (pax_group),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order),
    INDEX idx_room_lookup (room_number, room_type, pax_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DEFAULT DATA
-- ============================================

-- Insert default room prices
INSERT INTO room_prices (room_type, pax_group, price) VALUES
('Regular', 2, 1500.00),
('Regular', 8, 3000.00),
('Regular', 20, 6000.00),
('Deluxe', 2, 2500.00),
('Deluxe', 8, 4500.00),
('Deluxe', 20, 8500.00),
('VIP', 2, 4000.00),
('VIP', 8, 7000.00),
('VIP', 20, 12000.00)
ON DUPLICATE KEY UPDATE price=VALUES(price);

-- Insert default admin account
-- Username: admin, Password: admin123, Email: admin@paradisehotel.com
INSERT INTO users (username, email, password, full_name, is_admin) 
VALUES ('admin', 'admin@paradisehotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 1)
ON DUPLICATE KEY UPDATE 
password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
is_admin = 1;

-- ============================================
-- PERFORMANCE INDEXES
-- ============================================

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_user_id ON reservations(user_id);
CREATE INDEX IF NOT EXISTS idx_checkin_date ON reservations(checkin_date);
CREATE INDEX IF NOT EXISTS idx_status ON reservations(status);

-- ============================================
-- TROUBLESHOOTING SECTION
-- ============================================

-- Guest Booking Support: Allow NULL user_id for guest reservations
-- This allows users to book without logging in, login is only required for payment
ALTER TABLE reservations MODIFY COLUMN user_id INT(11) NULL;

-- Update foreign key constraint to handle NULL properly (if it exists)
-- Note: This may fail if constraint doesn't exist, which is fine
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'hotel_reservation' 
    AND TABLE_NAME = 'reservations' 
    AND REFERENCED_TABLE_NAME = 'users'
    LIMIT 1
);

SET @sql = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE reservations DROP FOREIGN KEY ', @constraint_name), 
    'SELECT "No foreign key to drop" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint with proper NULL handling
ALTER TABLE reservations ADD CONSTRAINT fk_reservations_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- If you encounter "Duplicate entry" errors for room images, run these commands:
-- (These will fail silently if constraints don't exist)

-- Remove any unique constraints that prevent multiple images per room
-- ALTER TABLE room_images DROP INDEX unique_room_image;
-- ALTER TABLE room_images DROP INDEX room_number;
-- ALTER TABLE room_images DROP INDEX room_number_2;
-- ALTER TABLE room_images DROP INDEX room_type;
-- ALTER TABLE room_images DROP INDEX pax_group;
-- ALTER TABLE room_images DROP INDEX unique_room_pax;
-- ALTER TABLE room_images DROP INDEX room_images_unique;

-- If sort_order column is missing, add it:
-- ALTER TABLE room_images ADD COLUMN sort_order INT DEFAULT 0;

-- Test multiple image insertion (should work without errors):
-- INSERT INTO room_images (room_number, room_type, pax_group, filename, original_name, file_path, file_size, mime_type, sort_order) 
-- VALUES ('TEST', 'Regular', 2, 'test1.jpg', 'test1.jpg', 'test/test1.jpg', 1000, 'image/jpeg', 1);
-- INSERT INTO room_images (room_number, room_type, pax_group, filename, original_name, file_path, file_size, mime_type, sort_order) 
-- VALUES ('TEST', 'Regular', 2, 'test2.jpg', 'test2.jpg', 'test/test2.jpg', 1000, 'image/jpeg', 2);
-- DELETE FROM room_images WHERE room_number = 'TEST';

-- ============================================
-- NUCLEAR OPTION (Complete Table Recreation)
-- ============================================
-- Only use this if you have persistent unique constraint issues

/*
-- Backup existing data
CREATE TABLE room_images_backup AS SELECT * FROM room_images;

-- Drop and recreate table
DROP TABLE room_images;

CREATE TABLE room_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    pax_group INT(11) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    INDEX idx_room_number (room_number),
    INDEX idx_room_type (room_type),
    INDEX idx_pax_group (pax_group),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order),
    INDEX idx_room_lookup (room_number, room_type, pax_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Restore data
INSERT INTO room_images SELECT * FROM room_images_backup;

-- Drop backup table
DROP TABLE room_images_backup;
*/

-- ============================================
-- SETUP INSTRUCTIONS FOR NEW PC
-- ============================================

/*
COMPLETE SETUP INSTRUCTIONS:

1. CREATE DATABASE:
   - Import this entire file into MySQL/phpMyAdmin
   - Database name: hotel_reservation

2. ADMIN CREDENTIALS:
   - Username: admin
   - Password: admin123
   - Email: admin@paradisehotel.com

3. ACCESS URLS (replace 'your-folder' with actual folder name):
   - Main Website: http://localhost/your-folder/index.php
   - Admin Access: http://localhost/your-folder/admin_access.php
   - Admin Login: http://localhost/your-folder/admin/login.php

4. FEATURES INCLUDED:
   ✅ Guest booking (no login required until payment)
   ✅ Room galleries (Regular, Deluxe, VIP)
   ✅ Admin panel with photo management
   ✅ Room pricing system
   ✅ Reservation management
   ✅ Payment processing

5. FOLDER STRUCTURE REQUIRED:
   - uploads/carousel/
   - uploads/pool/
   - uploads/spa/
   - uploads/restaurant/
   - uploads/rooms/regular/
   - uploads/rooms/deluxe/
   - uploads/rooms/individual/ (for VIP)

6. TROUBLESHOOTING:
   - If admin login fails: Run admin_reset.php
   - If booking fails: Check user_id column allows NULL
   - If images don't show: Check upload folder permissions
*/
-- ============================================
-- VERIFICATION COMMANDS
-- ============================================
-- Run these to check your database structure:

-- Check table structure:
-- DESCRIBE reservations;

-- Verify user_id allows NULL:
-- SHOW COLUMNS FROM reservations LIKE 'user_id';

-- Check indexes (should show NO unique constraints except PRIMARY):
-- SHOW INDEX FROM room_images;

-- Check room prices:
-- SELECT * FROM room_prices ORDER BY room_type, pax_group;

-- Check admin user:
-- SELECT username, email, is_admin FROM users WHERE is_admin = 1;

-- Test guest booking capability:
-- INSERT INTO reservations (user_id, guest_name, email, phone, checkin_date, checkout_date, room_type, guests, price, status) 
-- VALUES (NULL, 'Test Guest', 'test@example.com', '1234567890', '2024-12-25', '2024-12-27', 'Regular', 2, 1500.00, 'pending');
-- DELETE FROM reservations WHERE guest_name = 'Test Guest';