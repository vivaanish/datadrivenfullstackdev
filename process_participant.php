<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

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
    $kills = intval($_POST['kills'] ?? 0);
    $deaths = intval($_POST['deaths'] ?? 0);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'First name, last name, and email are required';
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
                $error = 'A participant with this email already exists';
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
                    // Simulate email sending
                    $email_msg = "";
                    if (isset($_POST['send_invite']) && $_POST['send_invite'] == '1') {
                        // In a real app, mail($email, "Welcome", "..."); would go here
                        $email_msg = "&email_sent=1";
                    }
                    
                    // Redirect to success page
                    header("Location: add_participant.php?success=1" . $email_msg);
                    exit();
                } else {
                    $error = 'Error adding participant: ' . $conn->error;
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    // If there was an error, redirect back with error message
    if (!empty($error)) {
        header("Location: add_participant.php?error=" . urlencode($error));
        exit();
    }
} else {
    // If not a POST request, redirect to add participant page
    header("Location: add_participant.php");
    exit();
}
?>
