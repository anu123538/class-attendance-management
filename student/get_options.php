<?php
$servername = "localhost";
$username = "root"; // Update with your MySQL username
$password = ""; // Update with your MySQL password
$dbname = "class_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$type = $_GET['type'];

header('Content-Type: application/json');

if ($type == 'department') {
    // Fetch departments
    $sql = "SELECT department_id, department_name FROM Department";
    $result = $conn->query($sql);
    $departments = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
    }
    echo json_encode($departments);

} elseif ($type == 'faculty') {
    $department_id = $_GET['department_id'];

    // Fetch faculties based on department
    if ($stmt = $conn->prepare("SELECT faculty_id, faculty_name FROM Faculty WHERE department_id = ?")) {
        $stmt->bind_param("i", $department_id); // Use "s" for string if it's VARCHAR
        $stmt->execute();
        $result = $stmt->get_result();
        $faculties = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $faculties[] = $row;
            }
        }
        echo json_encode($faculties);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error fetching faculties']);
    }

} elseif ($type == 'course') {
    $faculty_id = $_GET['faculty_id'];

    // Fetch courses based on faculty
    if ($stmt = $conn->prepare("SELECT course_id, course_name FROM Course WHERE faculty_id = ?")) {
        $stmt->bind_param("i", $faculty_id); // Use "s" for string if it's VARCHAR
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
        }
        echo json_encode($courses);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error fetching courses']);
    }
}

$conn->close();
?>
