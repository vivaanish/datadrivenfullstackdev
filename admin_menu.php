<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'dbconnect.php';

// Get counts from database
try {
    // Get total participants
    $result = $conn->query("SELECT COUNT(*) as total FROM participant");
    $participant_count = $result->fetch_assoc()['total'];
    
    // Get total teams
    $result = $conn->query("SELECT COUNT(*) as total FROM team");
    $team_count = $result->fetch_assoc()['total'];
    
} catch (Exception $e) {
    $error = "Error fetching data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UK E-Sports League</title>
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
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #64748B;
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
            font-weight: bold;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1E293B;
            margin: 0.5rem 0;
            font-family: 'Space Grotesk', sans-serif;
        }

        .stat-label {
            color: #64748B;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        /* Quick Actions */
        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            color: #1E293B;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            border: 1px solid #E2E8F0;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .action-icon {
            width: 64px;
            height: 64px;
            background: #EFF6FF;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .action-card:hover .action-icon {
            background: var(--primary);
            color: white;
        }

        .action-title {
            color: #1E293B;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .action-desc {
            color: #64748B;
            font-size: 0.9rem;
            line-height: 1.5;
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
                    <li><a href="admin_menu.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="view_participants_edit_delete.php" class="nav-link"><i class="fas fa-users"></i> Participants</a></li>
                    <li><a href="search_admin.php" class="nav-link"><i class="fas fa-search"></i> Search</a></li>
                    <li><a href="add_participant.php" class="nav-link"><i class="fas fa-user-plus"></i> Add New</a></li>
                    <li><a href="admin_login.html" class="nav-link" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Dashboard Overview</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>! Here's what's happening.</p>
                </div>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #EFF6FF; color: #3B82F6;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $participant_count; ?></div>
                    <div class="stat-label">Total Participants</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ECFDF5; color: #10B981;">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="stat-value"><?php echo $team_count; ?></div>
                    <div class="stat-label">Total Teams</div>
                </div>
                <!-- Additional Placeholder Stats -->
                <div class="stat-card">
                   <div class="stat-icon" style="background: #FFFBEB; color: #F59E0B;">
                       <i class="fas fa-trophy"></i>
                   </div>
                   <div class="stat-value">4</div>
                   <div class="stat-label">Active Tournaments</div>
               </div>
               <div class="stat-card">
                   <div class="stat-icon" style="background: #FEF2F2; color: #EF4444;">
                       <i class="fas fa-bolt"></i>
                   </div>
                   <div class="stat-value">Live</div>
                   <div class="stat-label">System Status</div>
               </div>
            </div>

            <!-- Quick Actions -->
            <h2 class="section-title">Quick Actions</h2>
            <div class="action-grid">
                <a href="view_participants_edit_delete.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="action-title">Manage Participants</h3>
                    <p class="action-desc">View, edit, or remove players from the tournament.</p>
                </a>

                <a href="add_participant.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 class="action-title">Add New Player</h3>
                    <p class="action-desc">Register a new participant for an upcoming match.</p>
                </a>

                <a href="search_admin.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="action-title">Search Database</h3>
                    <p class="action-desc">Find players by name, email, or ID quickly.</p>
                </a>
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
                    <a href="register_form.html">Merchandise</a>
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