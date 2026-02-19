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
        .sidebar-transaction {
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

        .sidebar-transaction.closed {
            transform: translateX(-240px);
        }

        .sidebar-header-transaction {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container-transaction {
            width: 70px;
            height: 70px;
            background: transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-container-transaction i {
            font-size: 20px;
            color: #0b1f33;
        }

        .logo-image-transaction {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .school-name-transaction {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .school-name-transaction h2 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            color: #fff;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        .school-name-transaction h3 {
            font-size: 10px;
            margin: 5px 0 0 0;
            color: #9fb3c8;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .sidebar-transaction h3.menu-title-transaction {
            font-size: 12px;
            margin: 20px 0 15px;
            color: #9fb3c8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            padding-left: 5px;
        }

        .sidebar-menu-transaction {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu-transaction li {
            margin-bottom: 5px;
        }

        .sidebar-menu-transaction a {
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

        .sidebar-menu-transaction a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            color: #9fb3c8;
            transition: color 0.2s ease;
        }

        .sidebar-menu-transaction a.active-transaction,
        .sidebar-menu-transaction a:hover {
            background: #f59e0b;
            color: #000;
        }

        .sidebar-menu-transaction a.active-transaction i,
        .sidebar-menu-transaction a:hover i {
            color: #000;
        }

        /* Scrollbar styling for sidebar */
        .sidebar-transaction::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-transaction::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .sidebar-transaction::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar-transaction::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* NAVBAR STYLES - WITH UNIQUE CLASSES FOR TRANSACTIONS PAGE */
        :root {
            --bg-primary-transaction: #0b1f33;
            --bg-secondary-transaction: #fff;
            --text-primary-transaction: #fff;
            --text-secondary-transaction: #0b1f33;
            --text-muted-transaction: #9fb3c8;
            --accent-transaction: #f59e0b;
            --border-color-transaction: rgba(255, 255, 255, 0.1);
            --card-bg-transaction: #f0f0f0;
        }

        body.dark-mode-transaction {
            --bg-primary-transaction: #0b1f33;
            --bg-secondary-transaction: #fff;
            --text-primary-transaction: #fff;
            --text-secondary-transaction: #0b1f33;
            --text-muted-transaction: #9fb3c8;
            --accent-transaction: #f59e0b;
            --border-color-transaction: rgba(255, 255, 255, 0.1);
            --card-bg-transaction: #f0f0f0;
        }

        body.light-mode-transaction {
            --bg-primary-transaction: #f5f5f5;
            --bg-secondary-transaction: #fff;
            --text-primary-transaction: #0b1f33;
            --text-secondary-transaction: #fff;
            --text-muted-transaction: #6b7280;
            --accent-transaction: #f59e0b;
            --border-color-transaction: rgba(0, 0, 0, 0.1);
            --card-bg-transaction: #e5e7eb;
        }

        .navbar-transaction {
            height: 65px;
            background: var(--bg-primary-transaction);
            color: var(--text-primary-transaction);
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

        .navbar-left-transaction {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle-transaction {
            font-size: 24px;
            cursor: pointer;
            color: var(--text-primary-transaction);
            transition: color 0.2s ease;
        }

        .menu-toggle-transaction:hover {
            color: var(--accent-transaction);
        }

        .navbar-title-transaction {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .navbar-title-transaction h1 {
            font-size: 16px;
            font-weight: 700;   
            margin: 0;
            color: var(--text-primary-transaction);
        }

        .navbar-subtitle-transaction {
            font-size: 12px;
            color: var(--text-muted-transaction);
            font-weight: 400;
        }

        .nav-right-transaction {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon-transaction {
            font-size: 18px;
            cursor: pointer;
            color: var(--text-primary-transaction);
            transition: color 0.2s ease;
            width: 20px;
            text-align: center;
        }

        .nav-icon-transaction:hover {
            color: var(--accent-transaction);
        }

        .theme-toggle-transaction {
            font-size: 18px;
            cursor: pointer;
            color: var(--text-primary-transaction);
            transition: color 0.2s ease;
            width: 20px;
            text-align: center;
        }

        .theme-toggle-transaction:hover {
            color: var(--accent-transaction);
        }

        .nav-profile-transaction {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .nav-profile-avatar-transaction {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent-transaction);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-secondary-transaction);
            font-size: 13px;
            flex-shrink: 0;
        }

        .nav-profile-text-transaction {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .nav-profile-name-transaction {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary-transaction);
            margin: 0;
            white-space: nowrap;
        }

        .nav-profile-role-transaction {
            font-size: 11px;
            color: var(--text-muted-transaction);
            margin: 0;
            white-space: nowrap;
        }

        .profile-dropdown-toggle-transaction {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .dropdown-arrow-transaction {
            font-size: 10px;
            color: var(--text-muted-transaction);
            transition: transform 0.2s ease;
            margin-left: 4px;
        }

        .profile-dropdown-toggle-transaction:hover .dropdown-arrow-transaction {
            color: var(--text-primary-transaction);
            transform: translateY(2px);
        }

        .profile-dropdown-menu-transaction {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--bg-secondary-transaction);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 150px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
            z-index: 1000;
        }

        .profile-dropdown-menu-transaction.active-transaction {
            display: block;
        }

        .profile-dropdown-menu-transaction a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary-transaction);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
            border-bottom: 1px solid var(--border-color-transaction);
        }

        .profile-dropdown-menu-transaction a:last-child {
            border-bottom: none;
        }

        .profile-dropdown-menu-transaction a:hover {
            background: var(--accent-transaction);
            color: #fff;
        }

        .semester-dropdown-toggle-transaction {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .semester-dropdown-arrow-transaction {
            font-size: 10px;
            color: var(--text-muted-transaction);
            transition: transform 0.2s ease;
            margin-left: 4px;
        }

        .semester-dropdown-toggle-transaction:hover .semester-dropdown-arrow-transaction {
            color: var(--text-primary-transaction);
            transform: translateY(2px);
        }

        .semester-dropdown-menu-transaction {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--bg-secondary-transaction);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
            z-index: 100;
        }

        .semester-dropdown-menu-transaction.active-transaction {
            display: block;
        }

        .semester-dropdown-menu-transaction a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary-transaction);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
            border-bottom: 1px solid var(--border-color-transaction);
        }

        .semester-dropdown-menu-transaction a:last-child {
            border-bottom: none;
        }

        .semester-dropdown-menu-transaction a:hover {
            background: var(--accent-transaction);
            color: #fff;
        }

        .semester-dropdown-menu-transaction a.active-transaction {
            background: var(--accent-transaction);
            color: #fff;
        }

        /* MAIN CONTENT - Adjusted for navbar */
        .main-content-transaction {
            flex: 1;
            margin-left: 240px;
            margin-top: 65px;
            padding: 30px;
        }

        /* FILTER SECTION */
        .filter-section-transaction {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .input-group-transaction {
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
        }

        .input-group-text-transaction {
            background: transparent;
            border: none;
            padding: 0 12px;
            color: #94a3b8;
        }

        .form-control-transaction, .form-select-transaction {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            color: #1e293b;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control-transaction:focus, .form-select-transaction:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-control-transaction.border-0 {
            border: none;
        }

        .btn-primary-transaction {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary-transaction:hover {
            background: #1d4ed8;
        }

        .btn-light-transaction {
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

        .btn-light-transaction:hover {
            background: #e2e8f0;
        }

        /* CARD AND TABLE STYLES */
        .card-transaction {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
        }

        .table-transaction {
            width: 100%;
            border-collapse: collapse;
        }

        .table-transaction thead th {
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

        .table-transaction tbody td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }

        .table-transaction tbody tr:hover {
            background: #f8fafc;
        }

        .badge-paid-transaction {
            background: #dcfce7;
            color: #166534;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        /* ACTION BUTTONS */
        .btn-sm-transaction {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-info-transaction {
            background: #3b82f6;
            color: white;
        }

        .btn-info-transaction:hover {
            background: #2563eb;
        }

        .btn-outline-secondary-transaction {
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-outline-secondary-transaction:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        /* MODAL STYLES */
        .modal-transaction {
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

        .modal-transaction.active-transaction {
            display: flex;
        }

        .modal-dialog-transaction {
            max-width: 500px;
            width: 90%;
        }

        .modal-content-transaction {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header-transaction {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header-transaction h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-close-transaction {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal-body-transaction {
            padding: 24px;
        }

        .modal-footer-transaction {
            padding: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-secondary-transaction {
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary-transaction:hover {
            background: #e2e8f0;
        }

        .paid-box-transaction {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 16px;
            border-radius: 8px;
        }

        .section-label-transaction {
            font-size: 12px;
            font-weight: 600;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: block;
        }

        /* ROW GRID */
        .row-transaction {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .col-md-5-transaction {
            flex: 1 1 calc(41.666% - 20px);
        }

        .col-md-4-transaction {
            flex: 1 1 calc(33.333% - 20px);
        }

        .col-md-3-transaction {
            flex: 1 1 calc(25% - 20px);
        }

        .d-flex-transaction {
            display: flex;
        }

        .gap-2-transaction {
            gap: 10px;
        }

        .gap-3-transaction {
            gap: 15px;
        }

        .justify-content-between-transaction {
            justify-content: space-between;
        }

        .align-items-center-transaction {
            align-items: center;
        }

        .mb-4-transaction {
            margin-bottom: 20px;
        }

        .mb-0-transaction {
            margin-bottom: 0;
        }

        .fw-bold-transaction {
            font-weight: 700;
        }

        .text-muted-transaction {
            color: #64748b;
        }

        .text-center-transaction {
            text-align: center;
        }

        .text-end-transaction {
            text-align: right;
        }

        .py-5-transaction {
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .shadow-sm-transaction {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .w-100-transaction {
            width: 100%;
        }

        .w-50-transaction {
            width: 50%;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar-transaction {
                transform: translateX(-240px);
            }
            .navbar-transaction {
                left: 0;
            }
            .main-content-transaction {
                margin-left: 0;
            }
            .col-md-5-transaction, .col-md-4-transaction, .col-md-3-transaction {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body class="dark-mode-transaction">

<div class="sidebar-transaction">
    <div class="sidebar-header-transaction">
        <div class="logo-container-transaction">
            <img src="../components/images/hcc.png" alt="Holy Cross College Logo" class="logo-image-transaction">
        </div>
        <div class="school-name-transaction">
            <h2>HOLY CROSS COLLEGE</h2>
            <h3>School Management System</h3>
        </div>
    </div>

    <h3 class="menu-title-transaction">Main Menu</h3>
    <ul class="sidebar-menu-transaction">
       
        <li><a href="../index.php"> 
            <i class="fas fa-money-bill-wave"></i>
            Finance
        </a></li>
        <li><a href="cashiering.php">
            <i class="fas fa-chalkboard-teacher"></i>
            Cashier
        </a></li>
        <li><a href="transactions.php" class="active-transaction">
            <i class="fas fa-receipt"></i>
            Transactions
        </a></li>
    </ul>
</div>

<!-- NAVBAR WITH UNIQUE CLASSES -->
<div class="navbar-transaction">
    <div class="navbar-left-transaction">
        <span id="menuToggleTransaction" class="menu-toggle-transaction">☰</span>
        <div class="navbar-title-transaction">
            <h1>TRANSACTION HISTORY</h1>
            <div class="semester-dropdown-toggle-transaction">
                <span class="navbar-subtitle-transaction" id="semesterDisplayTransaction">Transactions / 2024-2025 1st Semester</span>
                <span class="semester-dropdown-arrow-transaction">▼</span>
                <div class="semester-dropdown-menu-transaction" id="semesterDropdownTransaction">
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

    <div class="nav-right-transaction">
        <span class="nav-icon-transaction">🔔</span>
        <span id="themeToggleTransaction" class="theme-toggle-transaction">🌙</span>
        <div class="profile-dropdown-toggle-transaction">
            <div class="nav-profile-transaction">
                <div class="nav-profile-avatar-transaction">SA</div>
                <div class="nav-profile-text-transaction">
                    <p class="nav-profile-name-transaction">Sample Admin</p>
                    <p class="nav-profile-role-transaction">Admin</p>
                </div>
            </div>
            <span class="dropdown-arrow-transaction">▼</span>
            <div class="profile-dropdown-menu-transaction">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <li><a class="dropdown-item py-2" href="logout.php"><span class="material-icons align-middle me-2" style="font-size: 18px;"></span>Logout</a></li>
            </div>
        </div>
    </div>
</div>

<div class="main-content-transaction">
    <div class="d-flex-transaction justify-content-between-transaction align-items-center-transaction" style="margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Transaction History</h2>
            <small style="color: #64748b; font-size: 14px;">Review and manage student payment records</small>
        </div>
    </div>

    <div class="filter-section-transaction">
        <form action="" method="GET" style="display: flex; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1 1 calc(41.666% - 20px);">
                <div class="input-group-transaction">
                    <span class="input-group-text-transaction"><i class="fas fa-search" style="color: #94a3b8;"></i></span>
                    <input type="text" name="search" class="form-control-transaction border-0" placeholder="Search ID or Name..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div style="flex: 1 1 calc(33.333% - 20px);">
                <select name="course" class="form-select-transaction">
                    <option value="">All Courses/Years</option>
                    <?php while($c = $courses_res->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['course_year']) ?>" <?= ($course_filter == $c['course_year']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['course_year']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="flex: 1 1 calc(25% - 20px); display: flex; gap: 10px;">
                <button type="submit" class="btn-primary-transaction" style="flex: 1;">Apply</button>
                <a href="transactions.php" class="btn-light-transaction" style="flex: 0.5; text-decoration: none;">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-transaction">
        <div style="overflow-x: auto;">
            <table class="table-transaction">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>OR Number</th>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Course/Year</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-center-transaction">Action</th>
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
                                <td><span class="badge-paid-transaction">Paid</span></td>
                                <td class="text-center-transaction">
                                    <button class="btn-sm-transaction btn-info-transaction" style="margin-right: 5px;" 
                                            onclick="viewDetailsTransaction(
                                                '<?= addslashes($row['first_name'] . ' ' . $row['last_name']) ?>', 
                                                '<?= addslashes($row['course_year']) ?>', 
                                                '<?= addslashes($row['fee_breakdown']) ?>'
                                            )">
                                        <i class="fas fa-eye" style="font-size: 14px;"></i>
                                    </button>
                                    <button class="btn-sm-transaction btn-outline-secondary-transaction" onclick="window.print()">
                                        <i class="fas fa-print" style="font-size: 14px;"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center-transaction py-5-transaction" style="color: #64748b;">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal-transaction" id="detailsModalTransaction">
    <div class="modal-dialog-transaction">
        <div class="modal-content-transaction">
            <div class="modal-header-transaction">
                <h5>Transaction Breakdown</h5>
                <button class="btn-close-transaction" onclick="closeDetailsModalTransaction()">×</button>
            </div>
            <div class="modal-body-transaction">
                <div style="margin-bottom: 20px;">
                    <h6 id="modalStudentNameTransaction" style="font-weight: 700; margin-bottom: 5px; color: #2563eb;"></h6>
                    <small id="modalCourseYearTransaction" style="color: #64748b;"></small>
                </div>
                <div>
                    <span class="section-label-transaction">Paid in this Transaction</span>
                    <div id="modalFeeDetailsTransaction" class="paid-box-transaction"></div>
                </div>
            </div>
            <div class="modal-footer-transaction">
                <button type="button" class="btn-secondary-transaction" onclick="closeDetailsModalTransaction()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Navbar and dropdown functionality with unique IDs
document.addEventListener('DOMContentLoaded', function() {
    const profileDropdownToggleTransaction = document.querySelector('.profile-dropdown-toggle-transaction');
    const profileDropdownMenuTransaction = document.querySelector('.profile-dropdown-menu-transaction');

    if (profileDropdownToggleTransaction) {
        profileDropdownToggleTransaction.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdownMenuTransaction.classList.toggle('active-transaction');
        });
    }

    // Semester dropdown functionality
    const semesterDropdownToggleTransaction = document.querySelector('.semester-dropdown-toggle-transaction');
    const semesterDropdownMenuTransaction = document.getElementById('semesterDropdownTransaction');
    const semesterDisplayTransaction = document.getElementById('semesterDisplayTransaction');
    const semesterLinksTransaction = semesterDropdownMenuTransaction.querySelectorAll('a');

    if (semesterDropdownToggleTransaction) {
        semesterDropdownToggleTransaction.addEventListener('click', function(e) {
            e.stopPropagation();
            semesterDropdownMenuTransaction.classList.toggle('active-transaction');
        });
    }

    semesterLinksTransaction.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            semesterDisplayTransaction.textContent = this.textContent;
            semesterLinksTransaction.forEach(l => l.classList.remove('active-transaction'));
            this.classList.add('active-transaction');
            semesterDropdownMenuTransaction.classList.remove('active-transaction');
        });
    });

    semesterLinksTransaction[0].classList.add('active-transaction');

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown-toggle-transaction')) {
            profileDropdownMenuTransaction.classList.remove('active-transaction');
        }
        if (!e.target.closest('.semester-dropdown-toggle-transaction')) {
            semesterDropdownMenuTransaction.classList.remove('active-transaction');
        }
    });

    // Theme toggle functionality
    const themeToggleTransaction = document.getElementById('themeToggleTransaction');
    const htmlTransaction = document.documentElement;
    
    const currentThemeTransaction = localStorage.getItem('theme-transaction') || 'dark-mode-transaction';
    htmlTransaction.classList.add(currentThemeTransaction);
    updateThemeIconTransaction(currentThemeTransaction);

    function updateThemeIconTransaction(theme) {
        themeToggleTransaction.textContent = theme === 'dark-mode-transaction' ? '🌙' : '☀️';
    }

    themeToggleTransaction.addEventListener('click', function() {
        const isDarkModeTransaction = htmlTransaction.classList.contains('dark-mode-transaction');
        
        if (isDarkModeTransaction) {
            htmlTransaction.classList.remove('dark-mode-transaction');
            htmlTransaction.classList.add('light-mode-transaction');
            localStorage.setItem('theme-transaction', 'light-mode-transaction');
            updateThemeIconTransaction('light-mode-transaction');
        } else {
            htmlTransaction.classList.remove('light-mode-transaction');
            htmlTransaction.classList.add('dark-mode-transaction');
            localStorage.setItem('theme-transaction', 'dark-mode-transaction');
            updateThemeIconTransaction('dark-mode-transaction');
        }
    });

    // Menu toggle functionality
    const menuToggleTransaction = document.getElementById('menuToggleTransaction');
    const sidebarTransaction = document.querySelector('.sidebar-transaction');
    
    if (menuToggleTransaction) {
        menuToggleTransaction.addEventListener('click', function() {
            sidebarTransaction.classList.toggle('closed');
            if (sidebarTransaction.classList.contains('closed')) {
                document.querySelector('.main-content-transaction').style.marginLeft = '0';
                document.querySelector('.navbar-transaction').style.left = '0';
            } else {
                document.querySelector('.main-content-transaction').style.marginLeft = '240px';
                document.querySelector('.navbar-transaction').style.left = '240px';
            }
        });
    }
});

function viewDetailsTransaction(name, course, paid) {
    document.getElementById('modalStudentNameTransaction').innerText = name;
    document.getElementById('modalCourseYearTransaction').innerText = course;
    document.getElementById('modalFeeDetailsTransaction').innerHTML = paid || '<span style="color: #64748b;">No breakdown data available.</span>';
    
    document.getElementById('detailsModalTransaction').classList.add('active-transaction');
}

function closeDetailsModalTransaction() {
    document.getElementById('detailsModalTransaction').classList.remove('active-transaction');
}
</script>
</body>
</html>