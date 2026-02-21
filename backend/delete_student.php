<?php
include 'db.php';

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Start transaction for safety
    $conn->begin_transaction();

    try {
        // 1. Delete student balances
        $stmt = $conn->prepare("DELETE FROM student_balances WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();

        // 2. Delete payments
        $stmt = $conn->prepare("DELETE FROM payments WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();

        // 3. Delete the student
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollback();
        echo "error: " . $e->getMessage();
    }

    $conn->close();
} else {
    echo "error: no student ID provided";
}
?>