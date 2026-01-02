<?php
// Start the session
session_start();

// Include database connection
require_once 'dbconnect.php';

// Function to show alert and redirect
function showAlertAndRedirect($message, $isError = false) {
    if ($isError) {
        // For errors, return JSON response that the AJAX call will handle
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    } else {
        // For success, redirect immediately
        header('Location: admin_menu.php');
        exit();
    }
}

// Check if the form is submitted via AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get username and password from form
        $input_username = trim($_POST['username'] ?? '');
        $input_password = $_POST['password'] ?? '';

        // Validate input
        if (empty($input_username) || empty($input_password)) {
            throw new Exception("Please enter both username and password.");
        }

        // Prepare and execute query to check admin credentials
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? LIMIT 1");
        if ($stmt === false) {
            throw new Exception("Database error. Please try again later.");
        }
        
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows !== 1) {
            throw new Exception("Invalid username or password.");
        }
        
        $user = $result->fetch_assoc();
        
        // Check plain text password (since passwords are stored in plain text)
        if ($input_password === $user['password']) {
            // Set session variables for admin
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            if ($isAjax) {
                // For AJAX requests, return JSON response
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => 'admin_menu.php']);
                exit();
            } else {
                // For non-AJAX requests (fallback)
                header('Location: admin_menu.php');
                exit();
            }
        } else {
            throw new Exception("Invalid username or password.");
        }

    } catch (Exception $e) {
        if ($isAjax) {
            // For AJAX requests, return JSON error
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        } else {
            // For non-AJAX requests (fallback)
            // For non-AJAX requests (fallback)
            $errorMessage = urlencode($e->getMessage());
            header("Location: admin_login.html?error=$errorMessage");
            exit();
        }
    }
} else {
    // If someone tries to access this file directly, redirect to login
    header("Location: admin_login.html");
    exit();
}
?>