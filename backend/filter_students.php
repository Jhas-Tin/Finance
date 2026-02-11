<?php
include 'db.php';

$period = $_GET['period'] ?? 'Today';
$className = $_GET['class'] ?? ''; // Get the class from the fetch request
$today = date('Y-m-d');
$conditions = [];

// Handle Date Filter Logic
switch ($period) {
    case 'This Week':
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $conditions[] = "DATE(created_at) >= '$startOfWeek'";
        break;
    case 'This Month':
        $startOfMonth = date('Y-m-01');
        $conditions[] = "DATE(created_at) >= '$startOfMonth'";
        break;
    case 'All Time':
        // No date condition needed
        break;
    default: // Today
        $conditions[] = "DATE(created_at) = '$today'";
        break;
}

// Handle Class Filter Logic
if (!empty($className)) {
    $conditions[] = "class = '" . $conn->real_escape_string($className) . "'";
}

// Build the SQL
$sql = "SELECT * FROM students";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $statusClass = strtolower($row['payment_status']);
        echo "<tr>
                <td><input type='checkbox'></td>
                <td>" . htmlspecialchars($row['student_name']) . "</td>
                <td>" . htmlspecialchars($row['student_id']) . "</td>
                <td>" . htmlspecialchars($row['class']) . "</td>
                <td class='currency'>₱" . number_format($row['tuition_fee'], 2) . "</td>
                <td class='currency'>₱" . number_format($row['activities_fee'], 2) . "</td>
                <td class='currency'>₱" . number_format($row['miscellaneous_fee'], 2) . "</td>
                <td class='currency'>₱" . number_format($row['total_amount'], 2) . "</td>
                <td><span class='status $statusClass'>" . $row['payment_status'] . "</span></td>
                <td class='action-buttons'>
                    <button class='view-btn'>View</button>
                    <button class='delete-btn' onclick='deleteStudent(" . $row['id'] . ")'>Delete</button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='10' style='text-align:center; padding: 40px; color: #64748b;'>No records found for " . ($className ?: 'All Classes') . " in this period.</td></tr>";
}
$conn->close();
?>