<?php
include 'db.php';

date_default_timezone_set('Asia/Manila');

$class = $_GET['class'] ?? 'All Classes';
$period = $_GET['period'] ?? 'Monthly';

$data = [];

if ($period === "Yearly") {

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT YEAR(created_at) AS year, COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE class = ?
            GROUP BY YEAR(created_at)
            ORDER BY YEAR(created_at)
        ");
        $stmt->bind_param("s", $class);
    } else {
        $stmt = $conn->prepare("
            SELECT YEAR(created_at) AS year, COALESCE(SUM(total_amount),0) AS total
            FROM students
            GROUP BY YEAR(created_at)
            ORDER BY YEAR(created_at)
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[(int)$row['year']] = (float)$row['total'];
    }

}

else if ($period === "Monthly") {

    $months = array_fill(1, 12, 0);
    $currentYear = date("Y");

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT MONTH(created_at) AS month, COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE class = ? AND YEAR(created_at) = ?
            GROUP BY MONTH(created_at)
        ");
        $stmt->bind_param("si", $class, $currentYear);
    } else {
        $stmt = $conn->prepare("
            SELECT MONTH(created_at) AS month, COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE YEAR(created_at) = ?
            GROUP BY MONTH(created_at)
        ");
        $stmt->bind_param("i", $currentYear);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $months[(int)$row['month']] = (float)$row['total'];
    }

    $data = array_values($months);
}

else if ($period === "Weekly") {

    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));

    $daysOfWeek = ['Mon'=>0,'Tue'=>0,'Wed'=>0,'Thu'=>0,'Fri'=>0,'Sat'=>0,'Sun'=>0];

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT DAYNAME(created_at) AS day, COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE class = ? 
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DAYNAME(created_at)
        ");
        $stmt->bind_param("sss", $class, $monday, $sunday);
    } else {
        $stmt = $conn->prepare("
            SELECT DAYNAME(created_at) AS day, COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DAYNAME(created_at)
        ");
        $stmt->bind_param("ss", $monday, $sunday);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $dayKey = substr($row['day'],0,3);
        $daysOfWeek[$dayKey] = (float)$row['total'];
    }

    $data = $daysOfWeek;
}

else if ($period === "Daily") {

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE class = ?
            AND DATE(created_at) = CURDATE()
        ");
        $stmt->bind_param("s", $class);
    } else {
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_amount),0) AS total
            FROM students
            WHERE DATE(created_at) = CURDATE()
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $data = ['today' => (float)$row['total']];
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
