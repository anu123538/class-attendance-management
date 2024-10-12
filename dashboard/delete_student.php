<?php
session_start();
if (!isset($_SESSION['lecturer_logged_in']) || !$_SESSION['lecturer_logged_in']) {
    header("Location: ../lecturer/lecturer_login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete from attendance table
        $delete_attendance_query = "DELETE FROM Attendance WHERE student_id = ?";
        $stmt = $conn->prepare($delete_attendance_query);
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete from student table
        $delete_student_query = "DELETE FROM Student WHERE student_id = ?";
        $stmt = $conn->prepare($delete_student_query);
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit the transaction
        $conn->commit();
        
        header("Location: lecturer_dashboard.php"); // Redirect back to the dashboard
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        $conn->rollback();
        die("Failed to delete student: " . $e->getMessage());
    }
}

$conn->close();
?>
