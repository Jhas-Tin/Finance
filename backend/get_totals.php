<?php
include 'db.php';
date_default_timezone_set('Asia/Manila');

$class  = $_GET['class'] ?? 'All Classes';
$period = $_GET['period'] ?? 'Daily';

$whereClass  = "";
$wherePeriod = "";
$params = [];
$types  = "";

if ($class !== "All Classes" && !empty($class)) {
    $whereClass = " AND s.course_year = ?";
    $params[] = $class;
    $types .= "s";
}

if ($period === "Daily") {
    $wherePeriod = " AND DATE(p.payment_date) = CURDATE()";
} elseif ($period === "Weekly") {
    $wherePeriod = " AND YEARWEEK(p.payment_date,1) = YEARWEEK(CURDATE(),1)";
} elseif ($period === "Monthly") {
    $wherePeriod = " AND MONTH(p.payment_date) = MONTH(CURDATE())
                     AND YEAR(p.payment_date) = YEAR(CURDATE())";
} elseif ($period === "Yearly") {
    $wherePeriod = " AND YEAR(p.payment_date) = YEAR(CURDATE())";
}

$feesResult = $conn->query("SELECT fee_id, fee_name FROM fee_categories");

$caseStatements = [];

while ($fee = $feesResult->fetch_assoc()) {
    $fee_id   = (int)$fee['fee_id'];
    $fee_name = $fee['fee_name'];

    $caseStatements[] = "
        COALESCE(SUM(
            CASE 
                WHEN sb.fee_id = $fee_id 
                THEN sb.paid_amount 
                ELSE 0 
            END
        ),0) AS `$fee_name`
    ";
}

$caseSql = implode(", ", $caseStatements);

$sql = "
SELECT $caseSql
FROM student_balances sb
JOIN students s ON sb.student_id = s.student_id
WHERE 1=1
$whereClass
AND EXISTS (
    SELECT 1 FROM payments p
    WHERE p.student_id = sb.student_id
    $wherePeriod
)
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

foreach ($row as $key => $value) {
    $row[$key] = (float)$value;
}

$row['total_amount'] = array_sum($row);

echo json_encode($row);

$stmt->close();
$conn->close();
?>