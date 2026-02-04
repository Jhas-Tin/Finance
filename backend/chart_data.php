<?php
include 'db.php';

// Get filters
$class = $_GET['class'] ?? 'All Classes';
$period = $_GET['period'] ?? 'Monthly';

// Prepare months array
$months = array_fill(1, 12, 0);

// Build SQL
$whereClass = ($class != "All Classes") ? " AND class='$class'" : "";

// Only monthly is implemented now
$sql = "SELECT MONTH(created_at) AS month, SUM(total_amount) AS total 
        FROM students 
        WHERE 1 $whereClass
        GROUP BY MONTH(created_at)";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $months[(int)$row['month']] = (float)$row['total'];
    }
}

// Return JSON
echo json_encode(array_values($months));

$conn->close();
