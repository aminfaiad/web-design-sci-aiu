<?php
session_start();
require_once 'database.php';

// Check if user is logged in and their role is student
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'students') {
    header("Location: login.php");
    exit;
}

// Get the logged-in student ID from the session
$student_username = $_SESSION['username'];
$student_query = "SELECT student_id FROM students WHERE username = '$student_username'";
$student_result = mysqli_query($conn, $student_query);
$student_row = mysqli_fetch_assoc($student_result);
$student_id = $student_row['student_id'];

// Fetch registered courses for the logged-in student
$courses = [];
$query = "
    SELECT c.course_id, c.course_name 
    FROM courses c
    JOIN semester_courses sc ON c.course_id = sc.course_id
    JOIN course_registrations cr ON sc.id = cr.semester_course_id
    WHERE cr.student_id = '$student_id' AND cr.status = 'Approved'
    ORDER BY c.course_name ASC
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[$row['course_id']] = $row['course_name'];
    }
}

// Fetch attendance based on selected course
$selected_course = '';
$attendance_records = [];
$total_classes = 0;
$attended_classes = 0;
$attendance_percentage = 0;

if (isset($_POST['view_attendance']) && isset($_POST['course']) && !empty($_POST['course'])) {
    $selected_course = mysqli_real_escape_string($conn, $_POST['course']);

    // Count total classes scheduled for the course
    $total_classes_query = "
        SELECT COUNT(*) AS total FROM class_schedule 
        WHERE course_id = '$selected_course'
    ";
    $total_classes_result = mysqli_query($conn, $total_classes_query);
    $total_classes_row = mysqli_fetch_assoc($total_classes_result);
    $total_classes = $total_classes_row['total'];

    // Count attended classes for the student in the selected course
    $attended_classes_query = "
        SELECT COUNT(*) AS attended FROM attendance a
        JOIN class_schedule cs ON a.class_id = cs.class_id
        WHERE a.student_id = '$student_id' AND cs.course_id = '$selected_course' AND a.status = 'Present'
    ";
    $attended_classes_result = mysqli_query($conn, $attended_classes_query);
    $attended_classes_row = mysqli_fetch_assoc($attended_classes_result);
    $attended_classes = $attended_classes_row['attended'];

    // Calculate attendance percentage
    if ($total_classes > 0) {
        $attendance_percentage = ($attended_classes / $total_classes) * 100;
    }

    // Query attendance records for the selected course
    $query = "
        SELECT 
            a.attendance_id, 
            cs.class_date, 
            cs.start_time, 
            c.course_name, 
            a.status
        FROM attendance a
        JOIN class_schedule cs ON a.class_id = cs.class_id
        JOIN courses c ON cs.course_id = c.course_id
        WHERE a.student_id = '$student_id' AND c.course_id = '$selected_course'
        ORDER BY cs.class_date ASC
    ";

    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $attendance_records[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <style>

        

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #004aad;
        }

        select, .viewattendance {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #004aad;
            border-radius: 8px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .viewattendance {
            background: linear-gradient(90deg, #004aad, #06beb6);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #004aad;
            color: white;
        }
    </style>
</head>
<body>




<?php include 'navbar.php'; ?>




<div class="container">
    <h1>View Attendance by Course</h1>
    <form method="POST">
        <label for="course">Select Course</label>
        <select name="course" id="course" required>
            <option value="">--Select Course--</option>
            <?php foreach ($courses as $id => $name): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo ($id == $selected_course) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="viewattendance" type="submit" name="view_attendance">View Attendance</button>
    </form>

    <?php if (!empty($attendance_records)): ?>
        <h2>Attendance for <?php echo htmlspecialchars($courses[$selected_course]); ?></h2>
        <p>Total Classes: <?php echo $total_classes; ?></p>
        <p>Classes Attended: <?php echo $attended_classes; ?></p>
        <p>Classes Missed: <?php echo $total_classes - $attended_classes; ?></p>
        <p>Attendance Percentage: <?php echo round($attendance_percentage, 2); ?>%</p>

        <table>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Course</th>
                <th>Status</th>
            </tr>
            <?php foreach ($attendance_records as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['class_date']); ?></td>
                    <td><?php echo htmlspecialchars($record['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($selected_course): ?>
        <p>No attendance records found for <?php echo htmlspecialchars($courses[$selected_course]); ?>.</p>
    <?php endif; ?>
</div>

</body>
</html>
