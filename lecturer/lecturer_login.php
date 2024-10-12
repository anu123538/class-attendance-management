<?php
session_start();
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "class_management"; 

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Prepare and execute the SQL statement
    $sql = "SELECT * FROM lecturer WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Directly compare the password
        if ($input_password === $row['password']) {
            $_SESSION['lecturer_logged_in'] = true;
            header("Location: ../dashboard/lecturer_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid username or password'); window.location.href='lecturer_login.html';</script>";
        }
    } else {
        echo "<script>alert('Invalid username or password'); window.location.href='lecturer_login.html';</script>";
    }
    $stmt->close();
}

$conn->close();
?>
