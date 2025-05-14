<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "", "ussd_bus_system");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(15) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    language_preference ENUM('en', 'rw') DEFAULT 'en',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating users table: " . $conn->error;
}

$conn->close();
?>