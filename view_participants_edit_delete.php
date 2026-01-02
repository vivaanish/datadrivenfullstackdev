<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

// Include database connection
include 'dbconnect.php';

// Fetch participants from database
$query = "SELECT id, firstname, surname, email, kills, deaths FROM participant ORDER BY id DESC";
$result = $conn->query($query);

// Calculate total kills
$total_kills = 0;
$total_deaths = 0;
$total_participants = 0;

if ($result && $result->num_rows > 0) {
    $total_participants = $result->num_rows;
    // Reset pointer and calculate totals
    $result->data_seek(0);
    while($row = $result->fetch_assoc()) {
        $total_kills += (int)$row['kills'];
        $total_deaths += (int)$row['deaths'];
    }
    // Reset pointer again for main display
    $result->data_seek(0);
}

// Calculate average K/D ratio
$avg_kd = $total_deaths > 0 ? $total_kills / $total_deaths : $total_kills;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Participants - UK E-Sports League</title>
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1E293B;
            margin: 0.5rem 0;
            font-family: 'Space Grotesk', sans-serif;
        }

        .stat-label {
            color: #64748B;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 1rem;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #F8FAFC;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #E2E8F0;
        }

        .table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .player-info {
            display: flex;
            flex-direction: column;
        }

        .player-name {
            font-weight: 600;
            color: #1E293B;
        }

        .player-email {
            font-size: 0.875rem;
            color: #64748B;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success { background: #ECFDF5; color: #059669; }
        .badge-danger { background: #FEF2F2; color: #DC2626; }
        .badge-warning { background: #FFFBEB; color: #D97706; }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
        
        .btn-edit { background: #E0F2FE; color: #0284C7; }
        .btn-edit:hover { background: #BAE6FD; }
        
        .btn-delete { background: #FEE2E2; color: #DC2626; }
        .btn-delete:hover { background: #FECACA; }

        .alert {
            background: #ECFDF5;
            border: 1px solid #10B981;
            color: #065F46;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
                    <li><a href="view_participants_edit_delete.php" class="nav-link active"><i class="fas fa-users"></i> Participants</a></li>
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
                    <h1>Manage Participants</h1>
                    <p>View, edit, and manage all registered players</p>
                </div>
                <a href="add_participant.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Participant
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Success!</strong>
                        <?php
                        $messages = [
                            'added' => 'Participant added successfully!',
                            'updated' => 'Participant updated successfully!',
                            'deleted' => 'Participant deleted successfully!'
                        ];
                        echo htmlspecialchars($messages[$_GET['success']] ?? 'Operation completed successfully!');
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #EFF6FF; color: #3B82F6;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_participants; ?></div>
                    <div class="stat-label">Total Players</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ECFDF5; color: #10B981;">
                        <i class="fas fa-crosshairs"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_kills); ?></div>
                    <div class="stat-label">Total Kills</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FEF2F2; color: #EF4444;">
                        <i class="fas fa-skull"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_deaths); ?></div>
                    <div class="stat-label">Total Deaths</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFFBEB; color: #F59E0B;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($avg_kd, 2); ?></div>
                    <div class="stat-label">Avg K/D Ratio</div>
                </div>
            </div>

            <!-- Participants Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Player Info</th>
                            <th>Contact</th>
                            <th>Stats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <div class="player-info">
                                            <span class="player-name"><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['surname']); ?></span>
                                            <span class="player-email"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <span class="badge badge-success"><?php echo (int)$row['kills']; ?> K</span>
                                            <span class="badge badge-danger"><?php echo (int)$row['deaths']; ?> D</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="edit_participant_form.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-delete"
                                               onclick="return confirm('Delete this participant?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-user-slash" style="font-size: 2rem; color: #CBD5E1; margin-bottom: 1rem;"></i>
                                    <p style="color: #64748B;">No participants found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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