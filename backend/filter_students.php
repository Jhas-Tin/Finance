<?php
include 'db.php';

$period = $_GET['period'] ?? 'Today';
$className = $_GET['class'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Get all students
$sql = "SELECT s.student_id, CONCAT(s.first_name,' ',s.last_name) AS student_name, s.student_number, s.course_year
        FROM students s
        WHERE 1";

// Filter by class
if ($className !== '') {
    $sql .= " AND s.course_year = '" . $conn->real_escape_string($className) . "'";
}

// Filter by search
if ($search !== '') {
    $sql .= " AND CONCAT(s.first_name,' ',s.last_name) LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$sql .= " ORDER BY s.student_id DESC";

$result = $conn->query($sql);

// Fetch fee categories
$feeResult = $conn->query("SELECT fee_id, fee_name, default_amount FROM fee_categories");
$fees = [];
while ($f = $feeResult->fetch_assoc()) {
    $fees[$f['fee_id']] = [
        'name' => $f['fee_name'],
        'default' => floatval($f['default_amount'])
    ];
}

// Fetch student balances
$balanceResult = $conn->query("SELECT * FROM student_balances");
$studentBalances = [];
while ($b = $balanceResult->fetch_assoc()) {
    $studentBalances[$b['student_id']][$b['fee_id']] = $b['total_amount'];
}

// Fetch total payments
$paymentResult = $conn->query("SELECT student_id, SUM(amount_paid) AS total_paid FROM payments GROUP BY student_id");
$totalPaid = [];
while ($p = $paymentResult->fetch_assoc()) {
    $totalPaid[$p['student_id']] = floatval($p['total_paid']);
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];

        $tuition = $studentBalances[$student_id][1] ?? $fees[1]['default'];
        $misc    = $studentBalances[$student_id][2] ?? $fees[2]['default'];
        $lab     = $studentBalances[$student_id][3] ?? $fees[3]['default'];
        $uniform = $studentBalances[$student_id][4] ?? $fees[4]['default'];
        $id_req  = $studentBalances[$student_id][5] ?? $fees[5]['default'];

        $total_amount = $tuition + $misc + $lab + $uniform + $id_req;
        $paid_amount = $totalPaid[$student_id] ?? 0;
        $remaining = max($total_amount - $paid_amount, 0);

        // Determine status
        $status = ($remaining <= 0) ? 'paid' : 'pending';
        if ($statusFilter !== '' && strtolower($status) != strtolower($statusFilter)) continue;

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
                <button class='delete-btn' onclick='deleteStudent($student_id)'>Delete</button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='14' style='text-align:center; padding:40px; color:#64748b;'>No records found.</td></tr>";
}

$conn->close();
?>