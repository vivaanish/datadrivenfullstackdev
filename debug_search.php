<?php
// Debug script to test search functionality
require_once 'dbconnect.php';

// Test participant search
$search_term = 'John';
$search_type = 'name';

echo "Testing search for: $search_term\n";
echo "Search type: $search_type\n\n";

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

echo "SQL Query: $sql\n\n";

$result = $conn->query($sql);

if ($result) {
    echo "Results found: " . $result->num_rows . "\n";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " - " . $row['firstname'] . " " . $row['surname'] . " - " . $row['email'] . "\n";
        }
    } else {
        echo "No results found.\n";
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

echo "\n\nTesting team search...\n";
$team_name = 'NovaCore';
$sql = "SELECT t.* FROM team t WHERE t.name LIKE '%$team_name%'";
echo "Team SQL: $sql\n";

$result = $conn->query($sql);
if ($result) {
    echo "Team results: " . $result->num_rows . "\n";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "Team: " . $row['name'] . " - " . $row['location'] . "\n";
        }
    }
} else {
    echo "Team query error: " . $conn->error . "\n";
}
?>
