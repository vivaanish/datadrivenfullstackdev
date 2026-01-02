<?php
// Database connection variables for local XAMPP
$servername = "127.0.0.1";     // Use IP to bypass socket permission issues
$username   = "root";          // default XAMPP MySQL username
$password   = "";              // leave blank on macOS XAMPP
$database   = "admin_cycling123";  // Correct database name from sql file

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
