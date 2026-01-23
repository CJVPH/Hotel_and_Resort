<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_reservation');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Initialize database tables
function initDatabase() {
    $conn = getDBConnection();
    
    // Create users table
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Create reservations table
    $sql_reservations = "CREATE TABLE IF NOT EXISTS reservations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        guest_name VARCHAR(100) NOT NULL,
        checkin_date DATE NOT NULL,
        checkout_date DATE NOT NULL,
        room_type VARCHAR(50) NOT NULL,
        guests INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        options TEXT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Create room_images table
    $sql_room_images = "CREATE TABLE IF NOT EXISTS room_images (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        room_type VARCHAR(50) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        display_order INT(11) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_room_type (room_type)
    )";
    
    $conn->query($sql_users);
    $conn->query($sql_reservations);
    $conn->query($sql_room_images);

    // Create room_prices table for storing dynamic room pricing
    $sql_room_prices = "CREATE TABLE IF NOT EXISTS room_prices (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        room_type VARCHAR(50) NOT NULL,
        pax_group INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_room_pax (room_type, pax_group)
    )";
    $conn->query($sql_room_prices);

    // Seed default room prices if table is empty
    $countRes = $conn->query("SELECT COUNT(*) AS cnt FROM room_prices");
    $row = $countRes->fetch_assoc();
    if (isset($row['cnt']) && intval($row['cnt']) === 0) {
        $defaults = [
            ['Regular', 2, 1500],
            ['Regular', 8, 3000],
            ['Regular', 20, 6000],
            ['Deluxe', 2, 2500],
            ['Deluxe', 8, 4500],
            ['Deluxe', 20, 8500],
            ['VIP', 2, 4000],
            ['VIP', 8, 7000],
            ['VIP', 20, 12000]
        ];

        $stmt = $conn->prepare("INSERT INTO room_prices (room_type, pax_group, price) VALUES (?, ?, ?)");
        foreach ($defaults as $d) {
            $stmt->bind_param('sid', $d[0], $d[1], $d[2]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Add is_admin column if it doesn't exist (for existing databases)
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if ($columnCheck->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
    }

    // Add options column to reservations table if it doesn't exist
    $colOptions = $conn->query("SHOW COLUMNS FROM reservations LIKE 'options'");
    if ($colOptions->num_rows == 0) {
        $conn->query("ALTER TABLE reservations ADD COLUMN options TEXT NULL");
    }
    
    // Create admin account if it doesn't exist
    $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    if ($checkAdmin->num_rows == 0) {
        // Default admin password: admin123
        $adminUsername = 'admin';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $adminName = 'Administrator';
        $adminEmail = 'admin@paradisehotel.com';
        $isAdmin = 1;
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $adminUsername, $adminEmail, $adminPassword, $adminName, $isAdmin);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->close();
}

// Initialize database on first run
initDatabase();
?>

