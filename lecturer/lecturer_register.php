<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "class_management");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Retrieve form data
$lecturer_name = $_POST['lecturer_name'];
$username = $_POST['username'];
$password = $_POST['password']; // Assuming no hashing is required here

// Default values
$default_module = "Database"; // Set the default module name
$default_faculty_id = 1; // Default faculty ID
$default_course_id = 1;  // Default course ID

// Prepare and bind
$stmt = $mysqli->prepare("INSERT INTO lecturer (lecturer_name, faculty_id, course_id, module, username, password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siisss", $lecturer_name, $default_faculty_id, $default_course_id, $default_module, $username, $password);

// Execute the statement
if ($stmt->execute()) {
    // Show an alert and then redirect
    echo "<script>
            alert('Registration successful! Redirecting to the login page...');
            window.location.href = 'lecturer_login.html';
          </script>";
} else {
    echo "Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$mysqli->close();
?>
