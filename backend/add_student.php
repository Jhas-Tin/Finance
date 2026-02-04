<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['studentName'];
    $studentId = $_POST['studentId'];
    $class = $_POST['studentClass'];
    $tuition = $_POST['tuitionFee'];
    $activities = $_POST['activitiesFee'];
    $misc = $_POST['miscellaneousFee'];
    $status = $_POST['paymentStatus'];

    $total = $tuition + $activities + $misc;

    // 🔍 CHECK IF STUDENT ID OR NAME ALREADY EXISTS
    $check = $conn->prepare("SELECT id FROM students WHERE student_id = ? OR student_name = ?");
    $check->bind_param("ss", $studentId, $name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "exists"; // student already in DB
        $check->close();
        $conn->close();
        exit();
    }
    $check->close();

    // ✅ INSERT IF NOT EXISTS
    $stmt = $conn->prepare("INSERT INTO students 
        (student_name, student_id, class, tuition_fee, activities_fee, miscellaneous_fee, total_amount, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssdddss", $name, $studentId, $class, $tuition, $activities, $misc, $total, $status);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
