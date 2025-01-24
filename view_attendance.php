<?php
session_start();
require 'database.php'; // Include database connection

// Check if user is logged in and their role is lecturer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'lecturers') {
    header("Location: login.php");
    exit;
}

// Fetch lecturer's ID based on their username
$username = $_SESSION['username'];
$query = "SELECT lecturer_id FROM lecturers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($lecturer_id);
$stmt->fetch();
$stmt->close();

// Fetch courses taught by the lecturer
$courses_query = "SELECT DISTINCT c.course_id, c.course_name FROM courses c WHERE c.lecturer_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param('s', $lecturer_id);
$stmt->execute();
$courses_result = $stmt->get_result();

$selected_course = isset($_GET['course_id']) ? $_GET['course_id'] : '';
$attendance_data = [];

if (!empty($selected_course)) {
    $attendance_query = "SELECT s.student_id, s.fullname, 
                        COUNT(a.attendance_id) AS total_classes,
                        SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS total_missed,
                        (COUNT(a.attendance_id) - SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END)) * 100 / COUNT(a.attendance_id) AS attendance_percentage
                        FROM students s
                        INNER JOIN course_registrations cr ON s.student_id = cr.student_id
                        INNER JOIN semester_courses sc ON cr.semester_course_id = sc.id
                        INNER JOIN class_schedule cs ON sc.course_id = cs.course_id
                        LEFT JOIN attendance a ON cs.class_id = a.class_id AND s.student_id = a.student_id
                        WHERE sc.course_id = ?
                        GROUP BY s.student_id";
    
    $stmt = $conn->prepare($attendance_query);
    $stmt->bind_param('i', $selected_course);
    $stmt->execute();
    $attendance_result = $stmt->get_result();
    $attendance_data = $attendance_result->fetch_all(MYSQLI_ASSOC);
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
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
        }
        h1 {
            color: #004aad;
        }
        form, table {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #004aad;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #004aad;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: #002e6e;
        }
    </style>
</head>
<body>
<?php include 'navbar_lecturer.php'; ?>

    <h1>View Attendance</h1>
    <form method="GET">
        <label for="course">Select Course:</label>
        <select name="course_id" id="course" onchange="this.form.submit()">
            <option value="">-- Select Course --</option>
            <?php while ($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php if ($selected_course == $course['course_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if (!empty($attendance_data)) { ?>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Total Classes</th>
                    <th>Total Missed</th>
                    <th>Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_data as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_classes']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_missed']); ?></td>
                        <td><?php echo number_format($row['attendance_percentage'] ?? 0, 2); ?>%</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</body>
</html>
