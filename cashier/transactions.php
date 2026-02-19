transactions.php
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #e8ecf1;
            display: flex;
            min-height: 100vh;
            color: #2c3e50;
        }

        /* SIDEBAR STYLES - EXACT COPY FROM FINANCE SIDEBAR.PHP */
        .sidebar {
            width: 240px;
            height: 100vh;
            background: #0b1f33;
            color: #fff;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar.closed {
            transform: translateX(-240px);
        }

        .sidebar-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            width: 70px;
            height: 70px;
            background: transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-container i {
            font-size: 20px;
            color: #0b1f33;
        }

        .logo-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .school-name {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .school-name h2 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            color: #fff;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        .school-name h3 {
            font-size: 10px;
            margin: 5px 0 0 0;
            color: #9fb3c8;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .sidebar h3.menu-title {
            font-size: 12px;
            margin: 20px 0 15px;
            color: #9fb3c8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            padding-left: 5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 8px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            color: #9fb3c8;
            transition: color 0.2s ease;
        }

        .sidebar-menu a.active,
        .sidebar-menu a:hover {
            background: #f59e0b;
            color: #000;
        }

        .sidebar-menu a.active i,
        .sidebar-menu a:hover i {
            color: #000;
        }

        /* Scrollbar styling for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* NAVBAR STYLES - EXACT COPY FROM FINANCE */
        :root {
            --bg-primary: #0b1f33;
            --bg-secondary: #fff;
            --text-primary: #fff;
            --text-secondary: #0b1f33;
            --text-muted: #9fb3c8;
            --accent: #f59e0b;
            --border-color: rgba(255, 255, 255, 0.1);
            --card-bg: #f0f0f0;
        }

        body.dark-mode {
            --bg-primary: #0b1f33;
            --bg-secondary: #fff;
            --text-primary: #fff;
            --text-secondary: #0b1f33;
            --text-muted: #9fb3c8;
            --accent: #f59e0b;
            --border-color: rgba(255, 255, 255, 0.1);
            --card-bg: #f0f0f0;
        }

        body.light-mode {
            --bg-primary: #f5f5f5;
            --bg-secondary: #fff;
            --text-primary: #0b1f33;
            --text-secondary: #fff;
            --text-muted: #6b7280;
            --accent: #f59e0b;
            --border-color: rgba(0, 0, 0, 0.1);
            --card-bg: #e5e7eb;
        }

        .navbar {
            height: 65px;
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease;
            position: fixed;
            top: 0;
            right: 0;
            left: 240px;
            z-index: 99;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle {
            font-size: 24px;
            cursor: pointer;
            color: var(--text-primary);
            transition: color 0.2s ease;
        }

        .menu-toggle:hover {
            color: var(--accent);
        }

        .navbar-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .navbar h1 {
            font-size: 16px;
            font-weight: 700;   
            margin: 0;
            color: var(--text-primary);
        }

        .navbar-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon {
            font-size: 18px;
            cursor: pointer;
            color: var(--text-primary);
            transition: color 0.2s ease;
            width: 20px;
            text-align: center;
        }

        .nav-icon:hover {
            color: var(--accent);
        }

        .theme-toggle {
            font-size: 18px;
            cursor: pointer;
            color: var(--text-primary);
            transition: color 0.2s ease;
            width: 20px;
            text-align: center;
        }

        .theme-toggle:hover {
            color: var(--accent);
        }

        .nav-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .nav-profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 13px;
            flex-shrink: 0;
        }

        .nav-profile-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .nav-profile-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            white-space: nowrap;
        }

        .nav-profile-role {
            font-size: 11px;
            color: var(--text-muted);
            margin: 0;
            white-space: nowrap;
        }

        .profile-dropdown-toggle {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .dropdown-arrow {
            font-size: 10px;
            color: var(--text-muted);
            transition: transform 0.2s ease;
            margin-left: 4px;
        }

        .profile-dropdown-toggle:hover .dropdown-arrow {
            color: var(--text-primary);
            transform: translateY(2px);
        }

        .profile-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--bg-secondary);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 150px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
            z-index: 1000;
        }

        .profile-dropdown-menu.active {
            display: block;
        }

        .profile-dropdown-menu a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
            border-bottom: 1px solid var(--border-color);
        }

        .profile-dropdown-menu a:last-child {
            border-bottom: none;
        }

        .profile-dropdown-menu a:hover {
            background: var(--accent);
            color: #fff;
        }

        .semester-dropdown-toggle {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .semester-dropdown-arrow {
            font-size: 10px;
            color: var(--text-muted);
            transition: transform 0.2s ease;
            margin-left: 4px;
        }

        .semester-dropdown-toggle:hover .semester-dropdown-arrow {
            color: var(--text-primary);
            transform: translateY(2px);
        }

        .semester-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--bg-secondary);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
            z-index: 100;
        }

        .semester-dropdown-menu.active {
            display: block;
        }

        .semester-dropdown-menu a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
            border-bottom: 1px solid var(--border-color);
        }

        .semester-dropdown-menu a:last-child {
            border-bottom: none;
        }

        .semester-dropdown-menu a:hover {
            background: var(--accent);
            color: #fff;
        }

        .semester-dropdown-menu a.active {
            background: var(--accent);
            color: #fff;
        }

        /* MAIN CONTENT - Adjusted for navbar */
        .main-content {
            flex: 1;
            margin-left: 240px;
            margin-top: 65px;
            padding: 30px;
        }

        /* FILTER SECTION */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
        }

        .input-group-text {
            background: transparent;
            border: none;
            padding: 0 12px;
            color: #94a3b8;
        }

        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            color: #1e293b;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-control.border-0 {
            border: none;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-light {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-light:hover {
            background: #e2e8f0;
        }

        /* CARD AND TABLE STYLES */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }

        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .badge-paid {
            background: #dcfce7;
            color: #166534;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        /* ACTION BUTTONS */
        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background: #2563eb;
        }

        .btn-outline-secondary {
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-outline-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        /* MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-dialog {
            max-width: 500px;
            width: 90%;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .paid-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 16px;
            border-radius: 8px;
        }

        .section-label {
            font-size: 12px;
            font-weight: 600;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: block;
        }

        /* ROW GRID */
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .col-md-5 {
            flex: 1 1 calc(41.666% - 20px);
        }

        .col-md-4 {
            flex: 1 1 calc(33.333% - 20px);
        }

        .col-md-3 {
            flex: 1 1 calc(25% - 20px);
        }

        .d-flex {
            display: flex;
        }

        .gap-2 {
            gap: 10px;
        }

        .gap-3 {
            gap: 15px;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .fw-bold {
            font-weight: 700;
        }

        .text-muted {
            color: #64748b;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .py-5 {
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .shadow-sm {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .w-100 {
            width: 100%;
        }

        .w-50 {
            width: 50%;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-240px);
            }
            .navbar {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .col-md-5, .col-md-4, .col-md-3 {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body class="dark-mode">

<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <img src="../components/images/hcc.png" alt="Holy Cross College Logo" class="logo-image">
        </div>
        <div class="school-name">
            <h2>HOLY CROSS COLLEGE</h2>
            <h3>School Management System</h3>
        </div>
    </div>

    <h3 class="menu-title">Main Menu</h3>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a></li>
        <li><a href="../index.php"> 
            <i class="fas fa-money-bill-wave"></i>
            Finance
        </a></li>
        <li><a href="cashiering.php">
            <i class="fas fa-money-bill-wave"></i>
            Cashiering
        </a></li>
        <li><a href="transactions.php" class="active">
            <i class="fas fa-receipt"></i>
            Transactions
        </a></li>
    </ul>
</div>

<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-left">
        <span id="menuToggle" class="menu-toggle">☰</span>
        <div class="navbar-title">
            <h1>TRANSACTION HISTORY</h1>
            <div class="semester-dropdown-toggle">
                <span class="navbar-subtitle" id="semesterDisplay">Transactions / 2024-2025 1st Semester</span>
                <span class="semester-dropdown-arrow">▼</span>
                <div class="semester-dropdown-menu" id="semesterDropdown">
                    <a href="#" data-semester="2024-2025-1st">Transactions / 2024-2025 1st Semester</a>
                    <a href="#" data-semester="2024-2025-2nd">Transactions / 2024-2025 2nd Semester</a>
                    <a href="#" data-semester="2023-2024-1st">Transactions / 2023-2024 1st Semester</a>
                    <a href="#" data-semester="2023-2024-2nd">Transactions / 2023-2024 2nd Semester</a>
                    <a href="#" data-semester="2022-2023-1st">Transactions / 2022-2023 1st Semester</a>
                    <a href="#" data-semester="2022-2023-2nd">Transactions / 2022-2023 2nd Semester</a>
                </div>
            </div>
        </div>
    </div>

    <div class="nav-right">
        <span class="nav-icon">🔔</span>
        <span id="themeToggle" class="theme-toggle">🌙</span>
        <div class="profile-dropdown-toggle">
            <div class="nav-profile">
                <div class="nav-profile-avatar">SA</div>
                <div class="nav-profile-text">
                    <p class="nav-profile-name">Sample Admin</p>
                    <p class="nav-profile-role">Admin</p>
                </div>
            </div>
            <span class="dropdown-arrow">▼</span>
            <div class="profile-dropdown-menu">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <li><a class="dropdown-item py-2" href="logout.php"><span class="material-icons align-middle me-2" style="font-size: 18px;"></span>Logout</a></li>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Transaction History</h2>
            <small style="color: #64748b; font-size: 14px;">Review and manage student payment records</small>
        </div>
    </div>

    <div class="filter-section">
        <form action="" method="GET" style="display: flex; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1 1 calc(41.666% - 20px);">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search" style="color: #94a3b8;"></i></span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Search ID or Name..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div style="flex: 1 1 calc(33.333% - 20px);">
                <select name="course" class="form-select">
                    <option value="">All Courses/Years</option>
                    <?php while($c = $courses_res->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['course_year']) ?>" <?= ($course_filter == $c['course_year']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['course_year']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="flex: 1 1 calc(25% - 20px); display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Apply</button>
                <a href="transactions.php" class="btn-light" style="flex: 0.5; text-decoration: none;">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div style="overflow-x: auto;">
            <table class="table">
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
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['payment_date'])) ?></td>
                                <td style="font-weight: 600; color: #1e293b;">
                                    OR-<?= date('Y', strtotime($row['payment_date'])) ?>-<?= str_pad($row['payment_id'], 4, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td><?= htmlspecialchars($row['student_number']) ?></td>
                                <td style="text-transform: uppercase; font-size: 13px;"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['course_year']) ?></td>
                                <td style="font-weight: 600;">₱<?= number_format($row['amount_paid'], 2) ?></td>
                                <td><span class="badge-paid">Paid</span></td>
                                <td class="text-center">
                                    <button class="btn-sm btn-info" style="margin-right: 5px;" 
                                            onclick="viewDetails(
                                                '<?= addslashes($row['first_name'] . ' ' . $row['last_name']) ?>', 
                                                '<?= addslashes($row['course_year']) ?>', 
                                                '<?= addslashes($row['fee_breakdown']) ?>'
                                            )">
                                        <i class="fas fa-eye" style="font-size: 14px;"></i>
                                    </button>
                                    <button class="btn-sm btn-outline-secondary" onclick="window.print()">
                                        <i class="fas fa-print" style="font-size: 14px;"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5" style="color: #64748b;">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal" id="detailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Transaction Breakdown</h5>
                <button class="btn-close" onclick="closeDetailsModal()">×</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <h6 id="modalStudentName" style="font-weight: 700; margin-bottom: 5px; color: #2563eb;"></h6>
                    <small id="modalCourseYear" style="color: #64748b;"></small>
                </div>
                <div>
                    <span class="section-label">Paid in this Transaction</span>
                    <div id="modalFeeDetails" class="paid-box"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDetailsModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Navbar and dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const profileDropdownToggle = document.querySelector('.profile-dropdown-toggle');
    const profileDropdownMenu = document.querySelector('.profile-dropdown-menu');

    if (profileDropdownToggle) {
        profileDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdownMenu.classList.toggle('active');
        });
    }

    // Semester dropdown functionality
    const semesterDropdownToggle = document.querySelector('.semester-dropdown-toggle');
    const semesterDropdownMenu = document.getElementById('semesterDropdown');
    const semesterDisplay = document.getElementById('semesterDisplay');
    const semesterLinks = semesterDropdownMenu.querySelectorAll('a');

    if (semesterDropdownToggle) {
        semesterDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            semesterDropdownMenu.classList.toggle('active');
        });
    }

    semesterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            semesterDisplay.textContent = this.textContent;
            semesterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            semesterDropdownMenu.classList.remove('active');
        });
    });

    semesterLinks[0].classList.add('active');

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown-toggle')) {
            profileDropdownMenu.classList.remove('active');
        }
        if (!e.target.closest('.semester-dropdown-toggle')) {
            semesterDropdownMenu.classList.remove('active');
        }
    });

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    
    const currentTheme = localStorage.getItem('theme') || 'dark-mode';
    html.classList.add(currentTheme);
    updateThemeIcon(currentTheme);

    function updateThemeIcon(theme) {
        themeToggle.textContent = theme === 'dark-mode' ? '🌙' : '☀️';
    }

    themeToggle.addEventListener('click', function() {
        const isDarkMode = html.classList.contains('dark-mode');
        
        if (isDarkMode) {
            html.classList.remove('dark-mode');
            html.classList.add('light-mode');
            localStorage.setItem('theme', 'light-mode');
            updateThemeIcon('light-mode');
        } else {
            html.classList.remove('light-mode');
            html.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark-mode');
            updateThemeIcon('dark-mode');
        }
    });

    // Menu toggle functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('closed');
            if (sidebar.classList.contains('closed')) {
                document.querySelector('.main-content').style.marginLeft = '0';
                document.querySelector('.navbar').style.left = '0';
            } else {
                document.querySelector('.main-content').style.marginLeft = '240px';
                document.querySelector('.navbar').style.left = '240px';
            }
        });
    }
});

function viewDetails(name, course, paid) {
    document.getElementById('modalStudentName').innerText = name;
    document.getElementById('modalCourseYear').innerText = course;
    document.getElementById('modalFeeDetails').innerHTML = paid || '<span style="color: #64748b;">No breakdown data available.</span>';
    
    document.getElementById('detailsModal').classList.add('active');
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.remove('active');
}
</script>
</body>
</html>