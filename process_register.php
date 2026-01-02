<?php
// Include database connection
require_once 'dbconnect.php';

// Initialize error message
$error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Default stats for new players
    $kills = 0;
    $deaths = 0;

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM participant WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'This email is already registered';
            } else {
                // Insert new participant
                $stmt = $conn->prepare("
                    INSERT INTO participant (
                        firstname, surname, email, kills, deaths
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param(
                    "ssiii", 
                    $first_name, $last_name, $email, $kills, $deaths
                );
                
                if ($stmt->execute()) {
                    // Redirect to success page
                    header("Location: register.php?success=1");
                    exit();
                } else {
                    $error = 'Error registering: ' . $conn->error;
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    // If there was an error, redirect back with error message
    if (!empty($error)) {
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }
} else {
    // If not a POST request, redirect to register page
    header("Location: register.php");
    exit();
}
?>
