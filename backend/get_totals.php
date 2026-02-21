<?php
include 'db.php';
date_default_timezone_set('Asia/Manila');

$class = $_GET['class'] ?? 'All Classes';
$period = $_GET['period'] ?? 'Daily';

$whereClass = "";
$params = [];
$types = "";

// Filter by class
if ($class != "All Classes" && !empty($class)) {
    $whereClass = " AND s.course_year = ?";
    $params[] = $class;
    $types .= "s";
}

// Filter by period
$wherePeriod = "";
if ($period === "Daily") {
    $wherePeriod = " AND DATE(sb.id) = CURDATE()"; // <-- we will ignore this because sb has no date
} elseif ($period === "Weekly") {
    $wherePeriod = "";
} elseif ($period === "Monthly") {
    $wherePeriod = "";
} elseif ($period === "Yearly") {
    $wherePeriod = "";
}

// Get all fee categories
$feesResult = $conn->query("SELECT fee_id, fee_name FROM fee_categories");
$caseStatements = [];

while ($fee = $feesResult->fetch_assoc()) {
    $fee_id = $fee['fee_id'];
    $fee_name = $fee['fee_name'];

    $caseStatements[] = "COALESCE(SUM(CASE WHEN sb.fee_id = $fee_id THEN sb.paid_amount ELSE 0 END),0) AS `$fee_name`";
}

$caseSql = implode(", ", $caseStatements);

// Main query: join only student_balances → students
$sql = "
SELECT $caseSql
FROM student_balances sb
JOIN students s ON sb.student_id = s.student_id
WHERE 1=1 $whereClass
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Convert to float
foreach ($row as $key => $value) {
    $row[$key] = (float)$value;
}

// Add total_amount
$row['total_amount'] = array_sum($row);

echo json_encode($row);

$stmt->close();
$conn->close();
?>