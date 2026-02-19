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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root { --portal-navy: #2c3e5e; --portal-blue: #4460f1; --portal-gold: #fccb06; --portal-bg: #f5f7fb; }
        body { background-color: var(--portal-bg); font-family: 'Segoe UI', sans-serif; }
        
        .sidebar { height: 100vh; background: var(--portal-navy); color: white; position: fixed; width: 260px; z-index: 1000; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.1); }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); margin: 5px 15px; border-radius: 8px; padding: 12px; display: flex; align-items: center; text-decoration: none; transition: all 0.2s; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.05); color: white; }
        .sidebar .nav-link.active { background: var(--portal-blue); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .sidebar .nav-link.logout-link:hover { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; }
        
        .logo-text { font-size: 1.2rem; font-weight: 800; letter-spacing: 1px; }
        .logo-sub { font-size: 0.65rem; color: var(--portal-gold); text-transform: uppercase; }

        .main-content { margin-left: 260px; padding: 35px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.04); background: #ffffff; }
        .card-header { font-weight: 700; border-bottom: 1px solid #eee; color: var(--portal-navy); padding: 20px; }
        .form-control, .form-select { border: 1px solid #dce1e7; padding: 12px; border-radius: 6px; }
        .btn-portal-primary { background-color: var(--portal-blue); border: none; color: white; font-weight: 600; padding: 12px; text-transform: uppercase; }
        .btn-fee-option { background: white; border: 1px solid #dce1e7; color: var(--portal-navy); height: 55px; font-weight: 600; border-radius: 8px; transition: 0.2s; }
        .btn-fee-option:hover:not(:disabled) { background: #f8f9fa; border-color: var(--portal-blue); }
        .display-amount { background: var(--portal-navy); color: var(--portal-gold); padding: 25px; border-radius: 8px; text-align: right; }
        .display-amount h2 { font-weight: 800; margin: 0; font-family: monospace; }
        .empty-text { color: #adb5bd; font-style: italic; }
        .remove-btn { color: #e74c3c; cursor: pointer; vertical-align: middle; font-size: 18px; }
        .spinner-border-sm { display: none; }
        .loading .spinner-border-sm { display: inline-block; }
        .loading .search-icon { display: none; }
        #results_dropdown { display: none; margin-top: 10px; }

        @media print {
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print { position: absolute; left: 0; top: 0; width: 100%; border: none; }
            .modal-footer, .btn-close, .modal-header { display: none !important; }
        }
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
        <a class="nav-link active" href="cashiering.php"><span class="material-icons me-3">account_balance_wallet</span> Cashiering</a>
        <a class="nav-link" href="transactions.php"><span class="material-icons me-3">receipt_long</span> Transactions</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between mb-4">
        <div><h4 class="fw-bold mb-0">Cashiering Terminal</h4><small class="text-muted">AY 2025-2026</small></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header"><span class="material-icons align-bottom me-2">search</span>Student Search</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Student Number / Name</label>
                            <div class="input-group">
                                <input type="text" id="stu_no" class="form-control" placeholder="Search ID or Name...">
                                <button class="btn btn-outline-primary" id="search_btn" onclick="performSearch()">
                                    <span class="material-icons search-icon" style="font-size: 18px;">search</span>
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                </button>
                            </div>
                            <div id="results_dropdown">
                                <label class="form-label small fw-bold mt-2 text-primary">Multiple Matches Found:</label>
                                <select id="student_select" class="form-select" onchange="selectStudentFromList()">
                                    <option value="">-- Select Student --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="row g-2">
                                <div class="col-8">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" id="stu_name" class="form-control bg-light" readonly placeholder="Student Name...">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold">Course/Year</label>
                                    <input type="text" id="stu_course" class="form-control bg-light" readonly placeholder="Course...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span class="material-icons align-bottom me-2">list_alt</span>Select Fees</div>
                <div class="card-body p-4">
                    <div class="row g-2">
                        <div class="col-4"><button class="btn btn-fee-option w-100" onclick="addFee('Tuition Fee')">Tuition Fee</button></div>
                        <div class="col-4"><button class="btn btn-fee-option w-100" onclick="addFee('Misc Fee')">Misc. Fee</button></div>
                        <div class="col-4"><button class="btn btn-fee-option w-100" onclick="addFee('Lab Fee')">Lab Fee</button></div>
                        <div class="col-4"><button class="btn btn-fee-option w-100" onclick="addFee('Uniform')">Uniform</button></div>
                        <div class="col-4"><button class="btn btn-fee-option w-100" onclick="addFee('ID Request')">ID Request</button></div>
                        <div class="col-4"><button class="btn btn-fee-option w-100" onclick="addFee('Others')">Others</button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h6 class="fw-bold text-muted small mb-3">ASSESSMENT SUMMARY</h6>
                    <div class="flex-grow-1 overflow-auto mb-4">
                        <table class="table table-sm">
                            <thead class="text-muted small"><tr><th>DESCRIPTION</th><th class="text-end">BALANCE</th></tr></thead>
                            <tbody id="summary_table"><tr><td colspan="2" class="text-center py-5 empty-text">No items selected.</td></tr></tbody>
                        </table>
                    </div>
                    <div class="display-amount mb-4">
                        <label>Total Selected Balance</label>
                        <h2 id="balance_text">₱ 0.00</h2>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold text-muted">Cash Tendered (Partial OK)</label>
                        <input type="" id="cash_input" class="form-control form-control-lg text-end fw-bold" style="color: #27ae60; font-size: 1.5rem;" disabled>
                    </div>
                    <button id="btn_confirm" class="btn btn-portal-primary w-100 py-3 opacity-50" disabled onclick="processPayment()">Confirm & Print Receipt</button>
                    <button class="btn btn-link text-muted w-100 mt-2 text-decoration-none small" onclick="location.reload()">Clear Transaction</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold">Payment Confirmed</h5><button type="button" class="btn-close" data-bs-dismiss="modal" onclick="location.reload()"></button></div>
            <div class="modal-body bg-light">
                <div id="receipt-print" style="font-family: 'Courier New', Courier, monospace; width: 100%; padding: 20px; color: #000; background: white; border: 1px solid #ddd; margin: auto;">
                    <div style="text-align:center; border-bottom: 1px dashed #000; padding-bottom:10px;"><strong style="font-size: 1.2rem;">HOLY CROSS COLLEGE</strong><br><small>Official Payment Receipt</small></div>
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
                    <div style="text-align:right;"><strong>TOTAL PAID: ₱ <span id="r-amount"></span></strong></div>
                    <div style="text-align:center; margin-top:20px; font-size: 0.8rem;">Thank you for your payment!</div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" onclick="location.reload()">Done</button><button class="btn btn-primary" onclick="window.print()">Print Now</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let dbFees = {}; let items = []; let currentStudentNumber = ""; let currentStudentId = "";

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
    document.getElementById('btn_confirm').classList.remove('opacity-50');
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
    if(items.length === 0) table.innerHTML = '<tr><td colspan="2" class="text-center py-5 empty-text">No items selected.</td></tr>';
    else {
        table.innerHTML = items.map((item, index) => {
            total += item.amount;
            return `<tr><td><span class="material-icons remove-btn me-2" onclick="removeItem(${index})">cancel</span>${item.name}</td><td class="text-end">₱ ${item.amount.toLocaleString()}</td></tr>`;
        }).join('');
    }
    document.getElementById('balance_text').innerText = "₱ " + total.toLocaleString(undefined, {minimumFractionDigits: 2});
}

function removeItem(index) { items.splice(index, 1); updateUI(); }

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
            
            let breakdownHtml = items.map(i => `<div style="display:flex; justify-content:space-between;"><span>${i.name}</span><span>₱ ${i.amount.toLocaleString()}</span></div>`).join('');
            document.getElementById('r-items-list').innerHTML = breakdownHtml;

            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        } else alert("Error processing payment.");
    });
}

function resetUI() {
    document.getElementById('stu_name').value = ""; 
    document.getElementById('stu_course').value = "";
    document.getElementById('balance_text').innerText = "₱ 0.00";
    document.getElementById('summary_table').innerHTML = '<tr><td colspan="2" class="text-center py-5 empty-text">No items selected.</td></tr>';
    document.getElementById('cash_input').disabled = true; document.getElementById('btn_confirm').disabled = true;
    items = [];
}
document.getElementById('stu_no').addEventListener('keypress', (e) => { if (e.key === 'Enter') performSearch(); });
</script>
</body>
</html>