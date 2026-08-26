<?php
require_once 'php/db.php';

/** @var mysqli $conn */
global $conn;

// 1. Drop existing table
$drop_sql = "DROP TABLE IF EXISTS users";
if (mysqli_query($conn, $drop_sql)) {
    echo "Old table deleted successfully.<br>";
} else {
    die("Error deleting old table: " . mysqli_error($conn));
}

// 2. Create new table with Recovery Key column
$create_sql = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    recovery_key VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_sql)) {
    echo "New 'users' table created successfully with Recovery Key support!";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>