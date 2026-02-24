<?php
include 'db.php';

// 1. Capture Filters
$period = $_GET['period'] ?? 'Today';
$className = $_GET['class'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// 2. Handle Date Logic (Where Clause)
$dateWhere = "";
$today = date('Y-m-d');

switch ($period) {
    case 'This Week':
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $dateWhere = " AND DATE(s.created_at) >= '$startOfWeek'";
        break;
    case 'This Month':
        $startOfMonth = date('Y-m-01');
        $dateWhere = " AND DATE(s.created_at) >= '$startOfMonth'";
        break;
    case 'All Time':
        $dateWhere = ""; 
        break;
    default: // Today
        $dateWhere = " AND DATE(s.created_at) = '$today'";
        break;
}

// 3. Build Main Student Query
$sql = "SELECT s.student_id, CONCAT(s.first_name,' ',s.last_name) AS student_name, 
               s.student_number, s.course_year 
        FROM students s 
        WHERE 1=1"; // 1=1 makes it easier to append 'AND' conditions

// Append Filters
$sql .= $dateWhere;

if ($className !== '') {
    $sql .= " AND s.course_year = '" . $conn->real_escape_string($className) . "'";
}

if ($search !== '') {
    $sql .= " AND (CONCAT(s.first_name,' ',s.last_name) LIKE '%" . $conn->real_escape_string($search) . "%' 
               OR s.student_number LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$sql .= " ORDER BY s.student_id DESC";

$result = $conn->query($sql);

// 4. Fetch fee categories for calculations
$feeResult = $conn->query("SELECT fee_id, fee_name, default_amount FROM fee_categories");
$fees = [];
while ($f = $feeResult->fetch_assoc()) {
    $fees[$f['fee_id']] = [
        'name' => $f['fee_name'],
        'default' => floatval($f['default_amount'])
    ];
}

// 5. Fetch student balances (Overrides)
$balanceResult = $conn->query("SELECT * FROM student_balances");
$studentBalances = [];
while ($b = $balanceResult->fetch_assoc()) {
    $studentBalances[$b['student_id']][$b['fee_id']] = $b['total_amount'];
}

// 6. Fetch total payments per student
$paymentResult = $conn->query("SELECT student_id, SUM(amount_paid) AS total_paid FROM payments GROUP BY student_id");
$totalPaid = [];
while ($p = $paymentResult->fetch_assoc()) {
    $totalPaid[$p['student_id']] = floatval($p['total_paid']);
}

// 7. Output Table Rows
if ($result && $result->num_rows > 0) {
    $foundVisibleRecord = false;

    while ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];

        // Calculate specific fees (using ID-based mapping from your schema)
        $tuition = $studentBalances[$student_id][1] ?? ($fees[1]['default'] ?? 0);
        $misc    = $studentBalances[$student_id][2] ?? ($fees[2]['default'] ?? 0);
        $lab     = $studentBalances[$student_id][3] ?? ($fees[3]['default'] ?? 0);
        $uniform = $studentBalances[$student_id][4] ?? ($fees[4]['default'] ?? 0);
        $id_req  = $studentBalances[$student_id][5] ?? ($fees[5]['default'] ?? 0);

        $total_amount = $tuition + $misc + $lab + $uniform + $id_req;
        $paid_amount = $totalPaid[$student_id] ?? 0;
        $remaining = max($total_amount - $paid_amount, 0);

        // Determine status
        $status = ($remaining <= 0) ? 'paid' : 'pending';
        
        // Skip row if it doesn't match the Status Filter
        if ($statusFilter !== '' && strtolower($status) != strtolower($statusFilter)) {
            continue;
        }

        $foundVisibleRecord = true;

        echo "<tr>
            <td><input type='checkbox'></td>
            <td>".htmlspecialchars($row['student_name'])."</td>
            <td>".htmlspecialchars($row['student_number'])."</td>
            <td>".htmlspecialchars($row['course_year'])."</td>
            <td class='currency'>₱".number_format($tuition,2)."</td>
            <td class='currency'>₱".number_format($misc,2)."</td>
            <td class='currency'>₱".number_format($lab,2)."</td>
            <td class='currency'>₱".number_format($uniform,2)."</td>
            <td class='currency'>₱".number_format($id_req,2)."</td>
            <td class='currency'>₱".number_format($paid_amount,2)."</td>
            <td class='currency'>₱".number_format($remaining,2)."</td>
            <td class='currency'>₱".number_format($total_amount,2)."</td>
            <td><span class='status $status'>".ucfirst($status)."</span></td>
            <td class='action-buttons'>
                <button class='view-btn'>View</button>
                <button class='delete-btn' onclick='deleteStudent($student_id)'>Delete</button>
            </td>
        </tr>";
    }

    if (!$foundVisibleRecord) {
        echo "<tr><td colspan='14' style='text-align:center; padding:40px; color:#64748b;'>No records match the selected status filter.</td></tr>";
    }

} else {
    echo "<tr><td colspan='14' style='text-align:center; padding:40px; color:#64748b;'>No records found for $period.</td></tr>";
}

$conn->close();
?>