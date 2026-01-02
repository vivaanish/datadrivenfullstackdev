<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Participant - UK E-Sports League Admin</title>
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

        /* Form Container */
        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #E2E8F0;
            overflow: hidden;
        }

        .form-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #E2E8F0;
            background: #F8FAFC;
        }

        .form-header h2 {
            font-size: 1.25rem;
            color: #1E293B;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #CBD5E1;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E2E8F0;
            grid-column: 1 / -1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #E2E8F0;
            color: #1E293B;
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

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
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
                    <li><a href="search_admin.php" class="nav-link"><i class="fas fa-search"></i> Search</a></li>
                    <li><a href="add_participant.php" class="nav-link active"><i class="fas fa-user-plus"></i> Add New</a></li>
                    <li><a href="admin_login.html" class="nav-link" style="color: #EF4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Add New Participant</h1>
                    <p style="color: #64748B; margin-top: 0.5rem;">Register a new player to the tournament database</p>
                </div>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <h2><i class="fas fa-user-plus" style="color: #3B82F6;"></i> Player Details</h2>
                </div>
                
                <div class="form-body">
                    <?php
                    if (isset($_GET['success'])) {
                        echo '<div class="alert alert-success">
                                <div>
                                    <i class="fas fa-check-circle"></i> Participant added successfully!
                                    ' . (isset($_GET['email_sent']) ? '<br><small><i class="fas fa-envelope"></i> Invitation email has been sent.</small>' : '') . '
                                </div>
                              </div>';
                    } elseif (isset($_GET['error'])) {
                        echo '<div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                ' . htmlspecialchars($_GET['error']) . '
                              </div>';
                    }
                    ?>
                    
                    <form action="process_participant.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">First Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter surname" required>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="player@example.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="kills" class="form-label">Initial Kills</label>
                                <input type="number" id="kills" name="kills" class="form-control" value="0" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="deaths" class="form-label">Initial Deaths</label>
                                <input type="number" id="deaths" name="deaths" class="form-control" value="0" min="0">
                            </div>
                            <div class="form-group full-width">
                                <label class="flex items-center gap-2" style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" name="send_invite" value="1" checked style="width: auto; margin-right: 0.5rem;">
                                    <span style="font-weight: 500; color: #475569;">Send Welcome Email & Invitation</span>
                                </label>
                                <p style="font-size: 0.85rem; color: #64748B; margin-top: 0.25rem; margin-left: 1.5rem;">
                                    <i class="fas fa-info-circle"></i> Digital invitation will be sent to the provided email address.
                                </p>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="view_participants_edit_delete.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save" style="margin-right: 0.5rem;"></i> Save Participant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Complete Footer -->
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
