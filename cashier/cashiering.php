cashiering.php
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

// Handle AJAX Search Request
if (isset($_GET['action']) && $_GET['action'] == 'search') {
    ob_clean();
    header('Content-Type: application/json');

    $sno = $conn->real_escape_string($_GET['student_no']);
    
    // Search for all possible matches
    $res = $conn->query("SELECT * FROM students 
                         WHERE student_number LIKE '%$sno%' 
                         OR first_name LIKE '%$sno%' 
                         OR last_name LIKE '%$sno%'");
    
    $students = [];
    while($row = $res->fetch_assoc()) {
        $students[] = $row;
    }

    if(count($students) > 0) {
        if(count($students) == 1) {
            $sid = $students[0]['student_id'];
            $fees_query = $conn->query("
                SELECT fc.fee_id, fc.fee_name, IFNULL(sb.remaining_balance, fc.default_amount) as amount 
                FROM fee_categories fc
                LEFT JOIN student_balances sb ON fc.fee_id = sb.fee_id AND sb.student_id = $sid");
            
            $fee_list = [];
            while($f = $fees_query->fetch_assoc()) {
                $fee_list[] = ['fee_id' => $f['fee_id'], 'fee_name' => $f['fee_name'], 'amount' => (float)$f['amount']];
            }
            echo json_encode(['status' => 'single', 'student' => $students[0], 'fees' => $fee_list]);
        } else {
            echo json_encode(['status' => 'multiple', 'students' => $students]);
        }
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
    exit;
}

// Handle Fetching Fees for a selected student from dropdown
if (isset($_GET['action']) && $_GET['action'] == 'get_fees') {
    ob_clean();
    header('Content-Type: application/json');
    $sid = (int)$_GET['student_id'];
    $fees_query = $conn->query("
        SELECT fc.fee_id, fc.fee_name, IFNULL(sb.remaining_balance, fc.default_amount) as amount 
        FROM fee_categories fc
        LEFT JOIN student_balances sb ON fc.fee_id = sb.fee_id AND sb.student_id = $sid");
    $fee_list = [];
    while($f = $fees_query->fetch_assoc()) {
        $fee_list[] = ['fee_id' => $f['fee_id'], 'fee_name' => $f['fee_name'], 'amount' => (float)$f['amount']];
    }
    echo json_encode(['fees' => $fee_list]);
    exit;
}

// Handle Payment Submission
if (isset($_POST['action']) && $_POST['action'] == 'pay') {
    ob_clean();
    header('Content-Type: application/json');

    $sno = $conn->real_escape_string($_POST['student_no']);
    $fee_ids = json_decode($_POST['fee_ids']);
    $amount_paid = floatval($_POST['amount']); 
    
    $s_res = $conn->query("SELECT student_id FROM students WHERE student_number = '$sno'");
    $s_row = $s_res->fetch_assoc();
    $sid = $s_row['student_id'];
    
    $sql_pay = "INSERT INTO payments (student_id, amount_paid, payment_date) VALUES ($sid, $amount_paid, NOW())";
    
    if($conn->query($sql_pay)) {
        $receipt_no = $conn->insert_id;
        $remaining_to_distribute = $amount_paid;

        foreach($fee_ids as $fid) {
            if($remaining_to_distribute <= 0) break;
            $check = $conn->query("SELECT id, remaining_balance, total_amount FROM student_balances WHERE student_id = $sid AND fee_id = $fid");
            if($check->num_rows > 0) {
                $row = $check->fetch_assoc();
                $current_bal = $row['remaining_balance'];
                $payment_for_this_fee = min($remaining_to_distribute, $current_bal);
                $conn->query("UPDATE student_balances SET remaining_balance = remaining_balance - $payment_for_this_fee, paid_amount = paid_amount + $payment_for_this_fee WHERE student_id = $sid AND fee_id = $fid");
                $remaining_to_distribute -= $payment_for_this_fee;
            } else {
                $f_info = $conn->query("SELECT default_amount FROM fee_categories WHERE fee_id = $fid")->fetch_assoc();
                $def = (float)$f_info['default_amount'];
                $payment_for_this_fee = min($remaining_to_distribute, $def);
                $new_bal = $def - $payment_for_this_fee;
                $conn->query("INSERT INTO student_balances (student_id, fee_id, total_amount, paid_amount, remaining_balance) VALUES ($sid, $fid, $def, $payment_for_this_fee, $new_bal)");
                $remaining_to_distribute -= $payment_for_this_fee;
            }
        }
        echo json_encode(['status' => 'success', 'receipt_no' => $receipt_no]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCC Portal | Cashiering Terminal</title>
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

        /* CARD STYLES - Finance theme */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 24px;
            font-weight: 600;
            color: #1e293b;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-header i {
            color: #64748b;
        }

        .card-body {
            padding: 24px;
        }

        /* FORM ELEMENTS */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            display: block;
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

        .form-control.bg-light {
            background-color: #f8fafc;
        }

        .input-group {
            display: flex;
            gap: 8px;
        }

        .btn-outline-primary {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0 16px;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-outline-primary:hover {
            background: #f8fafc;
            border-color: #f59e0b;
            color: #f59e0b;
        }

        /* FEE BUTTONS */
        .btn-fee-option {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            color: #475569;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            height: 55px;
        }

        .btn-fee-option:hover {
            background: #f8fafc;
            border-color: #f59e0b;
            color: #f59e0b;
        }

        /* TABLE STYLES */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }

        .empty-text {
            color: #94a3b8;
            font-style: italic;
        }

        .remove-btn {
            color: #ef4444;
            cursor: pointer;
            font-size: 18px;
            margin-right: 8px;
            transition: color 0.2s;
        }

        .remove-btn:hover {
            color: #dc2626;
        }

        /* DISPLAY AMOUNT */
        .display-amount {
            background: linear-gradient(135deg, #5a7fa8 0%, #3d5a7a 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
        }

        .display-amount label {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            display: block;
            margin-bottom: 5px;
        }

        .display-amount h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        /* BUTTONS */
        .btn-portal-primary {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }

        .btn-portal-primary:hover:not(:disabled) {
            background: #1d4ed8;
        }

        .btn-portal-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-link {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-link:hover {
            color: #475569;
        }

        /* MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
        }

        .modal-header {
            padding: 20px 24px;
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
            padding: 20px 24px;
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

        .btn-primary {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
        }

        /* SPINNER */
        .spinner-border-sm {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading .spinner-border-sm {
            display: inline-block;
        }

        .loading .search-icon {
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* RESULTS DROPDOWN */
        #results_dropdown {
            display: none;
            margin-top: 15px;
        }

        /* ROW GRID */
        .row {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
        }

        .col-lg-7 {
            grid-column: span 7;
        }

        .col-lg-5 {
            grid-column: span 5;
        }

        .col-md-5 {
            grid-column: span 5;
        }

        .col-md-7 {
            grid-column: span 7;
        }

        .col-4 {
            grid-column: span 4;
        }

        .col-8 {
            grid-column: span 8;
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
            .col-lg-7, .col-lg-5, .col-md-5, .col-md-7, .col-4, .col-8 {
                grid-column: span 12;
            }
        }

        /* PRINT STYLES */
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-print, #receipt-print * {
                visibility: visible;
            }
            #receipt-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .modal-footer, .btn-close, .modal-header {
                display: none !important;
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
        <li><a href="../index.php" class="active">
            <i class="fas fa-money-bill-wave"></i>
            Finance
        </a></li>
         <li><a href="cashiering.php" class="active">
            <i class="fas fa-money-bill-wave"></i>
            Cashiering
        </a></li>
        <li><a href="transactions.php">
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
            <h1>CASHIERING TERMINAL</h1>
            <div class="semester-dropdown-toggle">
                <span class="navbar-subtitle" id="semesterDisplay">Cashier / 2024-2025 1st Semester</span>
                <span class="semester-dropdown-arrow">▼</span>
                <div class="semester-dropdown-menu" id="semesterDropdown">
                    <a href="#" data-semester="2024-2025-1st">Cashier / 2024-2025 1st Semester</a>
                    <a href="#" data-semester="2024-2025-2nd">Cashier / 2024-2025 2nd Semester</a>
                    <a href="#" data-semester="2023-2024-1st">Cashier / 2023-2024 1st Semester</a>
                    <a href="#" data-semester="2023-2024-2nd">Cashier / 2023-2024 2nd Semester</a>
                    <a href="#" data-semester="2022-2023-1st">Cashier / 2022-2023 1st Semester</a>
                    <a href="#" data-semester="2022-2023-2nd">Cashier / 2022-2023 2nd Semester</a>
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
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-search"></i>
                    Student Search
                </div>
                <div class="card-body">
                    <div class="row" style="gap: 15px;">
                        <div class="col-md-5">
                            <label class="form-label">Student Number / Name</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="stu_no" class="form-control" placeholder="Search ID or Name...">
                                <button class="btn-outline-primary" id="search_btn" onclick="performSearch()" style="padding: 0 20px;">
                                    <i class="fas fa-search search-icon"></i>
                                    <span class="spinner-border-sm"></span>
                                </button>
                            </div>
                            <div id="results_dropdown">
                                <label class="form-label" style="color: #f59e0b; margin-top: 10px;">Multiple Matches Found:</label>
                                <select id="student_select" class="form-select" onchange="selectStudentFromList()">
                                    <option value="">-- Select Student --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="row" style="gap: 10px;">
                                <div class="col-8">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" id="stu_name" class="form-control bg-light" readonly placeholder="Student Name...">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Course/Year</label>
                                    <input type="text" id="stu_course" class="form-control bg-light" readonly placeholder="Course...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list-alt"></i>
                    Select Fees
                </div>
                <div class="card-body">
                    <div class="row" style="gap: 10px;">
                        <div class="col-4"><button class="btn-fee-option" onclick="addFee('Tuition Fee')">Tuition Fee</button></div>
                        <div class="col-4"><button class="btn-fee-option" onclick="addFee('Misc Fee')">Misc. Fee</button></div>
                        <div class="col-4"><button class="btn-fee-option" onclick="addFee('Lab Fee')">Lab Fee</button></div>
                        <div class="col-4"><button class="btn-fee-option" onclick="addFee('Uniform')">Uniform</button></div>
                        <div class="col-4"><button class="btn-fee-option" onclick="addFee('ID Request')">ID Request</button></div>
                        <div class="col-4"><button class="btn-fee-option" onclick="addFee('Others')">Others</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card" style="height: fit-content;">
                <div class="card-body">
                    <h6 class="form-label" style="margin-bottom: 15px;">ASSESSMENT SUMMARY</h6>
                    <div style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>DESCRIPTION</th>
                                    <th class="text-end">BALANCE</th>
                                </tr>
                            </thead>
                            <tbody id="summary_table">
                                <tr>
                                    <td colspan="2" class="text-center empty-text" style="padding: 40px;">No items selected.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="display-amount" style="margin-bottom: 20px;">
                        <label>Total Selected Balance</label>
                        <h2 id="balance_text">₱ 0.00</h2>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Cash Tendered (Partial OK)</label>
                        <input type="number" id="cash_input" class="form-control" style="font-size: 1.2rem; font-weight: 600; color: #059669;" disabled>
                    </div>
                    <button id="btn_confirm" class="btn-portal-primary" style="margin-bottom: 10px;" disabled onclick="processPayment()">Confirm & Print Receipt</button>
                    <button class="btn-link" style="width: 100%;" onclick="location.reload()">Clear Transaction</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal" id="receiptModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Payment Confirmed</h5>
            <button class="btn-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body" style="background: #f8fafc;">
            <div id="receipt-print" style="font-family: 'Courier New', monospace; background: white; padding: 20px; border: 1px solid #e2e8f0;">
                <div style="text-align:center; border-bottom: 1px dashed #000; padding-bottom:10px;">
                    <strong style="font-size: 1.2rem;">HOLY CROSS COLLEGE</strong><br>
                    <small>Official Payment Receipt</small>
                </div>
                <div style="margin: 10px 0; font-size: 0.85rem;">
                    Date: <span id="r-date"></span><br>
                    Receipt #: <span id="r-id"></span><br>
                    Student: <span id="r-name"></span><br>
                    Course: <span id="r-course"></span><br>
                    ID: <span id="r-no"></span>
                </div>
                <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin-bottom: 10px;">
                    <div style="font-size: 0.8rem; font-weight: bold; margin-bottom: 5px;">ITEMS PAID:</div>
                    <div id="r-items-list" style="font-size: 0.8rem;"></div>
                </div>
                <div style="text-align:right;">
                    <strong>TOTAL PAID: ₱ <span id="r-amount"></span></strong>
                </div>
                <div style="text-align:center; margin-top:20px; font-size: 0.8rem;">Thank you for your payment!</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Done</button>
            <button class="btn-primary" onclick="window.print()">Print Now</button>
        </div>
    </div>
</div>

<script>
let dbFees = {}; 
let items = []; 
let currentStudentNumber = ""; 
let currentStudentId = "";

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

function performSearch() {
    const sno = document.getElementById('stu_no').value;
    const btn = document.getElementById('search_btn');
    const dropdown = document.getElementById('results_dropdown');
    const select = document.getElementById('student_select');
    
    if(!sno) return alert("Please enter a student number or name.");
    btn.classList.add('loading'); btn.disabled = true;
    dropdown.style.display = 'none'; select.innerHTML = '<option value="">-- Select Student --</option>';

    fetch(`?action=search&student_no=${encodeURIComponent(sno)}`)
    .then(r => r.json())
    .then(data => {
        btn.classList.remove('loading'); btn.disabled = false;
        if(data.error) { alert("Student not found!"); resetUI(); } 
        else if(data.status === 'single') { populateStudent(data.student, data.fees); } 
        else {
            dropdown.style.display = 'block';
            data.students.forEach(s => {
                let opt = document.createElement('option');
                opt.value = s.student_id;
                opt.dataset.sno = s.student_number;
                opt.dataset.name = s.first_name + " " + s.last_name;
                opt.dataset.course = s.course_year || "N/A";
                opt.textContent = `${s.student_number} - ${s.first_name} ${s.last_name} (${s.course_year || 'N/A'})`;
                select.appendChild(opt);
            });
        }
    });
}

function selectStudentFromList() {
    const select = document.getElementById('student_select');
    const sid = select.value;
    if(!sid) return;
    const selectedOpt = select.options[select.selectedIndex];
    
    fetch(`?action=get_fees&student_id=${sid}`)
    .then(r => r.json())
    .then(data => {
        populateStudent({
            student_id: sid,
            student_number: selectedOpt.dataset.sno,
            first_name: selectedOpt.dataset.name,
            last_name: "",
            course_year: selectedOpt.dataset.course
        }, data.fees);
    });
}

function populateStudent(student, fees) {
    document.getElementById('stu_name').value = student.first_name + " " + (student.last_name || "");
    document.getElementById('stu_course').value = student.course_year || "N/A";
    
    currentStudentNumber = student.student_number;
    currentStudentId = student.student_id;
    dbFees = {};
    fees.forEach(f => { dbFees[f.fee_name] = { amount: parseFloat(f.amount), id: f.fee_id }; });
    updateUI();
    document.getElementById('cash_input').disabled = false;
    document.getElementById('btn_confirm').disabled = false;
}

function addFee(name) {
    if(!currentStudentNumber) return alert("Search for a student first.");
    let feeData = dbFees[name];
    if(!feeData) return alert("Fee not found in database.");
    if(feeData.amount <= 0) return alert("This fee is already fully paid!");
    if(items.some(i => i.name === name)) return;
    items.push({ name: name, amount: feeData.amount, id: feeData.id });
    updateUI();
}

function updateUI() {
    const table = document.getElementById('summary_table');
    let total = 0;
    if(items.length === 0) {
        table.innerHTML = '<tr><td colspan="2" class="text-center empty-text" style="padding: 40px;">No items selected.</td></tr>';
    } else {
        table.innerHTML = items.map((item, index) => {
            total += item.amount;
            return `<tr>
                <td><i class="fas fa-times-circle remove-btn" onclick="removeItem(${index})"></i> ${item.name}</td>
                <td class="text-end">₱ ${item.amount.toLocaleString()}</td>
            </tr>`;
        }).join('');
    }
    document.getElementById('balance_text').innerText = "₱ " + total.toLocaleString(undefined, {minimumFractionDigits: 2});
}

function removeItem(index) { 
    items.splice(index, 1); 
    updateUI(); 
}

function processPayment() {
    const cash = parseFloat(document.getElementById('cash_input').value);
    if(!cash || cash <= 0) return alert("Please enter the amount paid.");
    
    const body = new FormData();
    body.append('action', 'pay');
    body.append('student_no', currentStudentNumber);
    body.append('amount', cash);
    body.append('fee_ids', JSON.stringify(items.map(i => i.id)));
    
    fetch(window.location.href, { method: 'POST', body: body })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('r-date').innerText = new Date().toLocaleString();
            document.getElementById('r-id').innerText = data.receipt_no;
            document.getElementById('r-name').innerText = document.getElementById('stu_name').value;
            document.getElementById('r-course').innerText = document.getElementById('stu_course').value;
            document.getElementById('r-no').innerText = currentStudentNumber;
            document.getElementById('r-amount').innerText = cash.toLocaleString(undefined, {minimumFractionDigits: 2});
            
            let breakdownHtml = items.map(i => 
                `<div style="display:flex; justify-content:space-between;">
                    <span>${i.name}</span>
                    <span>₱ ${i.amount.toLocaleString()}</span>
                </div>`
            ).join('');
            document.getElementById('r-items-list').innerHTML = breakdownHtml;

            document.getElementById('receiptModal').classList.add('active');
        } else alert("Error processing payment.");
    });
}

function closeModal() {
    document.getElementById('receiptModal').classList.remove('active');
    location.reload();
}

function resetUI() {
    document.getElementById('stu_name').value = ""; 
    document.getElementById('stu_course').value = "";
    document.getElementById('balance_text').innerText = "₱ 0.00";
    document.getElementById('summary_table').innerHTML = '<tr><td colspan="2" class="text-center empty-text" style="padding: 40px;">No items selected.</td></tr>';
    document.getElementById('cash_input').disabled = true; 
    document.getElementById('btn_confirm').disabled = true;
    items = [];
}

document.getElementById('stu_no').addEventListener('keypress', (e) => { 
    if (e.key === 'Enter') performSearch(); 
});
</script>
</body>
</html>