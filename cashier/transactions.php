<?php
// 1. Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sia_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Handle Search and Filter Inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? trim($_GET['course']) : '';

// 3. SQL Query
$sql = "SELECT 
            p.payment_id, 
            p.amount_paid, 
            p.payment_date, 
            s.student_number, 
            s.first_name, 
            s.last_name,
            s.course_year, 
            GROUP_CONCAT(DISTINCT CONCAT(fc.fee_name, ': ₱', FORMAT(sb.paid_amount, 2)) SEPARATOR '<br>') AS fee_breakdown
        FROM payments p 
        JOIN students s ON p.student_id = s.student_id 
        LEFT JOIN student_balances sb ON s.student_id = sb.student_id
        LEFT JOIN fee_categories fc ON sb.fee_id = fc.fee_id
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (
        s.student_number LIKE ? OR 
        s.first_name LIKE ? OR 
        s.last_name LIKE ? OR
        CONCAT(s.first_name, ' ', s.last_name) LIKE ?
    )";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if ($course_filter !== '') {
    $sql .= " AND s.course_year = ?";
    $params[] = $course_filter;
    $types .= "s";
}

$sql .= " GROUP BY p.payment_id ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$courses_res = $conn->query("SELECT DISTINCT course_year FROM students WHERE course_year IS NOT NULL AND course_year != '' ORDER BY course_year ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCC Portal | Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root { --portal-navy: #2c3e5e; --portal-blue: #4460f1; --portal-gold: #fccb06; --portal-bg: #f5f7fb; }
        body { background-color: var(--portal-bg); font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar Styling */
        .sidebar { height: 100vh; background: var(--portal-navy); color: white; position: fixed; width: 260px; z-index: 1000; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.1); }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); margin: 5px 15px; border-radius: 8px; padding: 12px; display: flex; align-items: center; text-decoration: none; transition: all 0.2s; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
        .sidebar .nav-link.active { background: var(--portal-blue); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .sidebar .nav-link.logout-link:hover { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; }
        
        .logo-text { font-size: 1.2rem; font-weight: 800; letter-spacing: 1px; }
        .logo-sub { font-size: 0.65rem; color: var(--portal-gold); text-transform: uppercase; }

        .main-content { margin-left: 260px; padding: 35px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.04); background: #ffffff; overflow: hidden; }
        .table thead th { color: var(--portal-navy); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px; border-bottom: 1px solid #eee; }
        .table tbody td { padding: 15px; color: #4a5568; font-size: 0.9rem; }
        .badge-paid { background-color: rgba(39, 174, 96, 0.1); color: #27ae60; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
        .filter-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .section-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
        .paid-box { background-color: #f0fff4; border-left: 4px solid #38a169; }
        .dropdown-toggle { background: white; border: none; padding: 8px 16px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="sidebar d-none d-md-block">
    <div class="text-center py-5">
        <div class="logo-text text-white">HOLY CROSS COLLEGE</div>
        <div class="logo-sub">Fides • Caritas • Libertas</div>
    </div>
    <nav class="nav flex-column mb-auto">
        <a class="nav-link" href="dashboard.php"><span class="material-icons me-3">dashboard</span> Dashboard</a>
         <a class="nav-link active" href="../indexFinance.php"><span class="material-icons me-3">account_balance_wallet</span> Finance</a>
        <a class="nav-link" href="cashiering.php"><span class="material-icons me-3">account_balance_wallet</span> Cashiering</a>
        <a class="nav-link active" href="transactions.php"><span class="material-icons me-3">receipt_long</span> Transactions</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: var(--portal-navy);">Transaction History</h2>
            <small class="text-muted">Review and manage student payment records</small>
        </div>
        <div class="dropdown">
            <button class="btn dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <span class="material-icons align-middle me-1" style="font-size: 18px;">account_circle</span> Admin User
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item py-2" href="logout.php"><span class="material-icons align-middle me-2" style="font-size: 18px;">logout</span>Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="filter-section shadow-sm">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="input-group border rounded">
                    <span class="input-group-text bg-white border-0"><span class="material-icons text-muted">search</span></span>
                    <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Search ID or Name..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="course" class="form-select border rounded shadow-none">
                    <option value="">All Courses/Years</option>
                    <?php while($c = $courses_res->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['course_year']) ?>" <?= ($course_filter == $c['course_year']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['course_year']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" style="background: var(--portal-blue);">Apply</button>
                <a href="transactions.php" class="btn btn-light border w-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>OR Number</th>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Course/Year</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="align-middle">
                                <td><?= date('M d, Y', strtotime($row['payment_date'])) ?></td>
                                <td class="fw-bold" style="color: var(--portal-navy);">
                                    OR-<?= date('Y', strtotime($row['payment_date'])) ?>-<?= str_pad($row['payment_id'], 4, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td><?= htmlspecialchars($row['student_number']) ?></td>
                                <td class="text-uppercase small"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['course_year']) ?></td>
                                <td class="fw-bold">₱<?= number_format($row['amount_paid'], 2) ?></td>
                                <td><span class="badge-paid">Paid</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white me-1 shadow-sm" 
                                            onclick="viewDetails(
                                                '<?= addslashes($row['first_name'] . ' ' . $row['last_name']) ?>', 
                                                '<?= addslashes($row['course_year']) ?>', 
                                                '<?= addslashes($row['fee_breakdown']) ?>'
                                            )">
                                        <span class="material-icons" style="font-size: 18px;">visibility</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary shadow-sm" onclick="window.print()">
                                        <span class="material-icons" style="font-size: 18px;">print</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--portal-navy);">Transaction Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 id="modalStudentName" class="fw-bold mb-0 text-primary"></h6>
                    <small id="modalCourseYear" class="text-muted"></small>
                </div>
                <div class="mb-2">
                    <span class="section-label text-success">Paid in this Transaction</span>
                    <div id="modalFeeDetails" class="paid-box p-3 rounded shadow-sm lh-lg"></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewDetails(name, course, paid) {
    document.getElementById('modalStudentName').innerText = name;
    document.getElementById('modalCourseYear').innerText = course;
    document.getElementById('modalFeeDetails').innerHTML = paid || '<span class="text-muted">No breakdown data available.</span>';
    
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}
</script>
</body>
</html>