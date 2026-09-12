<?php
// Check exam_results table structure and data
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "playmates";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get table structure
echo "=== EXAM_RESULTS TABLE STRUCTURE ===\n";
$result = $conn->query("DESCRIBE exam_results");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

// Get sample data
echo "\n=== SAMPLE EXAM_RESULTS DATA (Last 5 records) ===\n";
$result = $conn->query("SELECT * FROM exam_results ORDER BY created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

// Count total records
echo "\n=== EXAM_RESULTS COUNT ===\n";
$result = $conn->query("SELECT COUNT(*) as count FROM exam_results");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total records: " . $row['count'] . "\n";
}

$conn->close();
?>
