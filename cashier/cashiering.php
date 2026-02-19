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

        /* SIDEBAR STYLES - WITH UNIQUE CLASSES FOR CASHIER PAGE */
        .sidebar-cashier {
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

        .sidebar-cashier.closed {
            transform: translateX(-240px);
        }

        .sidebar-header-cashier {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container-cashier {
            width: 70px;
            height: 70px;
            background: transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-container-cashier i {
            font-size: 20px;
            color: #0b1f33;
        }

        .logo-image-cashier {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .school-name-cashier {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .school-name-cashier h2 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            color: #fff;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        .school-name-cashier h3 {
            font-size: 10px;
            margin: 5px 0 0 0;
            color: #9fb3c8;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .sidebar-cashier h3.menu-title-cashier {
            font-size: 12px;
            margin: 20px 0 15px;
            color: #9fb3c8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            padding-left: 5px;
        }

        .sidebar-menu-cashier {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu-cashier li {
            margin-bottom: 5px;
        }

        .sidebar-menu-cashier a {
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

        .sidebar-menu-cashier a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            color: #9fb3c8;
            transition: color 0.2s ease;
        }

        .sidebar-menu-cashier a.active-cashier,
        .sidebar-menu-cashier a:hover {
            background: #f59e0b;
            color: #000;
        }

        .sidebar-menu-cashier a.active-cashier i,
        .sidebar-menu-cashier a:hover i {
            color: #000;
        }

        /* Scrollbar styling for sidebar */
        .sidebar-cashier::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-cashier::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .sidebar-cashier::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar-cashier::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* NAVBAR STYLES - WITH UNIQUE CLASSES FOR CASHIER PAGE */
        :root {
            --bg-primary-cashier: #0b1f33;
            --bg-secondary-cashier: #fff;
            --text-primary-cashier: #fff;
            --text-secondary-cashier: #0b1f33;
            --text-muted-cashier: #9fb3c8;
            --accent-cashier: #f59e0b;
            --border-color-cashier: rgba(255, 255, 255, 0.1);
            --card-bg-cashier: #f0f0f0;
        }

        body.dark-mode-cashier {
            --bg-primary-cashier: #0b1f33;
            --bg-secondary-cashier: #fff;
            --text-primary-cashier: #fff;
            --text-secondary-cashier: #0b1f33;
            --text-muted-cashier: #9fb3c8;
            --accent-cashier: #f59e0b;
            --border-color-cashier: rgba(255, 255, 255, 0.1);
            --card-bg-cashier: #f0f0f0;
        }

        body.light-mode-cashier {
            --bg-primary-cashier: #f5f5f5;
            --bg-secondary-cashier: #fff;
            --text-primary-cashier: #0b1f33;
            --text-secondary-cashier: #fff;
            --text-muted-cashier: #6b7280;
            --accent-cashier: #f59e0b;
            --border-color-cashier: rgba(0, 0, 0, 0.1);
            --card-bg-cashier: #e5e7eb;
        }

        .navbar-cashier {
            height: 65px;
            background: var(--bg-primary-cashier);
            color: var(--text-primary-cashier);
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

        .navbar-left-cashier {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle-cashier {
            font-size: 24px;
            cursor: pointer;
            color: var(--text-primary-cashier);
            transition: color 0.2s ease;
        }

        .menu-toggle-cashier:hover {
            color: var(--accent-cashier);
        }

        .navbar-title-cashier {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .navbar-title-cashier h1 {
            font-size: 16px;
            font-weight: 700;   
            margin: 0;
            color: var(--text-primary-cashier);
        }

        .navbar-subtitle-cashier {
            font-size: 12px;
            color: var(--text-muted-cashier);
            font-weight: 400;
        }

        .nav-right-cashier {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon-cashier {
            font-size: 18px;
            cursor: pointer;
            color: var(--text-primary-cashier);
            transition: color 0.2s ease;
            width: 20px;
            text-align: center;
        }

        .nav-icon-cashier:hover {
            color: var(--accent-cashier);
        }

        .theme-toggle-cashier {
            font-size: 18px;
            cursor: pointer;
            color: var(--text-primary-cashier);
            transition: color 0.2s ease;
            width: 20px;
            text-align: center;
        }

        .theme-toggle-cashier:hover {
            color: var(--accent-cashier);
        }

        .nav-profile-cashier {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .nav-profile-avatar-cashier {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent-cashier);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-secondary-cashier);
            font-size: 13px;
            flex-shrink: 0;
        }

        .nav-profile-text-cashier {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .nav-profile-name-cashier {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary-cashier);
            margin: 0;
            white-space: nowrap;
        }

        .nav-profile-role-cashier {
            font-size: 11px;
            color: var(--text-muted-cashier);
            margin: 0;
            white-space: nowrap;
        }

        .profile-dropdown-toggle-cashier {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .dropdown-arrow-cashier {
            font-size: 10px;
            color: var(--text-muted-cashier);
            transition: transform 0.2s ease;
            margin-left: 4px;
        }

        .profile-dropdown-toggle-cashier:hover .dropdown-arrow-cashier {
            color: var(--text-primary-cashier);
            transform: translateY(2px);
        }

        .profile-dropdown-menu-cashier {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--bg-secondary-cashier);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 150px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
            z-index: 1000;
        }

        .profile-dropdown-menu-cashier.active-cashier {
            display: block;
        }

        .profile-dropdown-menu-cashier a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary-cashier);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
            border-bottom: 1px solid var(--border-color-cashier);
        }

        .profile-dropdown-menu-cashier a:last-child {
            border-bottom: none;
        }

        .profile-dropdown-menu-cashier a:hover {
            background: var(--accent-cashier);
            color: #fff;
        }

        .semester-dropdown-toggle-cashier {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .semester-dropdown-arrow-cashier {
            font-size: 10px;
            color: var(--text-muted-cashier);
            transition: transform 0.2s ease;
            margin-left: 4px;
        }

        .semester-dropdown-toggle-cashier:hover .semester-dropdown-arrow-cashier {
            color: var(--text-primary-cashier);
            transform: translateY(2px);
        }

        .semester-dropdown-menu-cashier {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--bg-secondary-cashier);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
            z-index: 100;
        }

        .semester-dropdown-menu-cashier.active-cashier {
            display: block;
        }

        .semester-dropdown-menu-cashier a {
            display: block;
            padding: 12px 16px;
            color: var(--text-secondary-cashier);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
            border-bottom: 1px solid var(--border-color-cashier);
        }

        .semester-dropdown-menu-cashier a:last-child {
            border-bottom: none;
        }

        .semester-dropdown-menu-cashier a:hover {
            background: var(--accent-cashier);
            color: #fff;
        }

        .semester-dropdown-menu-cashier a.active-cashier {
            background: var(--accent-cashier);
            color: #fff;
        }

        /* MAIN CONTENT - Adjusted for navbar */
        .main-content-cashier {
            flex: 1;
            margin-left: 240px;
            margin-top: 65px;
            padding: 30px;
        }

        /* CARD STYLES - With unique classes */
        .card-cashier {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 20px;
        }

        .card-header-cashier {
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

        .card-header-cashier i {
            color: #64748b;
        }

        .card-body-cashier {
            padding: 24px;
        }

        /* FORM ELEMENTS - With unique classes */
        .form-label-cashier {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            display: block;
        }

        .form-control-cashier, .form-select-cashier {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            color: #1e293b;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control-cashier:focus, .form-select-cashier:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-control-cashier.bg-light {
            background-color: #f8fafc;
        }

        .input-group-cashier {
            display: flex;
            gap: 8px;
        }

        .btn-outline-primary-cashier {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0 16px;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-outline-primary-cashier:hover {
            background: #f8fafc;
            border-color: #f59e0b;
            color: #f59e0b;
        }

        /* FEE BUTTONS - With unique classes */
        .btn-fee-option-cashier {
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

        .btn-fee-option-cashier:hover {
            background: #f8fafc;
            border-color: #f59e0b;
            color: #f59e0b;
        }

        /* TABLE STYLES - With unique classes */
        .table-cashier {
            width: 100%;
            border-collapse: collapse;
        }

        .table-cashier th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-cashier td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }

        .empty-text-cashier {
            color: #94a3b8;
            font-style: italic;
        }

        .remove-btn-cashier {
            color: #ef4444;
            cursor: pointer;
            font-size: 18px;
            margin-right: 8px;
            transition: color 0.2s;
        }

        .remove-btn-cashier:hover {
            color: #dc2626;
        }

        /* DISPLAY AMOUNT - With unique classes */
        .display-amount-cashier {
            background: linear-gradient(135deg, #5a7fa8 0%, #3d5a7a 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
        }

        .display-amount-cashier label {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            display: block;
            margin-bottom: 5px;
        }

        .display-amount-cashier h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        /* BUTTONS - With unique classes */
        .btn-portal-primary-cashier {
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

        .btn-portal-primary-cashier:hover:not(:disabled) {
            background: #1d4ed8;
        }

        .btn-portal-primary-cashier:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-link-cashier {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-link-cashier:hover {
            color: #475569;
        }

        /* MODAL STYLES - With unique classes */
        .modal-cashier {
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

        .modal-cashier.active-cashier {
            display: flex;
        }

        .modal-content-cashier {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
        }

        .modal-header-cashier {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header-cashier h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-close-cashier {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal-body-cashier {
            padding: 24px;
        }

        .modal-footer-cashier {
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-secondary-cashier {
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary-cashier {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
        }

        /* SPINNER - With unique classes */
        .spinner-border-sm-cashier {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin-cashier 1s linear infinite;
        }

        .loading-cashier .spinner-border-sm-cashier {
            display: inline-block;
        }

        .loading-cashier .search-icon-cashier {
            display: none;
        }

        @keyframes spin-cashier {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* RESULTS DROPDOWN - With unique classes */
        #results_dropdown_cashier {
            display: none;
            margin-top: 15px;
        }

        /* ROW GRID - With unique classes */
        .row-cashier {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
        }

        .col-lg-7-cashier {
            grid-column: span 7;
        }

        .col-lg-5-cashier {
            grid-column: span 5;
        }

        .col-md-5-cashier {
            grid-column: span 5;
        }

        .col-md-7-cashier {
            grid-column: span 7;
        }

        .col-4-cashier {
            grid-column: span 4;
        }

        .col-8-cashier {
            grid-column: span 8;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar-cashier {
                transform: translateX(-240px);
            }
            .navbar-cashier {
                left: 0;
            }
            .main-content-cashier {
                margin-left: 0;
            }
            .col-lg-7-cashier, .col-lg-5-cashier, .col-md-5-cashier, .col-md-7-cashier, .col-4-cashier, .col-8-cashier {
                grid-column: span 12;
            }
        }

        /* PRINT STYLES */
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-print-cashier, #receipt-print-cashier * {
                visibility: visible;
            }
            #receipt-print-cashier {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .modal-footer-cashier, .btn-close-cashier, .modal-header-cashier {
                display: none !important;
            }
        }
    </style>
</head>
<body class="dark-mode-cashier">

<div class="sidebar-cashier">
    <div class="sidebar-header-cashier">
        <div class="logo-container-cashier">
            <img src="../components/images/hcc.png" alt="Holy Cross College Logo" class="logo-image-cashier">
        </div>
        <div class="school-name-cashier">
            <h2>HOLY CROSS COLLEGE</h2>
            <h3>School Management System</h3>
        </div>
    </div>

    <h3 class="menu-title-cashier">Main Menu</h3>
    <ul class="sidebar-menu-cashier">
        
        <li><a href="../index.php">
            <i class="fas fa-money-bill-wave"></i>
            Finance
        </a></li>
         <li><a href="cashiering.php" class="active-cashier">
            <i class="fas fa-chalkboard-teacher"></i>
            Cashier
        </a></li>
        <li><a href="transactions.php">
            <i class="fas fa-receipt"></i>
            Transactions
        </a></li>
    </ul>
</div>

<!-- NAVBAR WITH UNIQUE CLASSES -->
<div class="navbar-cashier">
    <div class="navbar-left-cashier">
        <span id="menuToggleCashier" class="menu-toggle-cashier">☰</span>
        <div class="navbar-title-cashier">
            <h1>CASHIERING TERMINAL</h1>
            <div class="semester-dropdown-toggle-cashier">
                <span class="navbar-subtitle-cashier" id="semesterDisplayCashier">Cashier / 2024-2025 1st Semester</span>
                <span class="semester-dropdown-arrow-cashier">▼</span>
                <div class="semester-dropdown-menu-cashier" id="semesterDropdownCashier">
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

    <div class="nav-right-cashier">
        <span class="nav-icon-cashier">🔔</span>
        <span id="themeToggleCashier" class="theme-toggle-cashier">🌙</span>
        <div class="profile-dropdown-toggle-cashier">
            <div class="nav-profile-cashier">
                <div class="nav-profile-avatar-cashier">SA</div>
                <div class="nav-profile-text-cashier">
                    <p class="nav-profile-name-cashier">Sample Admin</p>
                    <p class="nav-profile-role-cashier">Admin</p>
                </div>
            </div>
            <span class="dropdown-arrow-cashier">▼</span>
            <div class="profile-dropdown-menu-cashier">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                 <li><a class="dropdown-item py-2" href="logout.php"><span class="material-icons align-middle me-2" style="font-size: 18px;"></span>Logout</a></li>
            </div>
        </div>
    </div>
</div>

<div class="main-content-cashier">
    <div class="row-cashier">
        <div class="col-lg-7-cashier">
            <div class="card-cashier">
                <div class="card-header-cashier">
                    <i class="fas fa-search"></i>
                    Student Search
                </div>
                <div class="card-body-cashier">
                    <div class="row-cashier" style="gap: 15px;">
                        <div class="col-md-5-cashier">
                            <label class="form-label-cashier">Student Number / Name</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="stu_no_cashier" class="form-control-cashier" placeholder="Search ID or Name...">
                                <button class="btn-outline-primary-cashier" id="search_btn_cashier" onclick="performSearchCashier()" style="padding: 0 20px;">
                                    <i class="fas fa-search search-icon-cashier"></i>
                                    <span class="spinner-border-sm-cashier"></span>
                                </button>
                            </div>
                            <div id="results_dropdown_cashier">
                                <label class="form-label-cashier" style="color: #f59e0b; margin-top: 10px;">Multiple Matches Found:</label>
                                <select id="student_select_cashier" class="form-select-cashier" onchange="selectStudentFromListCashier()">
                                    <option value="">-- Select Student --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7-cashier">
                            <div class="row-cashier" style="gap: 10px;">
                                <div class="col-8-cashier">
                                    <label class="form-label-cashier">Full Name</label>
                                    <input type="text" id="stu_name_cashier" class="form-control-cashier bg-light" readonly placeholder="Student Name...">
                                </div>
                                <div class="col-4-cashier">
                                    <label class="form-label-cashier">Course/Year</label>
                                    <input type="text" id="stu_course_cashier" class="form-control-cashier bg-light" readonly placeholder="Course...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-cashier">
                <div class="card-header-cashier">
                    <i class="fas fa-list-alt"></i>
                    Select Fees
                </div>
                <div class="card-body-cashier">
                    <div class="row-cashier" style="gap: 10px;">
                        <div class="col-4-cashier"><button class="btn-fee-option-cashier" onclick="addFeeCashier('Tuition Fee')">Tuition Fee</button></div>
                        <div class="col-4-cashier"><button class="btn-fee-option-cashier" onclick="addFeeCashier('Misc Fee')">Misc. Fee</button></div>
                        <div class="col-4-cashier"><button class="btn-fee-option-cashier" onclick="addFeeCashier('Lab Fee')">Lab Fee</button></div>
                        <div class="col-4-cashier"><button class="btn-fee-option-cashier" onclick="addFeeCashier('Uniform')">Uniform</button></div>
                        <div class="col-4-cashier"><button class="btn-fee-option-cashier" onclick="addFeeCashier('ID Request')">ID Request</button></div>
                        <div class="col-4-cashier"><button class="btn-fee-option-cashier" onclick="addFeeCashier('Others')">Others</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5-cashier">
            <div class="card-cashier" style="height: fit-content;">
                <div class="card-body-cashier">
                    <h6 class="form-label-cashier" style="margin-bottom: 15px;">ASSESSMENT SUMMARY</h6>
                    <div style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
                        <table class="table-cashier">
                            <thead>
                                <tr>
                                    <th>DESCRIPTION</th>
                                    <th class="text-end">BALANCE</th>
                                </tr>
                            </thead>
                            <tbody id="summary_table_cashier">
                                <tr>
                                    <td colspan="2" class="text-center empty-text-cashier" style="padding: 40px;">No items selected.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="display-amount-cashier" style="margin-bottom: 20px;">
                        <label>Total Selected Balance</label>
                        <h2 id="balance_text_cashier">₱ 0.00</h2>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label class="form-label-cashier">Cash Tendered (Partial OK)</label>
                        <input type="number" id="cash_input_cashier" class="form-control-cashier" style="font-size: 1.2rem; font-weight: 600; color: #059669;" disabled>
                    </div>
                    <button id="btn_confirm_cashier" class="btn-portal-primary-cashier" style="margin-bottom: 10px;" disabled onclick="processPaymentCashier()">Confirm & Print Receipt</button>
                    <button class="btn-link-cashier" style="width: 100%;" onclick="location.reload()">Clear Transaction</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal-cashier" id="receiptModalCashier">
    <div class="modal-content-cashier">
        <div class="modal-header-cashier">
            <h5>Payment Confirmed</h5>
            <button class="btn-close-cashier" onclick="closeModalCashier()">×</button>
        </div>
        <div class="modal-body-cashier" style="background: #f8fafc;">
            <div id="receipt-print-cashier" style="font-family: 'Courier New', monospace; background: white; padding: 20px; border: 1px solid #e2e8f0;">
                <div style="text-align:center; border-bottom: 1px dashed #000; padding-bottom:10px;">
                    <strong style="font-size: 1.2rem;">HOLY CROSS COLLEGE</strong><br>
                    <small>Official Payment Receipt</small>
                </div>
                <div style="margin: 10px 0; font-size: 0.85rem;">
                    Date: <span id="r-date-cashier"></span><br>
                    Receipt #: <span id="r-id-cashier"></span><br>
                    Student: <span id="r-name-cashier"></span><br>
                    Course: <span id="r-course-cashier"></span><br>
                    ID: <span id="r-no-cashier"></span>
                </div>
                <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin-bottom: 10px;">
                    <div style="font-size: 0.8rem; font-weight: bold; margin-bottom: 5px;">ITEMS PAID:</div>
                    <div id="r-items-list-cashier" style="font-size: 0.8rem;"></div>
                </div>
                <div style="text-align:right;">
                    <strong>TOTAL PAID: ₱ <span id="r-amount-cashier"></span></strong>
                </div>
                <div style="text-align:center; margin-top:20px; font-size: 0.8rem;">Thank you for your payment!</div>
            </div>
        </div>
        <div class="modal-footer-cashier">
            <button class="btn-secondary-cashier" onclick="closeModalCashier()">Done</button>
            <button class="btn-primary-cashier" onclick="window.print()">Print Now</button>
        </div>
    </div>
</div>

<script>
let dbFeesCashier = {}; 
let itemsCashier = []; 
let currentStudentNumberCashier = ""; 
let currentStudentIdCashier = "";

// Navbar and dropdown functionality with unique classes
document.addEventListener('DOMContentLoaded', function() {
    const profileDropdownToggleCashier = document.querySelector('.profile-dropdown-toggle-cashier');
    const profileDropdownMenuCashier = document.querySelector('.profile-dropdown-menu-cashier');

    if (profileDropdownToggleCashier) {
        profileDropdownToggleCashier.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdownMenuCashier.classList.toggle('active-cashier');
        });
    }

    // Semester dropdown functionality
    const semesterDropdownToggleCashier = document.querySelector('.semester-dropdown-toggle-cashier');
    const semesterDropdownMenuCashier = document.getElementById('semesterDropdownCashier');
    const semesterDisplayCashier = document.getElementById('semesterDisplayCashier');
    const semesterLinksCashier = semesterDropdownMenuCashier.querySelectorAll('a');

    if (semesterDropdownToggleCashier) {
        semesterDropdownToggleCashier.addEventListener('click', function(e) {
            e.stopPropagation();
            semesterDropdownMenuCashier.classList.toggle('active-cashier');
        });
    }

    semesterLinksCashier.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            semesterDisplayCashier.textContent = this.textContent;
            semesterLinksCashier.forEach(l => l.classList.remove('active-cashier'));
            this.classList.add('active-cashier');
            semesterDropdownMenuCashier.classList.remove('active-cashier');
        });
    });

    semesterLinksCashier[0].classList.add('active-cashier');

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown-toggle-cashier')) {
            profileDropdownMenuCashier.classList.remove('active-cashier');
        }
        if (!e.target.closest('.semester-dropdown-toggle-cashier')) {
            semesterDropdownMenuCashier.classList.remove('active-cashier');
        }
    });

    // Theme toggle functionality
    const themeToggleCashier = document.getElementById('themeToggleCashier');
    const htmlCashier = document.documentElement;
    
    const currentThemeCashier = localStorage.getItem('theme-cashier') || 'dark-mode-cashier';
    htmlCashier.classList.add(currentThemeCashier);
    updateThemeIconCashier(currentThemeCashier);

    function updateThemeIconCashier(theme) {
        themeToggleCashier.textContent = theme === 'dark-mode-cashier' ? '🌙' : '☀️';
    }

    themeToggleCashier.addEventListener('click', function() {
        const isDarkModeCashier = htmlCashier.classList.contains('dark-mode-cashier');
        
        if (isDarkModeCashier) {
            htmlCashier.classList.remove('dark-mode-cashier');
            htmlCashier.classList.add('light-mode-cashier');
            localStorage.setItem('theme-cashier', 'light-mode-cashier');
            updateThemeIconCashier('light-mode-cashier');
        } else {
            htmlCashier.classList.remove('light-mode-cashier');
            htmlCashier.classList.add('dark-mode-cashier');
            localStorage.setItem('theme-cashier', 'dark-mode-cashier');
            updateThemeIconCashier('dark-mode-cashier');
        }
    });

    // Menu toggle functionality
    const menuToggleCashier = document.getElementById('menuToggleCashier');
    const sidebarCashier = document.querySelector('.sidebar-cashier');
    
    if (menuToggleCashier) {
        menuToggleCashier.addEventListener('click', function() {
            sidebarCashier.classList.toggle('closed');
            if (sidebarCashier.classList.contains('closed')) {
                document.querySelector('.main-content-cashier').style.marginLeft = '0';
                document.querySelector('.navbar-cashier').style.left = '0';
            } else {
                document.querySelector('.main-content-cashier').style.marginLeft = '240px';
                document.querySelector('.navbar-cashier').style.left = '240px';
            }
        });
    }
});

function performSearchCashier() {
    const sno = document.getElementById('stu_no_cashier').value;
    const btn = document.getElementById('search_btn_cashier');
    const dropdown = document.getElementById('results_dropdown_cashier');
    const select = document.getElementById('student_select_cashier');
    
    if(!sno) return alert("Please enter a student number or name.");
    btn.classList.add('loading-cashier'); btn.disabled = true;
    dropdown.style.display = 'none'; select.innerHTML = '<option value="">-- Select Student --</option>';

    fetch(`?action=search&student_no=${encodeURIComponent(sno)}`)
    .then(r => r.json())
    .then(data => {
        btn.classList.remove('loading-cashier'); btn.disabled = false;
        if(data.error) { alert("Student not found!"); resetUICashier(); } 
        else if(data.status === 'single') { populateStudentCashier(data.student, data.fees); } 
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

function selectStudentFromListCashier() {
    const select = document.getElementById('student_select_cashier');
    const sid = select.value;
    if(!sid) return;
    const selectedOpt = select.options[select.selectedIndex];
    
    fetch(`?action=get_fees&student_id=${sid}`)
    .then(r => r.json())
    .then(data => {
        populateStudentCashier({
            student_id: sid,
            student_number: selectedOpt.dataset.sno,
            first_name: selectedOpt.dataset.name,
            last_name: "",
            course_year: selectedOpt.dataset.course
        }, data.fees);
    });
}

function populateStudentCashier(student, fees) {
    document.getElementById('stu_name_cashier').value = student.first_name + " " + (student.last_name || "");
    document.getElementById('stu_course_cashier').value = student.course_year || "N/A";
    
    currentStudentNumberCashier = student.student_number;
    currentStudentIdCashier = student.student_id;
    dbFeesCashier = {};
    fees.forEach(f => { dbFeesCashier[f.fee_name] = { amount: parseFloat(f.amount), id: f.fee_id }; });
    updateUICashier();
    document.getElementById('cash_input_cashier').disabled = false;
    document.getElementById('btn_confirm_cashier').disabled = false;
}

function addFeeCashier(name) {
    if(!currentStudentNumberCashier) return alert("Search for a student first.");
    let feeData = dbFeesCashier[name];
    if(!feeData) return alert("Fee not found in database.");
    if(feeData.amount <= 0) return alert("This fee is already fully paid!");
    if(itemsCashier.some(i => i.name === name)) return;
    itemsCashier.push({ name: name, amount: feeData.amount, id: feeData.id });
    updateUICashier();
}

function updateUICashier() {
    const table = document.getElementById('summary_table_cashier');
    let total = 0;
    if(itemsCashier.length === 0) {
        table.innerHTML = '<tr><td colspan="2" class="text-center empty-text-cashier" style="padding: 40px;">No items selected.</td></tr>';
    } else {
        table.innerHTML = itemsCashier.map((item, index) => {
            total += item.amount;
            return `<tr>
                <td><i class="fas fa-times-circle remove-btn-cashier" onclick="removeItemCashier(${index})"></i> ${item.name}</td>
                <td class="text-end">₱ ${item.amount.toLocaleString()}</td>
            </tr>`;
        }).join('');
    }
    document.getElementById('balance_text_cashier').innerText = "₱ " + total.toLocaleString(undefined, {minimumFractionDigits: 2});
}

function removeItemCashier(index) { 
    itemsCashier.splice(index, 1); 
    updateUICashier(); 
}

function processPaymentCashier() {
    const cash = parseFloat(document.getElementById('cash_input_cashier').value);
    if(!cash || cash <= 0) return alert("Please enter the amount paid.");
    
    const body = new FormData();
    body.append('action', 'pay');
    body.append('student_no', currentStudentNumberCashier);
    body.append('amount', cash);
    body.append('fee_ids', JSON.stringify(itemsCashier.map(i => i.id)));
    
    fetch(window.location.href, { method: 'POST', body: body })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('r-date-cashier').innerText = new Date().toLocaleString();
            document.getElementById('r-id-cashier').innerText = data.receipt_no;
            document.getElementById('r-name-cashier').innerText = document.getElementById('stu_name_cashier').value;
            document.getElementById('r-course-cashier').innerText = document.getElementById('stu_course_cashier').value;
            document.getElementById('r-no-cashier').innerText = currentStudentNumberCashier;
            document.getElementById('r-amount-cashier').innerText = cash.toLocaleString(undefined, {minimumFractionDigits: 2});
            
            let breakdownHtml = itemsCashier.map(i => 
                `<div style="display:flex; justify-content:space-between;">
                    <span>${i.name}</span>
                    <span>₱ ${i.amount.toLocaleString()}</span>
                </div>`
            ).join('');
            document.getElementById('r-items-list-cashier').innerHTML = breakdownHtml;

            document.getElementById('receiptModalCashier').classList.add('active-cashier');
        } else alert("Error processing payment.");
    });
}

function closeModalCashier() {
    document.getElementById('receiptModalCashier').classList.remove('active-cashier');
    location.reload();
}

function resetUICashier() {
    document.getElementById('stu_name_cashier').value = ""; 
    document.getElementById('stu_course_cashier').value = "";
    document.getElementById('balance_text_cashier').innerText = "₱ 0.00";
    document.getElementById('summary_table_cashier').innerHTML = '<tr><td colspan="2" class="text-center empty-text-cashier" style="padding: 40px;">No items selected.</td></tr>';
    document.getElementById('cash_input_cashier').disabled = true; 
    document.getElementById('btn_confirm_cashier').disabled = true;
    itemsCashier = [];
}

document.getElementById('stu_no_cashier').addEventListener('keypress', (e) => { 
    if (e.key === 'Enter') performSearchCashier(); 
});
</script>
</body>
</html>