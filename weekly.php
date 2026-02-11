<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Dashboard - Weekly</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        /* MAIN CONTENT AREA */
        .main {
            flex: 1;
            margin-left: 240px;
            transition: margin-left 0.3s ease;
        }

        .main.full {
            margin-left: 0;
        }

        .content {
            padding: 30px;
        }

        /* HEADER SECTION */
        .header-section {
            margin-bottom: 30px;
        }

        .main-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        /* TIME FILTER */
        .time-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        .time-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 10px;
            background: white;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .time-btn.active {
            background: #f59e0b;
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }

        /* TWO COLUMN LAYOUT */
        .dashboard-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* LEFT COLUMN - STATS CARDS */
        .stats-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #5a7fa8 0%, #3d5a7a 100%);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .stat-change {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
        }

        .positive {
            background: rgba(34, 197, 94, 0.9);
            color: #fff;
        }

        .negative {
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
        }

        /* RIGHT COLUMN - CHART */
        .chart-section {
            background: linear-gradient(135deg, #5a7fa8 0%, #3d5a7a 100%);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: white;
        }

        .chart-controls {
            display: flex;
            gap: 12px;
        }

        .chart-controls select {
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            font-weight: 500;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }

        .chart-controls select option {
            background: #3d5a7a;
            color: white;
        }

        .chart-container {
            height: 180px;
            position: relative;
        }

        /* TABLE SECTION */
        .table-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .section-controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 16px 10px 42px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            width: 220px;
            font-size: 14px;
            color: #475569;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .add-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .add-btn:hover {
            background: #1d4ed8;
        }

        .entries-select {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #64748b;
        }

        .entries-select select {
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            color: #475569;
            font-size: 14px;
        }

        /* TABLE STYLES */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        /* STATUS BADGES */
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .paid {
            background: #dcfce7;
            color: #166534;
        }

        .pending {
            background: #fef9c3;
            color: #854d0e;
        }

        .overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        /* VIEW BUTTON */
        .view-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .view-btn:hover {
            background: #2563eb;
        }

        /* DELETE BUTTON */
        .delete-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 8px;
            transition: background 0.2s;
        }

        .delete-btn:hover {
            background: #dc2626;
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #64748b;
        }

        .pagination-info {
            font-size: 14px;
        }

        .pagination-controls {
            display: flex;
            gap: 8px;
        }

        .pagination-btn {
            padding: 8px 16px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            color: #475569;
            font-weight: 500;
        }

        .pagination-btn:hover {
            background: #f8fafc;
        }

        .pagination-btn.active {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        /* CURRENCY STYLE */
        .currency {
            font-weight: 600;
            color: #1e293b;
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

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .close-btn:hover {
            background: #f8fafc;
            color: #64748b;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #475569;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #1e293b;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-footer {
            padding: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .modal-footer .cancel-btn {
            padding: 12px 24px;
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-footer .cancel-btn:hover {
            background: #e2e8f0;
        }

        .modal-footer .save-btn {
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-footer .save-btn:hover {
            background: #1d4ed8;
        }

        /* ACTION BUTTONS CONTAINER */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
    </style>
</head>

<body>

<?php include 'components/sidebar.php'; ?>

<div class="main">
    <?php include 'components/nav.php'; ?>

    <div class="content">
        
        <div class="time-filter">
            <a href="index.php" class="time-btn">Daily</a>
            <a href="weekly.php" class="time-btn active">Weekly</a>
            <a href="monthly.php" class="time-btn">Monthly</a>
            <a href="yearly.php" class="time-btn">Yearly</a> 
        </div>

        <div class="dashboard-container">
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Weekly Total</span>
                        <span class="stat-change positive" id="totalChange">+5.2%</span>
                    </div>
                    <div class="stat-value" id="totalAmount">₱482,150</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Weekly Tuition</span>
                        <span class="stat-change positive" id="tuitionChange">+3.1%</span>
                    </div>
                    <div class="stat-value" id="totalTuition">₱310,200</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Weekly Activities</span>
                        <span class="stat-change positive" id="activitiesChange">+12%</span>
                    </div>
                    <div class="stat-value" id="totalActivities">₱85,450</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Weekly Misc</span>
                        <span class="stat-change negative" id="miscChange">-2.4%</span>
                    </div>
                    <div class="stat-value" id="totalMisc">₱86,500</div>
                </div>
            </div>

            <div class="chart-section">
                <div class="chart-header">
                    <h3>Fees Collection (This Week)</h3>
                    <div class="chart-controls">
                        <select id="classSelect">
                            <option>All Classes</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSCPE">BSCPE</option>
                            <option value="BSCS">BSCS</option>
                            <option value="BSCE">BSCE</option>
                        </select>
                        <select id="periodSelect" style="display: none;">
                            <option>Daily</option>
                            <option selected>Weekly</option>
                            <option>Monthly</option>
                            <option>Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="feesChart"></canvas>
                </div>
            </div>
        </div>

       <div class="table-section">
            <div class="section-header">
                <h3>Weekly Collection List</h3>
                <div class="section-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search Name...">
                    </div>

                    <select id="dateSelect" style="padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <option>Today</option>
                        <option selected>This Week</option>
                        <option>This Month</option>
                        <option>All Time</option>
                    </select>

                    <select id="chartClassSelect" style="padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <option value="">All Classes</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCPE">BSCPE</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSCE">BSCE</option>
                    </select>

                    <select id="statusSelect" style="padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <option value="">All Status</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Overdue">Overdue</option>
                    </select>

                    <button class="add-btn" onclick="openAddStudentModal()">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox"></th>
                        <th>Name</th>
                        <th>ID</th>
                        <th>Class</th>
                        <th>Tuition Fee</th>
                        <th>Activities Fee</th>
                        <th>Miscellaneous</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    <?php
                    include 'backend/db.php';

                    // SQL query to fetch records from the current week (Sunday to Saturday)
                    $sql = "SELECT * FROM students 
                            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) 
                            ORDER BY id DESC";

                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = strtolower($row['payment_status']);
                    ?>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= htmlspecialchars($row['class']) ?></td>
                            <td class="currency">₱<?= number_format($row['tuition_fee'], 2) ?></td>
                            <td class="currency">₱<?= number_format($row['activities_fee'], 2) ?></td>
                            <td class="currency">₱<?= number_format($row['miscellaneous_fee'], 2) ?></td>
                            <td class="currency">₱<?= number_format($row['total_amount'], 2) ?></td>
                            <td>
                                <span class="status <?= $statusClass ?>">
                                    <?= $row['payment_status'] ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="view-btn">View</button>
                                <button class="delete-btn" onclick="deleteStudent(<?= $row['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='10' style='text-align:center; padding: 40px; color: #64748b;'>No records found for this week.</td></tr>";
                    }
                    $conn->close();
                    ?>
                    </tbody>
            </table>

            <div class="pagination">
                <div class="pagination-info">Showing weekly records</div>
                <div class="pagination-controls">
                    <button class="pagination-btn active">1</button>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Student</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addStudentForm">
                <div class="form-group"><label>Student Name</label><input type="text" name="studentName" required></div>
                <div class="form-group"><label>Student ID</label><input type="text" name="studentId" required></div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="studentClass" required>
                        <option value="">Select Class</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCPE">BSCPE</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSCE">BSCE</option>
                    </select>
                </div>
                <div class="form-group"><label>Tuition Fee</label><input type="number" name="tuitionFee" required></div>
                <div class="form-group"><label>Activities Fee</label><input type="number" name="activitiesFee" required></div>
                <div class="form-group"><label>Misc Fee</label><input type="number" name="miscellaneousFee" required></div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="paymentStatus" required>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" onclick="closeModal()">Cancel</button>
            <button class="save-btn">Add Student</button>
        </div>
    </div>
</div>

<script>
function openAddStudentModal() { document.getElementById("addStudentModal").style.display = "flex"; }
function closeModal() { document.getElementById("addStudentModal").style.display = "none"; }

document.querySelector(".save-btn").addEventListener("click", function () {
    const form = document.getElementById("addStudentForm");
    const formData = new FormData(form);
    fetch("backend/add_student.php", { method: "POST", body: formData })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") { location.reload(); } 
        else { alert("Action failed: " + data); }
    });
});

function deleteStudent(id) {
    if (!confirm("Delete this student?")) return;
    fetch("backend/delete_student.php?id=" + id).then(() => location.reload());
}

const weekLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
const ctx = document.getElementById("feesChart").getContext("2d");

let feesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: weekLabels,
        datasets: [{
            label: "Weekly Collection",
            data: Array(7).fill(0), 
            backgroundColor: "#f59e0b",
            borderRadius: 6,
            barThickness: 20
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => "₱" + value.toLocaleString(),
                    color: "#ffffff"
                },
                grid: { color: "rgba(255,255,255,0.2)" }
            },
            x: { ticks: { color: "#ffffff" }, grid: { display: false } }
        }
    }
});

function updateChart() {
    const className = document.getElementById("classSelect").value;
    const period = "Weekly";

    fetch(`backend/chart_data.php?class=${encodeURIComponent(className)}&period=${period}`)
    .then(res => res.json())
    .then(data => {
        const totals = weekLabels.map(day => data[day] ? Number(data[day]) : 0);
        feesChart.data.datasets[0].data = totals;
        feesChart.update();
    })
    .catch(err => console.error("Failed to fetch weekly data:", err));
}

function updateTotals() {
    const className = document.getElementById("classSelect").value;
    const period = document.getElementById("periodSelect").value;

    fetch(`backend/get_totals.php?class=${encodeURIComponent(className)}&period=${period}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById("totalAmount").textContent = "₱" + Number(data.total_amount).toLocaleString();
        document.getElementById("totalTuition").textContent = "₱" + Number(data.total_tuition).toLocaleString();
        document.getElementById("totalActivities").textContent = "₱" + Number(data.total_activities).toLocaleString();
        document.getElementById("totalMisc").textContent = "₱" + Number(data.total_misc).toLocaleString();
    })
    .catch(err => console.error("Failed to fetch totals:", err));
}

// Global Filter for Table
function filterTable() {
    const period = document.getElementById("dateSelect").value;
    const selectedClass = document.getElementById("chartClassSelect").value; // Corrected ID
    const status = document.getElementById("statusSelect").value;
    const tableBody = document.getElementById("tableBody");

    tableBody.innerHTML = "<tr><td colspan='10' style='text-align:center; padding: 20px;'>Filtering...</td></tr>";

    fetch(`backend/filter_students.php?period=${encodeURIComponent(period)}&class=${encodeURIComponent(selectedClass)}&status=${encodeURIComponent(status)}`)
    .then(res => res.text())
    .then(data => {
        tableBody.innerHTML = data;
    })
    .catch(err => {
        console.error("Filter error:", err);
        tableBody.innerHTML = "<tr><td colspan='10' style='text-align:center; color:red;'>Error loading data.</td></tr>";
    });
}

// Listeners for Chart
document.getElementById("classSelect").addEventListener("change", () => {
    updateChart();
    updateTotals();
});

// Listeners for Table
document.getElementById("dateSelect").addEventListener("change", filterTable);
document.getElementById("chartClassSelect").addEventListener("change", filterTable); // Corrected ID
document.getElementById("statusSelect").addEventListener("change", filterTable);

// Initial Load
updateChart();
updateTotals();
</script>

</body>
</html>