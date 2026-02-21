<?php
include 'db.php';
date_default_timezone_set('Asia/Manila');

$class = $_GET['class'] ?? 'All Classes';
$period = $_GET['period'] ?? 'Monthly';

$data = [];

/* ================= YEARLY ================= */
if ($period === "Yearly") {
    $currentYear = date("Y");
    $startYear = $currentYear - 4;

    $years = [];
    for ($y = $startYear; $y <= $currentYear; $y++) $years[$y] = 0;

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT YEAR(s.created_at) AS yr,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE s.course_year = ?
            GROUP BY YEAR(s.created_at)
        ");
        $stmt->bind_param("s", $class);
    } else {
        $stmt = $conn->prepare("
            SELECT YEAR(s.created_at) AS yr,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            GROUP BY YEAR(s.created_at)
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $years[(int)$row['yr']] = (float)$row['total'];

    $data = $years; // return keyed object
}

/* ================= MONTHLY ================= */
else if ($period === "Monthly") {
    $months = array_fill(1, 12, 0);
    $currentYear = date("Y");

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT MONTH(s.created_at) AS mn,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE s.course_year = ?
            AND YEAR(s.created_at) = ?
            GROUP BY MONTH(s.created_at)
        ");
        $stmt->bind_param("si", $class, $currentYear);
    } else {
        $stmt = $conn->prepare("
            SELECT MONTH(s.created_at) AS mn,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE YEAR(s.created_at) = ?
            GROUP BY MONTH(s.created_at)
        ");
        $stmt->bind_param("i", $currentYear);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $months[(int)$row['mn']] = (float)$row['total'];

    // return keyed object with month abbreviations
    $monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    $data = [];
    foreach($months as $num => $total) $data[$monthNames[$num-1]] = $total;
}

/* ================= WEEKLY ================= */
else if ($period === "Weekly") {
    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));

    $days = ['Mon'=>0,'Tue'=>0,'Wed'=>0,'Thu'=>0,'Fri'=>0,'Sat'=>0,'Sun'=>0];

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT DAYNAME(s.created_at) AS dy,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE s.course_year = ?
            AND DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY DAYNAME(s.created_at)
        ");
        $stmt->bind_param("sss", $class, $monday, $sunday);
    } else {
        $stmt = $conn->prepare("
            SELECT DAYNAME(s.created_at) AS dy,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY DAYNAME(s.created_at)
        ");
        $stmt->bind_param("ss", $monday, $sunday);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $key = substr($row['dy'],0,3);
        $days[$key] = (float)$row['total'];
    }

    $data = $days; // return keyed object
}

/* ================= DAILY (24 HOURS) ================= */
else if ($period === "Daily") {
    $hours = array_fill(0, 24, 0);

    if ($class != "All Classes") {
        $stmt = $conn->prepare("
            SELECT HOUR(s.created_at) AS hr,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE s.course_year = ?
            AND DATE(s.created_at) = CURDATE()
            GROUP BY HOUR(s.created_at)
        ");
        $stmt->bind_param("s", $class);
    } else {
        $stmt = $conn->prepare("
            SELECT HOUR(s.created_at) AS hr,
                   SUM(sb.total_amount) AS total
            FROM students s
            LEFT JOIN student_balances sb ON s.student_id = sb.student_id
            WHERE DATE(s.created_at) = CURDATE()
            GROUP BY HOUR(s.created_at)
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $hours[(int)$row['hr']] = (float)$row['total'];

    $data = array_values($hours); // keep numeric array
}

echo json_encode($data);
$stmt->close();
$conn->close();
?>