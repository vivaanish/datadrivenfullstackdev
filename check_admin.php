<?php
require_once 'dbconnect.php';

$sql = "SELECT * FROM user";
$result = $conn->query($sql);

echo "<h1>Admin Users Check</h1>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Username: " . $row["username"]. " - Password: " . $row["password"]. "<br>";
    }
} else {
    echo "0 results";
}
?>
