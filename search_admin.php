<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'dbconnect.php';

// Check if connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Participants - E-Sports Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --secondary: #1E293B;
            --light: #F8FAFC;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #F1F5F9;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-layout {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #FFFFFF;
            border-right: 1px solid #E2E8F0;
            display: flex;
            flex-direction: column;
        }

        .logo {
            padding: 1.5rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .logo h2 {
            font-family: 'Space Grotesk', sans-serif;
            color: #1E293B;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .nav-menu {
            list-style: none;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #64748B;
            text-decoration: none;
            border-radius: 0.75rem;
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: #EFF6FF;
            color: var(--primary);
        }

        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-family: 'Space Grotesk', sans-serif;
            color: #1E293B;
            font-size: 1.875rem;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #EFF6FF;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Search Container */
        .search-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #CBD5E1;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            width: 100%;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .search-tip {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            border-radius: 0.75rem;
            color: #065F46;
        }

        .search-tip strong {
            display: block;
            margin-bottom: 0.5rem;
            color: #047857;
        }

        .search-tip ul {
            margin-left: 1.5rem;
            margin-top: 0.5rem;
        }

        .search-tip li {
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        /* Footer */
        footer {
            background: #1E293B;
            color: white;
            padding: 4rem 0 2rem;
            margin-top: auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            color: white;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-family: 'Space Grotesk', sans-serif;
        }

        .footer-section p {
            color: #94A3B8;
            margin-bottom: 0.8rem;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .footer-section strong {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            color: white;
            transition: all 0.3s;
            text-decoration: none;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: #94A3B8;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2><i class="fas fa-gamepad"></i> ES Admin</h2>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin_menu.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="view_participants_edit_delete.php" class="nav-link"><i class="fas fa-users"></i> Participants</a></li>
                    <li><a href="search_admin.php" class="nav-link active"><i class="fas fa-search"></i> Search</a></li>
                    <li><a href="add_participant.php" class="nav-link"><i class="fas fa-user-plus"></i> Add New</a></li>
                    <li><a href="admin_login.html" class="nav-link" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Search Database</h1>
                    <p style="color: #64748B; margin-top: 0.5rem;">Find participants, teams, and tournament records</p>
                </div>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                    </div>
                </div>
            </div>

            <div class="search-card">
                <form action="search_admin_result.php" method="GET">
                    <input type="hidden" name="search_type" value="all">
                    
                    <div class="form-group">
                        <label for="search_term" class="form-label">Enter Username, Name, or Email</label>
                        <input type="text" id="search_term" name="search_term" class="form-control" 
                               placeholder="Type to search..." required autofocus
                               style="font-size: 1.1rem; padding: 1rem;">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search" style="margin-right: 0.5rem;"></i> Search Database
                    </button>
                </form>

                <div class="search-tip">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <i class="fas fa-lightbulb" style="font-size: 1.25rem; margin-top: 0.1rem;"></i>
                        <div>
                            <strong>Search Tip</strong>
                            The comprehensive search will look through all participant records including:
                            <ul>
                                <li>First Name and Surname</li>
                                <li>Email Address</li>
                                <li>Gamertags / Usernames</li>
                                <li>Team Names</li>
                                <li>Contact Information</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>Student Information</h3>
                    <p><strong>Name:</strong> Anish Pangeni</p>
                    <p><strong>Email:</strong> bwit24d.anh@ismt.edu.np</p>
                    <p><strong>Student ID:</strong> bi95sn</p>
                    <div class="social-links" style="margin-top: 1rem;">
                        <a href="https://github.com/vivaanish" target="_blank" title="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="https://www.linkedin.com/in/anishisgreat/" target="_blank" title="LinkedIn">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="https://x.com/anishisgreatt" target="_blank" title="Twitter/X">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.instagram.com/anishisgreat/" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>College Details</h3>
                    <p><strong>Institution:</strong> ISMT Butwal, Nepal</p>
                    <p><strong>Affiliated University:</strong> <a href="https://www.sunderland.ac.uk/" target="_blank" style="color: #3B82F6; text-decoration: none; font-weight: 600;">University of Sunderland, UK</a></p>
                    <p><strong>Program:</strong> BSc (Hons) Computing</p>
                    <div class="social-links" style="margin-top: 1rem;">
                        <a href="https://www.sunderland.ac.uk/study/undergraduate/" target="_blank" title="Undergraduate Programs">
                            <i class="fas fa-graduation-cap"></i>
                        </a>
                        <a href="https://en.wikipedia.org/wiki/University_of_Sunderland" target="_blank" title="Wikipedia">
                            <i class="fab fa-wikipedia-w"></i>
                        </a>
                        <a href="https://www.facebook.com/sunderlanduniversity/" target="_blank" title="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/sunderlanduni/" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="index.html">Home</a>
                    <a href="search_form.php">Search Players</a>
                    <a href="register_form.html" target="_blank">Merchandise</a>
                    <a href="admin_menu.php">Dashboard</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="copyright">&copy; 2026 UK E-Sports League. Created by Anish Pangeni.</p>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.875rem; margin-top: 0.5rem;">ISMT Butwal | University of Sunderland, UK</p>
            </div>
        </div>
    </footer>
</body>
</html>