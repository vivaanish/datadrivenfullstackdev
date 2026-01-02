<?php
// Start the session
session_start();

// Include database connection
include 'dbconnect.php';

// Check if database connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check if a table exists in the database
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}

// Check if required tables exist
if (!tableExists($conn, 'participant') || !tableExists($conn, 'team')) {
    die("<div style='padding: 20px; color: white; background: #ff4444; border-radius: 5px; margin: 20px;'>
            <h3>Database Setup Required</h3>
            <p>Required database tables are missing or have different names.</p>
            <p>Found tables: participant, team</p>
          </div>");
}

// Utility functions
function calculateKDRatio($kills, $deaths) {
    $kills = floatval($kills ?? 0);
    $deaths = floatval($deaths ?? 0);
    
    if ($deaths <= 0) {
        return $kills > 0 ? $kills : 0;
    }
    
    return $kills / $deaths;
}

function getKDClass($kd_ratio) {
    if ($kd_ratio >= 2.0) return 'kd-great';
    if ($kd_ratio >= 1.5) return 'kd-good';
    if ($kd_ratio >= 1.0) return 'kd-ok';
    return 'kd-bad';
}

function formatKD($kd_ratio) {
    return number_format($kd_ratio, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - E-Sports Arena</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --neon-blue: #00f3ff;
            --neon-pink: #ff00ff;
            --neon-purple: #bc13fe;
            --dark-bg: #0a0a1a;
            --darker-bg: #050510;
            --primary: #00f3ff;
            --secondary: #6c63ff;
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--dark-bg);
            color: #fff;
            min-height: 100vh;
            line-height: 1.6;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(11, 2, 35, 0.8) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(60, 10, 80, 0.6) 0%, transparent 20%);
            position: relative;
            overflow-x: hidden;
        }

        /* Navbar Styles */
        .navbar {
            background-color: rgba(10, 10, 30, 0.9);
            padding: 15px 0;
            border-bottom: 2px solid #4a1e8a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Orbitron', sans-serif;
        }

        .logo:hover {
            color: #9d4edd;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: #e0e0e0;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Roboto', sans-serif;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #fff;
            background: rgba(157, 78, 221, 0.2);
        }

        .nav-links .btn-primary {
            background: linear-gradient(90deg, #9d4edd, #5a189a);
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(157, 78, 221, 0.3);
        }

        .nav-links .btn-primary:hover {
            background: linear-gradient(90deg, #7b2cbf, #3c096c);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(157, 78, 221, 0.4);
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(90deg, var(--dark-bg) 1px, transparent 1px 10px) 0 0 / 10px 10px,
                linear-gradient(var(--dark-bg) 1px, transparent 1px 10px) 0 0 / 10px 10px;
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--neon-blue);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 2rem;
        }

        .back-link::before {
            content: '←';
            position: absolute;
            left: 0;
            transition: transform 0.3s ease;
        }

        .back-link:hover {
            color: #fff;
            text-shadow: 0 0 10px var(--neon-blue), 
                        0 0 20px var(--neon-blue),
                        0 0 30px var(--neon-blue);
        }

        .back-link:hover::before {
            transform: translateX(-5px);
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: #fff;
            text-shadow: 0 0 10px var(--neon-blue), 
                        0 0 20px var(--neon-blue);
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, 
                transparent, 
                var(--neon-blue), 
                var(--neon-purple), 
                var(--neon-pink), 
                transparent);
            border-radius: 3px;
        }

        .results {
            margin-top: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            margin: 2rem 0 1rem 0;
            font-size: 1.5rem;
        }

        /* Table Styles */
        .table-container {
            margin-bottom: 3rem;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 2rem 0;
            background: rgba(10, 10, 25, 0.7);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(188, 19, 254, 0.2);
            position: relative;
        }

        table::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                var(--neon-blue), 
                var(--neon-purple), 
                var(--neon-pink));
            z-index: -1;
            border-radius: 12px;
            opacity: 0.5;
        }

        th {
            background: linear-gradient(135deg, 
                rgba(0, 243, 255, 0.1), 
                rgba(188, 19, 254, 0.1));
            color: var(--neon-blue);
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 1.5rem;
            text-align: left;
            border: none;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.03);
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            transform: translateX(5px);
        }

        /* Team Card Styles */
        .team-card {
            background: rgba(20, 20, 40, 0.6);
            border-radius: var(--border-radius);
            border: 1px solid rgba(108, 99, 255, 0.1);
            margin-bottom: 3rem;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .team-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(108, 99, 255, 0.3);
        }

        .team-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(15, 12, 41, 0.8), rgba(48, 43, 99, 0.6));
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .team-name {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.3);
            letter-spacing: 0.5px;
        }

        .team-location {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1rem;
        }

        .team-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .stat {
            text-align: center;
            min-width: 80px;
        }

        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Team Roster Styles */
        .team-roster {
            padding: 1.5rem;
        }

        .roster-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .roster-header h3 {
            margin: 0;
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .roster-count {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .roster-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(10, 10, 26, 0.5);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .roster-table th {
            background: rgba(108, 99, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            text-align: left;
        }

        .roster-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .roster-table tr:last-child td {
            border-bottom: none;
        }

        .player-info {
            display: flex;
            flex-direction: column;
        }

        .player-name {
            font-weight: 500;
            color: white;
            line-height: 1.2;
        }

        .player-email {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 0.1rem;
        }

        /* K/D Ratio Styles */
        .kd-ratio {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            font-size: 0.9rem;
            min-width: 50px;
            text-align: center;
        }
        
        .kd-great {
            background: rgba(0, 200, 83, 0.15);
            color: #00e676;
            border: 1px solid #00e676;
            text-shadow: 0 0 5px rgba(0, 230, 118, 0.5);
        }
        
        .kd-good {
            background: rgba(0, 201, 167, 0.15);
            color: #00c9a7;
            border: 1px solid #00c9a7;
            text-shadow: 0 0 5px rgba(0, 201, 167, 0.5);
        }
        
        .kd-ok {
            background: rgba(255, 171, 0, 0.15);
            color: #ffab00;
            border: 1px solid #ffab00;
            text-shadow: 0 0 5px rgba(255, 171, 0, 0.5);
        }
        
        .kd-bad {
            background: rgba(255, 82, 82, 0.15);
            color: #ff5252;
            border: 1px solid #ff5252;
            text-shadow: 0 0 5px rgba(255, 82, 82, 0.5);
        }

        /* No Results Styles */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #ff6b6b;
            font-size: 1.2rem;
            background: rgba(255, 0, 0, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 0, 0, 0.2);
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.1);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 243, 255, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(0, 243, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(0, 243, 255, 0);
            }
        }

        /* Neon text effect */
        .neon-text {
            color: #fff;
            text-shadow: 0 0 5px #fff,
                        0 0 10px #fff,
                        0 0 20px var(--neon-blue),
                        0 0 30px var(--neon-blue),
                        0 0 40px var(--neon-purple),
                        0 0 55px var(--neon-purple),
                        0 0 75px var(--neon-pink);
            animation: flicker 1.5s infinite alternate;
        }

        @keyframes flicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                text-shadow: 0 0 5px #fff,
                            0 0 10px #fff,
                            0 0 20px var(--neon-blue),
                            0 0 30px var(--neon-blue),
                            0 0 40px var(--neon-purple),
                            0 0 55px var(--neon-purple),
                            0 0 75px var(--neon-pink);
            }
            20%, 24%, 55% {        
                text-shadow: none;
            }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #fff;
            border-radius: 50%;
            opacity: 0;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .team-stats {
                gap: 1rem;
            }
            
            .stat {
                min-width: 60px;
            }
            
            .roster-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">
                <i class="fas fa-gamepad"></i>
                <span>UK E-Sports League</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="search_form.php" class="active">Search</a></li>
                <li><a href="register_form.html" class="btn-primary">Register</a></li>
                <li><a href="admin_login.html">Admin</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="neon-text">Search Results</h1>
        
        <div class="results">
            <?php
            try {
                // Enable error reporting for debugging
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                
                // Check if it's a participant search
                if (isset($_POST['search_query'])) {
                    $search_type = $_POST['search_type'] ?? 'name';
                    $search_term = $conn->real_escape_string($_POST['search_query']);
                    
                    if ($search_type === 'name') {
                        $sql = "SELECT p.*, t.name as team_name 
                                FROM participant p 
                                LEFT JOIN team t ON p.team_id = t.id 
                                WHERE p.firstname LIKE '%$search_term%' 
                                OR p.surname LIKE '%$search_term%'";
                    } else {
                        $sql = "SELECT p.*, t.name as team_name 
                                FROM participant p 
                                LEFT JOIN team t ON p.team_id = t.id 
                                WHERE p.email LIKE '%$search_term%'";
                    }
                    
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        echo '<div class="table-container">';
                        echo '<h2 class="section-title">🎮 Player Results</h2>';
                        echo '<table class="pulse">';
                        echo '<thead><tr>
                                <th>ID</th>
                                <th>Player</th>
                                <th>Email</th>
                                <th>Team</th>
                                <th>K/D Ratio</th>
                                <th>Kills</th>
                                <th>Deaths</th>
                              </tr></thead><tbody>';
                        
                        while($row = $result->fetch_assoc()) {
                            $kd_ratio = calculateKDRatio($row['kills'] ?? 0, $row['deaths'] ?? 0);
                            $kd_class = getKDClass($kd_ratio);
                            $formatted_kd = formatKD($kd_ratio);
                            
                            echo '<tr>';
                            echo '<td>#' . htmlspecialchars($row['id']) . '</td>';
                            echo '<td>
                                    <div class="player-info">
                                        <span class="player-name">' . htmlspecialchars($row['firstname'] . ' ' . $row['surname']) . '</span>
                                        <span class="player-email">' . htmlspecialchars($row['email']) . '</span>
                                    </div>
                                  </td>';
                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['team_name'] ?? 'Free Agent') . '</td>';
                            echo '<td><span class="kd-ratio ' . $kd_class . '">' . $formatted_kd . '</span></td>';
                            echo '<td>' . htmlspecialchars($row['kills'] ?? 0) . '</td>';
                            echo '<td>' . htmlspecialchars($row['deaths'] ?? 0) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="no-results pulse">
                                <p>🔍 No players found matching your search</p>
                                <p>Try a different search term or check the spelling</p>
                              </div>';
                    }
                } 
                // Check if it's a team search
                elseif (isset($_POST['team'])) {
                    $team_name = $conn->real_escape_string($_POST['team']);
                    
                    // Get team details with statistics
                    $sql = "SELECT t.*, 
                           COUNT(p.id) as member_count,
                           AVG(IFNULL(p.kills, 0)) as avg_kills,
                           AVG(IFNULL(p.deaths, 1)) as avg_deaths,
                           MAX(IFNULL(p.kills, 0)) as top_kills,
                           SUM(IFNULL(p.kills, 0)) as total_kills,
                           CASE 
                               WHEN AVG(IFNULL(p.deaths, 0)) = 0 THEN AVG(IFNULL(p.kills, 1))
                               ELSE AVG(IFNULL(p.kills, 0)) / NULLIF(AVG(IFNULL(p.deaths, 1)), 0)
                           END as team_kd_ratio
                    FROM team t
                    LEFT JOIN participant p ON t.id = p.team_id
                    WHERE t.name LIKE '%$team_name%'
                    GROUP BY t.id, t.name, t.location
                    ORDER BY team_kd_ratio DESC";
                    
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($team = $result->fetch_assoc()) {
                            $team_kd = calculateKDRatio($team['avg_kills'], $team['avg_deaths']);
                            $kd_class = getKDClass($team_kd);
                            $formatted_kd = formatKD($team_kd);
                            
                            echo '<div class="team-card">';
                            
                            // Team header
                            echo '<div class="team-header">';
                            echo '<h2 class="team-name">' . htmlspecialchars($team['name']) . '</h2>';
                            echo '<p class="team-location"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($team['location'] ?? 'Unknown') . '</p>';
                            
                            // Team statistics
                            echo '<div class="team-stats">';
                            echo '<div class="stat">';
                            echo '<span class="stat-value">' . $team['member_count'] . '</span>';
                            echo '<span class="stat-label">Members</span>';
                            echo '</div>';
                            
                            echo '<div class="stat">';
                            echo '<span class="stat-value">' . $formatted_kd . '</span>';
                            echo '<span class="stat-label">Avg K/D</span>';
                            echo '</div>';
                            
                            echo '<div class="stat">';
                            echo '<span class="stat-value">' . number_format($team['avg_kills'], 1) . '</span>';
                            echo '<span class="stat-label">Avg Kills</span>';
                            echo '</div>';
                            
                            echo '<div class="stat">';
                            echo '<span class="stat-value">' . number_format($team['avg_deaths'], 1) . '</span>';
                            echo '<span class="stat-label">Avg Deaths</span>';
                            echo '</div>';
                            
                            echo '<div class="stat">';
                            echo '<span class="stat-value">' . $team['top_kills'] . '</span>';
                            echo '<span class="stat-label">Top Kills</span>';
                            echo '</div>';
                            echo '</div>'; // .team-stats
                            echo '</div>'; // .team-header
                            
                            // Get team members
                            $team_id = $team['id'];
                            $members_sql = "SELECT p.*, 
                                          CASE 
                                              WHEN p.deaths = 0 OR p.deaths IS NULL THEN p.kills
                                              ELSE p.kills / NULLIF(p.deaths, 0)
                                          END as kd_ratio
                                          FROM participant p 
                                          WHERE p.team_id = $team_id 
                                          ORDER BY kd_ratio DESC";
                            
                            $members_result = $conn->query($members_sql);
                            
                            if ($members_result && $members_result->num_rows > 0) {
                                echo '<div class="team-roster">';
                                echo '<div class="roster-header">';
                                echo '<h3><i class="fas fa-users"></i> Team Roster</h3>';
                                echo '<div class="roster-count">' . $team['member_count'] . ' members</div>';
                                echo '</div>';
                                
                                echo '<table class="roster-table">';
                                echo '<thead>';
                                echo '<tr>';
                                echo '<th>Player</th>';
                                echo '<th>K/D Ratio</th>';
                                echo '<th>Kills</th>';
                                echo '<th>Deaths</th>';
                                echo '</tr>';
                                echo '</thead>';
                                echo '<tbody>';
                                
                                while($member = $members_result->fetch_assoc()) {
                                    $member_kd = calculateKDRatio($member['kills'], $member['deaths']);
                                    $member_kd_class = getKDClass($member_kd);
                                    $formatted_member_kd = formatKD($member_kd);
                                    
                                    echo '<tr>';
                                    echo '<td>';
                                    echo '<div class="player-info">';
                                    echo '<span class="player-name">' . htmlspecialchars($member['firstname'] . ' ' . $member['surname']) . '</span>';
                                    echo '<span class="player-email">' . htmlspecialchars($member['email']) . '</span>';
                                    echo '</div>';
                                    echo '</td>';
                                    echo '<td><span class="kd-ratio ' . $member_kd_class . '">' . $formatted_member_kd . '</span></td>';
                                    echo '<td>' . htmlspecialchars($member['kills'] ?? 0) . '</td>';
                                    echo '<td>' . htmlspecialchars($member['deaths'] ?? 0) . '</td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>'; // .team-roster
                            } else {
                                echo '<div class="team-roster">';
                                echo '<div class="no-results">';
                                echo '<p>No team members found for this team.</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                            
                            echo '</div>'; // .team-card
                        }
                    } else {
                        echo '<div class="no-results pulse">';
                        echo '<p>🏆 No teams found matching your search</p>';
                        echo '<p>Try a different team name or check the spelling</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-results">';
                    echo '<p>⚠️ No search parameters provided</p>';
                    echo '<p>Please return to the dashboard and perform a search</p>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                // Log the error for debugging
                error_log('Search Error: ' . $e->getMessage());
                
                echo '<div class="no-results">';
                echo '<p>⚠️ An error occurred while processing your request.</p>';
                echo '<p>Please try again later or contact support if the problem persists.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random position
                const posX = Math.random() * 100;
                const delay = Math.random() * 15;
                const size = Math.random() * 3 + 1;
                const duration = Math.random() * 10 + 10;
                
                // Random color
                const colors = ['#00f3ff', '#ff00ff', '#bc13fe', '#ffffff'];
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                // Apply styles
                particle.style.left = `${posX}%`;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.background = color;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Initialize particles when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Add hover effect to table rows
            const rows = document.querySelectorAll('tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                    this.style.boxShadow = '0 0 15px rgba(0, 243, 255, 0.3)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                });
            });
        });

        // Add parallax effect to background
        document.addEventListener('mousemove', function(e) {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            document.body.style.backgroundPosition = 
                `${x * 30}px ${y * 30}px, ${x * -20}px ${y * -20}px`;
        });
    </script>
</body>
</html>
<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>