<?php
include 'db.php';

date_default_timezone_set('Asia/Manila');

$class = $_GET['class'] ?? 'All Classes';
$period = $_GET['period'] ?? 'Daily';

$whereClass = "";
$params = [];
$types = "";

if ($class != "All Classes" && !empty($class)) {
    $whereClass = " AND class = ?";
    $params[] = $class;
    $types .= "s";
}

$wherePeriod = "";

if ($period === "Daily") {

    $wherePeriod = " AND DATE(created_at) = CURDATE()";
}

if ($period === "Weekly") {

    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));

    $wherePeriod = " AND DATE(created_at) BETWEEN ? AND ?";
    $params[] = $monday;
    $params[] = $sunday;
    $types .= "ss";
}

if ($period === "Monthly") {

    $wherePeriod = " AND MONTH(created_at)=? AND YEAR(created_at)=?";
    $params[] = date('m');
    $params[] = date('Y');
    $types .= "ii";
}

if ($period === "Yearly") {

    $wherePeriod = " AND YEAR(created_at)=?";
    $params[] = date('Y');
    $types .= "i";
}

$sql = "
SELECT 
    COALESCE(SUM(total_amount),0) AS total_amount,
    COALESCE(SUM(tuition_fee),0) AS total_tuition,
    COALESCE(SUM(activities_fee),0) AS total_activities,
    COALESCE(SUM(miscellaneous_fee),0) AS total_misc
FROM students
WHERE 1=1 $whereClass $wherePeriod
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'total_amount' => (float)$row['total_amount'],
    'total_tuition' => (float)$row['total_tuition'],
    'total_activities' => (float)$row['total_activities'],
    'total_misc' => (float)$row['total_misc']
]);

$stmt->close();
$conn->close();
?>
