<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

// Include database connection
include 'dbconnect.php';

// Initialize variables
$error = '';
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $kills = isset($_POST['kills']) ? intval($_POST['kills']) : 0;
    $deaths = isset($_POST['deaths']) ? intval($_POST['deaths']) : 0;

    // Validate inputs
    if ($id <= 0) {
        $error = 'invalid';
    } elseif (empty($firstname) || empty($surname) || empty($email)) {
        $error = 'empty_fields'; 
    } elseif ($kills < 0 || $deaths < 0) {
        $error = 'invalid';
    } else {
        try {
            // Check if participant exists
            $check_stmt = $conn->prepare("SELECT id FROM participant WHERE id = ?");
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                $error = 'notfound';
            } else {
                // Update participant details and stats
                $update_stmt = $conn->prepare("UPDATE participant SET firstname = ?, surname = ?, email = ?, kills = ?, deaths = ? WHERE id = ?");
                $update_stmt->bind_param("sssiii", $firstname, $surname, $email, $kills, $deaths, $id);
                
                if ($update_stmt->execute()) {
                    $success = true;
                    
                    // Log the update action
                    error_log("Participant ID $id updated - Name: $firstname $surname, Email: $email, Kills: $kills, Deaths: $deaths by Admin ID: " . $_SESSION['admin_id']);
                    
                } else {
                    $error = 'db';
                    error_log("Database update error: " . $update_stmt->error);
                }
                
                $update_stmt->close();
            }
            
            $check_stmt->close();
            
        } catch (Exception $e) {
            $error = 'db';
            error_log("Update error: " . $e->getMessage());
        }
    }
} else {
    // Not a POST request, redirect to form
    header("Location: edit_participant_form.php");
    exit();
}

// Close database connection
$conn->close();

// Redirect based on result
if ($success) {
    // Redirect to view participants with success message
    header("Location: view_participants_edit_delete.php?success=updated&id=" . $id);
} else {
    // Redirect back to form with error
    header("Location: edit_participant_form.php?id=" . $id . "&error=" . $error);
}
exit();
?>