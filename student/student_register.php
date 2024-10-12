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

// Collect form data
$student_id = $_POST['student_id'];
$student_name = $_POST['student_name'];
$email_address = $_POST['email_address'];
$phone = $_POST['phone'];
$department_id = $_POST['department_id'];
$course_id = $_POST['course_id'];
$photo = "";

// Check if student ID or email already exists
$check_sql = "SELECT student_id, email_address FROM student WHERE student_id = ? OR email_address = ?";
$stmt_check = $conn->prepare($check_sql);
if ($stmt_check === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_check->bind_param('ss', $student_id, $email_address);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "<script>alert('Student ID or Email already registered.'); window.location.href='student_register.html';</script>";
    $stmt_check->close();
    $conn->close();
    exit();
}

$stmt_check->close();

// Handle file upload
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $upload_ok = 1;

    // Check if file is an image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $upload_ok = 0;
    }

    // Check file size (e.g., 5MB max)
    if ($_FILES["photo"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $upload_ok = 0;
    }

    // Allow certain file formats (e.g., jpg, png)
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
        $upload_ok = 0;
    }

    if ($upload_ok == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo = $target_file;
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch the corresponding faculty_id based on department_id
$faculty_id = null;
$sql_faculty = "SELECT faculty_id FROM Faculty WHERE department_id = ?";
$stmt_faculty = $conn->prepare($sql_faculty);
if ($stmt_faculty === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_faculty->bind_param('i', $department_id);
$stmt_faculty->execute();
$stmt_faculty->bind_result($faculty_id);
$stmt_faculty->fetch();
$stmt_faculty->close();

if ($faculty_id) {
    // Prepare and bind for Student table insertion
    $sql_student = "INSERT INTO student (student_id, student_name, email_address, phone, department_id, course_id, faculty_id, img_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_student = $conn->prepare($sql_student);
    if ($stmt_student === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters: 's' for string, 'i' for integer
    $stmt_student->bind_param('sssssiis', $student_id, $student_name, $email_address, $phone, $department_id, $course_id, $faculty_id, $photo);

    // Execute statement
    if ($stmt_student->execute()) {
        // Prepare and bind for Attendance table insertion
        $sql_attendance = "INSERT INTO attendance (student_id, course_id) VALUES (?, ?)";
        $stmt_attendance = $conn->prepare($sql_attendance);
        if ($stmt_attendance === false) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters: 's' for string, 'i' for integer
        $stmt_attendance->bind_param('si', $student_id, $course_id);

        // Execute statement
        if ($stmt_attendance->execute()) {
            // Redirect to home.html
            echo "<script>
            alert('Registration successful!');
            window.location.href = '../home/home.html';
          </script>";
            } else {
            echo "Error: " . $stmt->error;
        }

        $stmt_attendance->close();
    } else {
        echo "Error registering student: " . $stmt_student->error;
    }

    $stmt_student->close();
} else {
    echo "Error: Faculty not found for the selected department.";
}

$conn->close();
?>
