<?php
include 'db.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $studentNumber = $_POST['studentNumber'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $courseYear = $_POST['courseYear'];
    $createdAt = date("Y-m-d H:i:s");

    // Check for duplicate student_number
    $check = $conn->prepare("SELECT student_id FROM students WHERE student_number = ?");
    $check->bind_param("s", $studentNumber);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "exists";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO students (student_number, first_name, last_name, course_year, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $studentNumber, $firstName, $lastName, $courseYear, $createdAt);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>