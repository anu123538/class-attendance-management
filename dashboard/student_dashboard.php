<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student = null;
$attendance = [];
$attendance_percentage = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];

    // Fetch student details
    $sql = "SELECT s.student_id, s.student_name, s.email_address, s.phone, 
               f.faculty_name, d.department_name, c.course_name, s.img_path
        FROM student s
        LEFT JOIN faculty f ON s.faculty_id = f.faculty_id
        LEFT JOIN department d ON s.department_id = d.department_id
        LEFT JOIN course c ON s.course_id = c.course_id
        WHERE s.student_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL prepare error: " . $conn->error);
    }
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $student_result = $stmt->get_result();

    // Fetch attendance details
    $attendance_sql = "SELECT * FROM attendance WHERE student_id = ?";
    $attendance_stmt = $conn->prepare($attendance_sql);
    if (!$attendance_stmt) {
        die("SQL prepare error: " . $conn->error);
    }
    $attendance_stmt->bind_param("s", $student_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $attendance = $attendance_result->fetch_assoc(); // Fetch the attendance row

        // Calculate attendance percentage
        $total_weeks = 10;
        $present_count = 0;

        for ($i = 1; $i <= $total_weeks; $i++) {
            $week_column = "week_" . str_pad($i, 2, '0', STR_PAD_LEFT);
            if (isset($attendance[$week_column]) && $attendance[$week_column] === 'Present') {
                $present_count++;
            }
        }
        $attendance_percentage = ($present_count / $total_weeks) * 100;
    } else {
        echo "<script>alert('Student not found.'); window.location.href='../home/home.html';</script>";
        exit();
    }

    $stmt->close();
    $attendance_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <style>
        /* Your existing CSS styling */
        body {
            font-family: Arial, sans-serif;
            background-image: url('../img/set_up_box_bg-1.png');
            background-repeat: no-repeat; 
            background-size: cover; 
            background-position: center;
            padding-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;

        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 800px;
            text-align: left;
        }
        .heading{
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        h2 {
            color: #00796b; 
        }
        .button-container {
            text-align: center;
            
        }
        .button-container a {
            background-color: #0288d1;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button-container a:hover {
            background-color: #0277bd;
        }
        p {
            margin: 10px 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #00796b; 
            color: #ffffff;
        }
        .present {
            background-color: #90ee90; 
        }
        .absent {
            background-color: #f2dede; 
        }
        .attendance-percentage {
            font-size: 20px;
            font-weight: bold;
            color: #1e88e5; 
            margin-top: 20px;
        }
        .links a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #00796b; 
            font-weight: bold;
        }
        .icon:hover {
            transform: scale(1.5);
            transition: 0.8s;
        }
        .head {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            margin: 0px 80px;
        }
        .icon {
            width: 90px;
            margin-right: 50px;
        }
        .student-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-left: 10%;
            margin-right: 10%;
        }
        .student-info img {
            width: 180px;
            height: 250px;
            border-radius: 20px;
            margin-left: 100px;
            object-fit: cover;
            obeject-position:center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="heading">
            <h2>Student Details</h2>
            <div class="button-container">
                <a class="button-container" href="../home/home.html">Back</a>
            </div>
        </div>
        
        <?php if ($student): ?>
            <div class="student-info">
                <div>
                    <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student['student_name'] ?? 'N/A'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email_address'] ?? 'N/A'); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></p>
                    <p><strong>Faculty:</strong> <?php echo htmlspecialchars($student['faculty_name'] ?? 'N/A'); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department_name'] ?? 'N/A'); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?></p>
                    <p class="attendance-percentage">
                        Attendance Percentage: <?php echo number_format($attendance_percentage ?? 0, 2); ?>%
                    </p>
                </div>
                <div>
                    <?php if (!empty($student['img_path'])): ?>
                        <img src="../student/<?php echo htmlspecialchars($student['img_path']); ?>" alt="Student Image">
                    <?php else: ?>
                        <img src="path_to_default_image.jpg" alt="No Image Available">
                    <?php endif; ?>
                </div>

            </div>

            <h3>Attendance for the Last 10 Weeks</h3>
            <table>
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 10; $i >= 1; $i--) {
                        $week = sprintf("Week %02d", $i);
                        $status_column = "week_" . str_pad($i, 2, '0', STR_PAD_LEFT);
                        $status = $attendance[$status_column] ?? 'Absent'; // Default to Absent if not set
                        $status_class = $status === 'Present' ? 'present' : 'absent'; // Apply class based on status
                        
                        echo "<tr><td>" . htmlspecialchars($week) . "</td><td class=\"$status_class\">" . htmlspecialchars($status) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No student details available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

