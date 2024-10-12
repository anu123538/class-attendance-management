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

// Handle form submission for updating attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['attendance'] as $student_id => $weeks) {
        if (isset($_POST['course_id'][$student_id])) {
            $course_id = $_POST['course_id'][$student_id];
            
            // Prepare the update query
            $update_query = "
                UPDATE Attendance
                SET week_01 = ?, week_02 = ?, week_03 = ?, week_04 = ?, week_05 = ?, 
                    week_06 = ?, week_07 = ?, week_08 = ?, week_09 = ?, week_10 = ?
                WHERE student_id = ? AND course_id = ?
            ";
            
            // Prepare the statement
            $stmt = $conn->prepare($update_query);
            if ($stmt === false) {
                die("Failed to prepare statement: " . $conn->error);
            }
            
            // Bind parameters
            $params = array(
                $weeks['Week 1'], $weeks['Week 2'], $weeks['Week 3'], $weeks['Week 4'],
                $weeks['Week 5'], $weeks['Week 6'], $weeks['Week 7'], $weeks['Week 8'],
                $weeks['Week 9'], $weeks['Week 10'], $student_id, $course_id
            );
            $types = str_repeat('s', 10) . 'si'; // Adjust type definition based on your columns' types
            $stmt->bind_param($types, ...$params);
            
            // Execute the statement
            if (!$stmt->execute()) {
                die("Failed to execute statement: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            // Handle missing course_id
            error_log("Missing course_id for student_id: $student_id");
        }
    }
}

// Fetch students and their attendance data
$query = "
    SELECT s.student_id, s.student_name, s.email_address, s.phone, s.img_path, 
           c.course_name, a.week_01, a.week_02, a.week_03, a.week_04, 
           a.week_05, a.week_06, a.week_07, a.week_08, a.week_09, a.week_10,
           a.course_id
    FROM Student s
    LEFT JOIN Attendance a ON s.student_id = a.student_id
    LEFT JOIN Course c ON a.course_id = c.course_id
    ORDER BY s.student_id
";

$result = $conn->query($query);

$students = [];
while ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
    if (!isset($students[$student_id])) {
        $students[$student_id] = [
            'student_name' => $row['student_name'],
            'student_id' => $row['student_id'],
            'email_address' => $row['email_address'],
            'phone' => $row['phone'],
            'img_path' => $row['img_path'],
            'course_name' => $row['course_name'],
            'attendance' => [],
            'course_id' => $row['course_id'] // Added course_id for hidden field
        ];
    }

    $students[$student_id]['attendance'] = [
        'Week 1' => $row['week_01'],
        'Week 2' => $row['week_02'],
        'Week 3' => $row['week_03'],
        'Week 4' => $row['week_04'],
        'Week 5' => $row['week_05'],
        'Week 6' => $row['week_06'],
        'Week 7' => $row['week_07'],
        'Week 8' => $row['week_08'],
        'Week 9' => $row['week_09'],
        'Week 10' => $row['week_10']
    ];
}

$conn->close();

// Calculate attendance percentages
foreach ($students as $student_id => $student) {
    $attendance_counts = array_count_values($student['attendance']);
    $total_weeks = count($student['attendance']);
    $present_count = isset($attendance_counts['Present']) ? $attendance_counts['Present'] : 0;
    $attendance_percentage = ($present_count / $total_weeks) * 100;
    $students[$student_id]['attendance_percentage'] = number_format($attendance_percentage, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        form {
            width: 90%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            animation: fadeIn 1s ease-in;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            animation: slideIn 1s ease-in;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #0000cd;
            color: white;
        }
        td select {
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: transparent;
            border:none;
            padding: 10px 0px;
        }
        td.present {
            background-color: #d4edda;
        }
        td.absent {
            background-color: #f8d7da;
        }
        .divider {
            margin: 20px 0;
            border-top: 1px solid #ddd;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin: 20px;
        }
        .student-card {
    position: relative; /* Ensure the card contains the absolutely positioned button */
    display: flex;
    align-items: center;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    padding: 15px;
    width: 450px;
    transition: transform 0.3s ease;
    margin: 10px;
    overflow: hidden; /* Ensure contents do not overflow the card */
}

.student-card:hover {
    transform: scale(1.05);
}

.delete-form {
    position: absolute;
    top: 10px;
    right: 10px;
    margin: 0; /* Remove default margin */
    padding: 0; /* Remove default padding */
    display: flex; /* Ensure the button is visible within the form */
}

.delete-btn {
    background-color: #dc3545; /* Red color */
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.delete-btn:hover {
    background-color: #c82333;
}

.student-card img {
    border-radius: 20px;
    margin-right: 15px;
    object-fit: cover;
    width: 100px;
    height: 100px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.student-info {
    flex-grow: 1; /* Allow info section to take available space */
}

.student-info h3 {
    margin: 0 0 10px;
}

.student-info p {
    margin: 0;
    margin-bottom: 10px; /* Margin bottom added for spacing */
}

.attendance-percentage {
    font-size: 18px;
    font-weight: bold;
    text-align: left;
    color: blue;
}

        .button-container {
            text-align: center;
            display: flex;
            align-items: center;
            flex-direction: column;
        }
        .button-container button {
            background-color: #1e90ff; /* Blue color */
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .button-container button:hover {
            background-color: #1c86ee;
        }
        .button-container a {
            color: #1e90ff;
            text-decoration: none;
            font-size: 16px;
        }
        .attendance-percentage {
            font-size: 18px;
            font-weight: bold;
            text-align: left;
            color: blue;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <h2>Lecturer Dashboard [Database Module]</h2>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Course</th>
                    <th>Week 1</th>
                    <th>Week 2</th>
                    <th>Week 3</th>
                    <th>Week 4</th>
                    <th>Week 5</th>
                    <th>Week 6</th>
                    <th>Week 7</th>
                    <th>Week 8</th>
                    <th>Week 9</th>
                    <th>Week 10</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['student_name']) ?></td>
                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                    <td><?= htmlspecialchars($student['course_name']) ?></td>
                    <?php foreach ($student['attendance'] as $week => $status): ?>
                    <td class="<?= strtolower($status) ?>">
                        <select name="attendance[<?= htmlspecialchars($student['student_id']) ?>][<?= htmlspecialchars($week) ?>]">
                            <option value="Present" <?= $status === 'Present' ? 'selected' : '' ?>>Present</option>
                            <option value="Absent" <?= $status === 'Absent' ? 'selected' : '' ?>>Absent</option>
                        </select>
                    </td>
                    <?php endforeach; ?>
                    <input type="hidden" name="course_id[<?= htmlspecialchars($student['student_id']) ?>]" value="<?= htmlspecialchars($student['course_id']) ?>">
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="button-container">
            <button type="submit">Update Attendance</button>
            <a href="../lecturer/lecturer_login.html">LOGOUT</a>
        </div>
    </form>

    <div class="divider"></div>

    <h3>Student INFO</h3>
    <div class="card-container">
    <?php foreach ($students as $student): ?>
        <div class="student-card">
    <form action="delete_student.php" method="POST" class="delete-form" style="width:60px">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>">
        <button type="submit" class="delete-btn">Delete</button>
    </form>
    <img src="../student/<?= htmlspecialchars($student['img_path']) ?>" alt="Student Image">
    <div class="student-info">
        <h3><?= htmlspecialchars($student['student_name']) ?></h3>
        <p><strong>ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($student['email_address']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone']) ?></p>
        <p><strong>Module:</strong> <?= htmlspecialchars($student['course_name']) ?></p>
        <p class="attendance-percentage">Attendance Percentage: <?= htmlspecialchars($student['attendance_percentage']) ?>%</p>
    </div>
</div>

    <?php endforeach; ?>
</div>

</body>
</html>
