<?php
require_once 'dbconnect.php';

$sql = "UPDATE user SET password = 'admin123' WHERE username = 'admin'";

if ($conn->query($sql) === TRUE) {
    echo "Password updated successfully to 'admin123' for user 'admin'";
} else {
    echo "Error updating password: " . $conn->error;
}

$conn->close();
?>
