<?php
// Database connection file
// Connects to voting_db using mysqli

// Database credentials
$servername = "localhost";  // Server address (XAMPP default: localhost)
$username = "root";         // MySQL username (XAMPP default: root)
$password = "";             // MySQL password (XAMPP default: empty)
$database = "voting_db";    // Database name

// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $database);

// Check if connection failed
if ($conn->connect_error) {
    // If connection fails, show error message
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful - we can now use $conn in other files
// Set character set to utf8 for proper Unicode support
$conn->set_charset("utf8");
?>
