<?php
// Database configuration file
// Update these credentials based on your hosting provider
$servername = "localhost";
$username   = "root";
$password   = ""; // use the password you just reset
$dbname     = "portfolio_db";
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Log error instead of displaying it on production
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if table exists
function table_exists($pdo, $table_name) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table_name LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Create tables if they don't exist
if (!table_exists($pdo, 'contacts')) {
    $sql = "CREATE TABLE contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
}

if (!table_exists($pdo, 'portfolio_items')) {
    $sql = "CREATE TABLE portfolio_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        technologies VARCHAR(500),
        image_url VARCHAR(500),
        project_url VARCHAR(500),
        featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Insert sample portfolio items
    $sample_items = [
        ['E-Commerce Platform', 'A full-featured online store with payment integration and admin panel.', 'PHP,MySQL,Bootstrap', '', '', 1],
        ['Blog Management System', 'A content management system for bloggers with rich text editor.', 'PHP,JavaScript,MySQL', '', '', 1],
        ['Task Management App', 'A productivity tool for teams to manage projects and deadlines.', 'React,PHP API,MySQL', '', '', 1],
        ['Restaurant Website', 'A modern restaurant website with online ordering system.', 'PHP,MySQL,Bootstrap', '', '', 0],
        ['School Management System', 'Complete school administration and student management system.', 'PHP,MySQL,JavaScript', '', '', 0],
        ['Real Estate Portal', 'Property listing and management portal with advanced search.', 'PHP,MySQL,Bootstrap,JavaScript', '', '', 0]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO portfolio_items (title, description, technologies, image_url, project_url, featured) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($sample_items as $item) {
        $stmt->execute($item);
    }
}

if (!table_exists($pdo, 'admin_users')) {
    $sql = "CREATE TABLE admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Create default admin user (username: admin, password: admin123)
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $default_password, 'admin@portfolio.com']);
}
?>