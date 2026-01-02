<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

// Include database connection
require_once 'dbconnect.php';

// Get participant ID from URL
$participant_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// If no valid ID provided, redirect back
if (!$participant_id) {
    $_SESSION['error'] = "Invalid participant ID.";
    header("Location: view_participants_edit_delete.php");
    exit();
}

// Check if form was submitted with confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Prepare and execute delete query
        $stmt = $conn->prepare("DELETE FROM participant WHERE id = ?");
        $stmt->bind_param("i", $participant_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Participant deleted successfully!";
            header("Location: view_participants_edit_delete.php");
            exit();
        } else {
            throw new Exception("Failed to delete participant.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting participant: " . $e->getMessage();
        header("Location: view_participants_edit_delete.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Deletion - E-Sports Arena</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #00f3ff;
            --danger: #ff3860;
            --dark: #0a0a1a;
            --light: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--dark);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            line-height: 1.6;
            text-align: center;
        }
        
        .confirmation-box {
            background: rgba(20, 20, 40, 0.9);
            border-radius: 10px;
            padding: 2.5rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 56, 96, 0.2);
        }
        
        h1 {
            font-family: 'Orbitron', sans-serif;
            color: var(--danger);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        p {
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            margin: 0 0.5rem;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #e03150;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 56, 96, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .icon {
            font-size: 3rem;
            color: var(--danger);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <div class="icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Confirm Deletion</h1>
        <p>Are you sure you want to delete this participant? This action cannot be undone.</p>
        <div class="actions">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Yes, Delete
                </button>
            </form>
            <a href="view_participants_edit_delete.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </div>
</body>
</html>