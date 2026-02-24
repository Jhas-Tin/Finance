<?php
include 'db.php';
date_default_timezone_set('Asia/Manila');

// 1. Get parameters and sanitize
// If class is empty, "All Classes", or not set, we treat it as "All"
$classInput = $_GET['class'] ?? '';
$isAllClasses = ($classInput === '' || $classInput === 'All Classes');

$period = $_GET['period'] ?? 'Daily';
$data = [];

/* ================= YEARLY ================= */
if ($period === "Yearly") {
    $currentYear = date("Y");
    $startYear = $currentYear - 4;
    $years = [];
    for ($y = $startYear; $y <= $currentYear; $y++) $years[$y] = 0;

    if (!$isAllClasses) {
        $stmt = $conn->prepare("
            SELECT YEAR(p.payment_date) AS yr, SUM(p.amount_paid) AS total
            FROM payments p
            LEFT JOIN students s ON p.student_id = s.student_id
            WHERE s.course_year = ? AND YEAR(p.payment_date) BETWEEN ? AND ?
            GROUP BY YEAR(p.payment_date)
        ");
        $stmt->bind_param("sii", $classInput, $startYear, $currentYear);
    } else {
        $stmt = $conn->prepare("
            SELECT YEAR(p.payment_date) AS yr, SUM(p.amount_paid) AS total
            FROM payments p
            WHERE YEAR(p.payment_date) BETWEEN ? AND ?
            GROUP BY YEAR(p.payment_date)
        ");
        $stmt->bind_param("ii", $startYear, $currentYear);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $years[(int)$row['yr']] = (float)$row['total'];
    $data = $years;
}

/* ================= MONTHLY ================= */
else if ($period === "Monthly") {
    $months = array_fill(1, 12, 0);
    $currentYear = date("Y");

    if (!$isAllClasses) {
        $stmt = $conn->prepare("
            SELECT MONTH(p.payment_date) AS mn, SUM(p.amount_paid) AS total
            FROM payments p
            LEFT JOIN students s ON p.student_id = s.student_id
            WHERE s.course_year = ? AND YEAR(p.payment_date) = ?
            GROUP BY MONTH(p.payment_date)
        ");
        $stmt->bind_param("si", $classInput, $currentYear);
    } else {
        $stmt = $conn->prepare("
            SELECT MONTH(p.payment_date) AS mn, SUM(p.amount_paid) AS total
            FROM payments p
            WHERE YEAR(p.payment_date) = ?
            GROUP BY MONTH(p.payment_date)
        ");
        $stmt->bind_param("i", $currentYear);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $months[(int)$row['mn']] = (float)$row['total'];

    $monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    $formattedData = [];
    foreach($months as $num => $total) $formattedData[$monthNames[$num-1]] = $total;
    $data = $formattedData;
}

/* ================= WEEKLY ================= */
else if ($period === "Weekly") {
    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));
    $days = ['Mon'=>0,'Tue'=>0,'Wed'=>0,'Thu'=>0,'Fri'=>0,'Sat'=>0,'Sun'=>0];

    if (!$isAllClasses) {
        $stmt = $conn->prepare("
            SELECT DAYNAME(p.payment_date) AS dy, SUM(p.amount_paid) AS total
            FROM payments p
            LEFT JOIN students s ON p.student_id = s.student_id
            WHERE s.course_year = ? AND DATE(p.payment_date) BETWEEN ? AND ?
            GROUP BY DAYNAME(p.payment_date)
        ");
        $stmt->bind_param("sss", $classInput, $monday, $sunday);
    } else {
        $stmt = $conn->prepare("
            SELECT DAYNAME(p.payment_date) AS dy, SUM(p.amount_paid) AS total
            FROM payments p
            WHERE DATE(p.payment_date) BETWEEN ? AND ?
            GROUP BY DAYNAME(p.payment_date)
        ");
        $stmt->bind_param("ss", $monday, $sunday);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $shortDay = substr($row['dy'], 0, 3);
        if(isset($days[$shortDay])) $days[$shortDay] = (float)$row['total'];
    }
    $data = $days;
}

/* ================= DAILY (24 HOURS) ================= */
else if ($period === "Daily") {
    $today = date('Y-m-d');
    $hours = array_fill(0, 24, 0);

    if (!$isAllClasses) {
        $stmt = $conn->prepare("
            SELECT HOUR(p.payment_date) AS hr, SUM(p.amount_paid) AS total
            FROM payments p
            LEFT JOIN students s ON p.student_id = s.student_id
            WHERE s.course_year = ? AND DATE(p.payment_date) = ?
            GROUP BY HOUR(p.payment_date)
        ");
        $stmt->bind_param("ss", $classInput, $today);
    } else {
        $stmt = $conn->prepare("
            SELECT HOUR(p.payment_date) AS hr, SUM(p.amount_paid) AS total
            FROM payments p
            WHERE DATE(p.payment_date) = ?
            GROUP BY HOUR(p.payment_date)
        ");
        $stmt->bind_param("s", $today);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $hours[(int)$row['hr']] = (float)$row['total'];
    }
    // Return just the values for the JS array
    $data = array_values($hours);
}

header('Content-Type: application/json');
echo json_encode($data);
if (isset($stmt)) $stmt->close();
$conn->close();
?>