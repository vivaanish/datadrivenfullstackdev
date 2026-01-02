<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Include database connection
    require_once 'dbconnect.php';
    
    // Check if connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Log the request for debugging
    error_log("Search request: " . print_r($_GET, true));

    // Initialize variables
    $results = [];
    $search_type = $_GET['search_type'] ?? '';
    $search_term = '';
    $error = '';
    $team_name = '';
    $search_performed = false;

    // First, let's check the actual structure of the participant table
    $table_check = $conn->query("DESCRIBE participant");
    $table_structure = $table_check->fetch_all(MYSQLI_ASSOC);
    $column_names = array_column($table_structure, 'Field');
    error_log("Participant table columns: " . print_r($column_names, true));

    // Process search based on search type
    if (!empty($search_type)) {
        try {
            switch ($search_type) {
                case 'email':
                    if (!empty($_GET['email'])) {
                        $search_term = trim($_GET['email']);
                        // Use the correct column name for email
                        $stmt = $conn->prepare("
                            SELECT p.*, t.name as team_name 
                            FROM participant p 
                            LEFT JOIN team t ON p.team_id = t.id 
                            WHERE p.email = ?
                            ORDER BY p.surname, p.firstname
                        ");
                        $stmt->bind_param("s", $search_term);
                    }
                    break;
                    
                case 'gamertag':
                    if (!empty($_GET['gamertag'])) {
                        $search_term = '%' . trim($_GET['gamertag']) . '%';
                        // Check if gamertag column exists, if not use username or similar
                        if (in_array('gamertag', $column_names)) {
                            $stmt = $conn->prepare("
                                SELECT p.*, t.name as team_name 
                                FROM participant p 
                                LEFT JOIN team t ON p.team_id = t.id 
                                WHERE p.gamertag LIKE ?
                                ORDER BY p.surname, p.firstname
                            ");
                        } elseif (in_array('username', $column_names)) {
                            // Fallback to username if gamertag doesn't exist
                            $stmt = $conn->prepare("
                                SELECT p.*, t.name as team_name 
                                FROM participant p 
                                LEFT JOIN team t ON p.team_id = t.id 
                                WHERE p.username LIKE ?
                                ORDER BY p.surname, p.firstname
                            ");
                        } else {
                            $error = "Gamertag search is not available in the current database structure.";
                            break;
                        }
                        $stmt->bind_param("s", $search_term);
                    }
                    break;
                    
                case 'team':
                    if (!empty($_GET['team_id'])) {
                        $team_id = (int)$_GET['team_id'];
                        // First get team name
                        $team_stmt = $conn->prepare("SELECT name FROM team WHERE id = ?");
                        $team_stmt->bind_param("i", $team_id);
                        $team_stmt->execute();
                        $team_result = $team_stmt->get_result();
                        $team = $team_result->fetch_assoc();
                        $team_name = $team ? $team['name'] : 'Unknown Team';
                        
                        // Then get team members
                        $stmt = $conn->prepare("
                            SELECT p.*, t.name as team_name 
                            FROM participant p 
                            LEFT JOIN team t ON p.team_id = t.id 
                            WHERE p.team_id = ?
                            ORDER BY p.surname, p.firstname
                        ");
                        $stmt->bind_param("i", $team_id);
                    }
                    break;
                    
                case 'all':
                    if (!empty($_GET['search_term'])) {
                        $search_term = '%' . trim($_GET['search_term']) . '%';
                        
                        // Build query based on actual column names
                        $where_conditions = [];
                        $param_types = "";
                        $params = [];
                        
                        // Add conditions for existing columns only
                        if (in_array('firstname', $column_names)) {
                            $where_conditions[] = "p.firstname LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        }
                        
                        if (in_array('surname', $column_names)) {
                            $where_conditions[] = "p.surname LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        }
                        
                        if (in_array('email', $column_names)) {
                            $where_conditions[] = "p.email LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        }
                        
                        // Check for gamertag or username
                        if (in_array('gamertag', $column_names)) {
                            $where_conditions[] = "p.gamertag LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        } elseif (in_array('username', $column_names)) {
                            $where_conditions[] = "p.username LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        }
                        
                        if (in_array('phone', $column_names)) {
                            $where_conditions[] = "p.phone LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        }
                        
                        if (in_array('address', $column_names)) {
                            $where_conditions[] = "p.address LIKE ?";
                            $param_types .= "s";
                            $params[] = $search_term;
                        }
                        
                        // Team search
                        $where_conditions[] = "t.name LIKE ?";
                        $param_types .= "s";
                        $params[] = $search_term;
                        
                        if (empty($where_conditions)) {
                            $error = "No searchable columns found in the database.";
                            break;
                        }
                        
                        $where_clause = implode(" OR ", $where_conditions);
                        $sql = "
                            SELECT p.*, t.name as team_name 
                            FROM participant p 
                            LEFT JOIN team t ON p.team_id = t.id 
                            WHERE {$where_clause}
                            ORDER BY p.surname, p.firstname
                        ";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param($param_types, ...$params);
                    }
                    break;
                    
                default:
                    $error = "Invalid search type";
                    break;
            }
            
            if (isset($stmt) && empty($error)) {
                $stmt->execute();
                $result = $stmt->get_result();
                $results = $result->fetch_all(MYSQLI_ASSOC);
                $search_performed = true;
                error_log("Search results: " . count($results) . " records found");
            } elseif (empty($error)) {
                $error = "No valid search criteria provided";
            }
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
            error_log("Search Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    } else {
        // No search parameters provided
        header("Location: search_admin.php");
        exit();
    }

} catch (Exception $e) {
    $error = "System error: " . $e->getMessage();
    error_log("System Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}

// Function to calculate K/D ratio
function calculateKDRatio($kills, $deaths) {
    if ($deaths == 0) {
        return $kills > 0 ? $kills . '.00' : '0.00';
    }
    return number_format($kills / $deaths, 2);
}

// Function to get display name for gamertag/username
function getPlayerDisplayName($participant) {
    if (!empty($participant['gamertag'])) {
        return $participant['gamertag'];
    } elseif (!empty($participant['username'])) {
        return $participant['username'];
    } else {
        return 'No gamertag';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #00f3ff;
            --secondary: #6c63ff;
            --dark: #0a0a1a;
            --darker: #050510;
            --light: #ffffff;
            --danger: #ff3860;
            --success: #00c9a7;
            --warning: #ffc107;
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
            padding: 2rem;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border: 1px solid var(--primary);
            border-radius: 4px;
        }

        .back-link:hover {
            color: #00d4e0;
            border-color: #00d4e0;
            background: rgba(0, 243, 255, 0.1);
        }

        .search-summary {
            background: rgba(20, 20, 40, 0.6);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }

        .results-count {
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .search-query {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            background: rgba(20, 20, 40, 0.6);
            border-radius: 8px;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .no-results i {
            font-size: 2.5rem;
            color: var(--warning);
            margin-bottom: 1rem;
            display: block;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: rgba(20, 20, 40, 0.6);
            border-radius: 8px;
            overflow: hidden;
        }

        .results-table th {
            background: rgba(0, 243, 255, 0.1);
            color: var(--primary);
            font-family: 'Orbitron', sans-serif;
            font-weight: 500;
            text-align: left;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .results-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .results-table tr:last-child td {
            border-bottom: none;
        }

        .results-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .player-name {
            font-weight: 500;
            color: var(--light);
        }

        .player-email {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .team-badge {
            display: inline-block;
            background: rgba(108, 99, 255, 0.1);
            color: var(--secondary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .stats {
            display: flex;
            gap: 1rem;
        }

        .stat {
            text-align: center;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            min-width: 80px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-weight: 600;
            color: var(--light);
            font-size: 1.1rem;
        }

        .kd-ratio {
            font-weight: 700;
        }

        .kd-high {
            color: #00e676; /* Green for good K/D */
        }

        .kd-medium {
            color: #ffc107; /* Yellow for average K/D */
        }

        .kd-low {
            color: #ff5252; /* Red for poor K/D */
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: var(--primary);
            color: var(--dark);
        }

        .btn-edit:hover {
            background: #00d4e0;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #e03150;
            transform: translateY(-2px);
        }

        .team-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .team-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .team-stats {
            display: flex;
            gap: 1.5rem;
        }

        .team-stat {
            text-align: center;
        }

        .team-stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .team-stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--light);
        }

        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .stat {
                width: 100%;
                text-align: left;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .action-btns {
                flex-direction: column;
            }
            
            .results-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="search_admin.php" class="back-link">
                <i class="fas fa-arrow-left"></i> New Search
            </a>
            <h1>Search Results</h1>
            <a href="admin_menu.php" class="back-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>

        <div class="search-summary">
            <div class="results-count">
                <?php 
                $result_count = count($results);
                echo $result_count . ' ' . ($result_count === 1 ? 'result' : 'results') . ' found';
                ?>
            </div>
            <div class="search-query">
                <?php
                if ($search_type === 'team' && !empty($team_name)) {
                    echo 'Team: <strong>' . htmlspecialchars($team_name) . '</strong>';
                } else {
                    echo 'Search by ' . ucfirst($search_type) . ': <strong>' . htmlspecialchars($search_term) . '</strong>';
                }
                ?>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="no-results">
                <i class="fas fa-exclamation-circle"></i>
                <p>An error occurred while performing your search.</p>
                <p><small><?php echo htmlspecialchars($error); ?></small></p>
                <p><a href="search_admin.php" class="btn" style="display: inline-flex; margin-top: 1rem; background: var(--primary); color: var(--dark); padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none;">
                    <i class="fas fa-search"></i> Try a different search
                </a></p>
            </div>
        <?php elseif (empty($results)): ?>
            <div class="no-results">
                <i class="far fa-frown"></i>
                <p>No results found for your search.</p>
                <p><a href="search_admin.php" class="btn" style="display: inline-flex; margin-top: 1rem; background: var(--primary); color: var(--dark); padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none;">
                    <i class="fas fa-search"></i> Try a different search
                </a></p>
            </div>
        <?php else: ?>
            <?php if ($search_type === 'team'): ?>
                <div class="team-section">
                    <div class="team-header">
                        <h2 class="team-title"><?php echo htmlspecialchars($team_name); ?></h2>
                        <div class="team-stats">
                            <div class="team-stat">
                                <div class="team-stat-label">Members</div>
                                <div class="team-stat-value"><?php echo count($results); ?></div>
                            </div>
                            <?php
                            // Calculate team stats
                            $total_kills = array_sum(array_column($results, 'kills'));
                            $total_deaths = array_sum(array_column($results, 'deaths'));
                            $team_kd = $total_deaths > 0 ? $total_kills / $total_deaths : $total_kills;
                            $kd_class = $team_kd >= 1.5 ? 'kd-high' : ($team_kd >= 0.8 ? 'kd-medium' : 'kd-low');
                            ?>
                            <div class="team-stat">
                                <div class="team-stat-label">Total Kills</div>
                                <div class="team-stat-value"><?php echo $total_kills; ?></div>
                            </div>
                            <div class="team-stat">
                                <div class="team-stat-label">Team K/D</div>
                                <div class="team-stat-value <?php echo $kd_class; ?>">
                                    <?php echo number_format($team_kd, 2); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Team</th>
                            <th>Stats</th>
                            <th>K/D Ratio</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $participant): 
                            $kd_ratio = calculateKDRatio($participant['kills'], $participant['deaths']);
                            $kd_numeric = $participant['deaths'] > 0 ? $participant['kills'] / $participant['deaths'] : $participant['kills'];
                            $kd_class = $kd_numeric >= 1.5 ? 'kd-high' : ($kd_numeric >= 0.8 ? 'kd-medium' : 'kd-low');
                        ?>
                            <tr>
                                <td>
                                    <div class="player-name">
                                        <?php echo htmlspecialchars($participant['firstname'] . ' ' . $participant['surname']); ?>
                                    </div>
                                    <div class="player-email">
                                        <?php echo htmlspecialchars($participant['email']); ?>
                                    </div>
                                    <div class="player-gamertag" style="color: #aaa; font-size: 0.85rem; margin-top: 0.25rem;">
                                        @<?php echo htmlspecialchars(getPlayerDisplayName($participant)); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($participant['team_name'])): ?>
                                        <span class="team-badge">
                                            <i class="fas fa-users"></i> 
                                            <?php echo htmlspecialchars($participant['team_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic;">No team</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="stats">
                                        <div class="stat">
                                            <div class="stat-label">Kills</div>
                                            <div class="stat-value"><?php echo (int)$participant['kills']; ?></div>
                                        </div>
                                        <div class="stat">
                                            <div class="stat-label">Deaths</div>
                                            <div class="stat-value"><?php echo (int)$participant['deaths']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="kd-ratio <?php echo $kd_class; ?>">
                                        <?php echo $kd_ratio; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit_participant_form.php?id=<?php echo $participant['id']; ?>" 
                                           class="btn btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete.php?id=<?php echo $participant['id']; ?>" 
                                           class="btn btn-delete" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this participant? This action cannot be undone.');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add confirmation for delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this participant? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>