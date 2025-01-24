<?php
session_start();
require 'database.php'; // Include database connection

// Check if user is logged in and their role is lecturer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'lecturers') {
    header("Location: login.php");
    exit;
}

// Validate class_id in the GET request
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    echo "Invalid class ID.";
    exit;
}

$class_id = $_GET['class_id'];

// Fetch course and student information for the class
$query = "SELECT c.course_name, cs.class_date, cs.start_time, cs.end_time 
          FROM class_schedule cs 
          INNER JOIN courses c ON cs.course_id = c.course_id 
          WHERE cs.class_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
$class_info = $class_result->fetch_assoc();
$stmt->close();

// Fetch students registered for this course along with attendance status
$query = "SELECT s.student_id, s.fullname, 
                 IFNULL(a.status, 'Present') AS status 
          FROM course_registrations cr 
          INNER JOIN students s ON cr.student_id = s.student_id 
          INNER JOIN semester_courses sc ON cr.semester_course_id = sc.id 
          LEFT JOIN attendance a ON a.class_id = ? AND a.student_id = s.student_id 
          WHERE sc.course_id = (SELECT course_id FROM class_schedule WHERE class_id = ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $class_id, $class_id);
$stmt->execute();
$students_result = $stmt->get_result();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_attendance'])) {
    foreach ($_POST['attendance'] as $student_id => $status) {
        $query = "INSERT INTO attendance (class_id, student_id, status) 
                  VALUES (?, ?, ?) 
                  ON DUPLICATE KEY UPDATE status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isss', $class_id, $student_id, $status, $status);
        $stmt->execute();
    }
    $_SESSION['message'] = "Attendance updated successfully!";
    header("Location: attendance_form.php?class_id=$class_id");
    exit;
}

// Display success message
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        h1, p {
            color: #004aad;
        }
        form {
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
            font-size: 16px;
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

    <h1>Attendance for <?php echo htmlspecialchars($class_info['course_name']); ?></h1>
    <p>Date: <?php echo htmlspecialchars($class_info['class_date']); ?></p>
    <p>Time: <?php echo htmlspecialchars($class_info['start_time'] . ' - ' . $class_info['end_time']); ?></p>

    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        <td>
                            <select name="attendance[<?php echo $student['student_id']; ?>]" required>
                                <option value="Present" <?php echo ($student['status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                <option value="Absent" <?php echo ($student['status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                <option value="Late" <?php echo ($student['status'] == 'Late') ? 'selected' : ''; ?>>Late</option>
                                <option value="Excused" <?php echo ($student['status'] == 'Excused') ? 'selected' : ''; ?>>Excused</option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button type="submit" name="submit_attendance">Submit Attendance</button>
    </form>
</body>
</html>
