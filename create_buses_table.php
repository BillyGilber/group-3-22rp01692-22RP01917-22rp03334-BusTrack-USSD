<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "", "ussd_bus_system");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create buses table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS buses (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    route_id VARCHAR(10) NOT NULL UNIQUE,
    location VARCHAR(100) NOT NULL,
    eta TIME NOT NULL,
    seats_available INT(3) NOT NULL DEFAULT 50,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Buses table created successfully\n";
    
    // Insert sample buses
    $sample_buses = [
        ['RAB 123B', 'Kigali City Center', '14:30', 45, 'active'],
        ['RAB 456C', 'Nyabugogo', '15:00', 38, 'active'],
        ['RAB 789D', 'Remera', '15:15', 50, 'active'],
        ['RAB 234E', 'Kimironko', '15:45', 42, 'active']
    ];
    
    // Prepare the insert statement
    $stmt = $conn->prepare("INSERT IGNORE INTO buses (route_id, location, eta, seats_available, status) VALUES (?, ?, ?, ?, ?)");
    
    // Insert each sample bus
    foreach ($sample_buses as $bus) {
        $stmt->bind_param("sssis", $bus[0], $bus[1], $bus[2], $bus[3], $bus[4]);
        $stmt->execute();
    }
    
    echo "Sample buses added successfully\n";
} else {
    echo "Error creating buses table: " . $conn->error;
}

$conn->close();
?> 