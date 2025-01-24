<?php
session_start();
require 'database.php'; // Include database connection file

// Check if user is logged in and their role is lecturer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'lecturers') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Ensure database connection is established
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch lecturer_id from the database using the logged-in lecturer's username
$lecturer_id = null;
$lecturerQuery = "SELECT lecturer_id FROM lecturers WHERE username = ?";
$stmt = $conn->prepare($lecturerQuery);

if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($lecturer_id);
    $stmt->fetch();
    $stmt->close();
}

// If no lecturer ID is found, log the user out
if (!$lecturer_id) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch courses associated with the logged-in lecturer
$courses = [];
$courseQuery = "SELECT course_id, course_name FROM courses WHERE lecturer_id = ?";
$stmt = $conn->prepare($courseQuery);

if ($stmt) {
    $stmt->bind_param("s", $lecturer_id);
    $stmt->execute();
    $courseResult = $stmt->get_result();
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
}

// Handle marks update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marks'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $coursework_marks = $_POST['coursework_marks'];
    $final_exam_marks = $_POST['final_exam_marks'];

    $stmt = $conn->prepare("INSERT INTO student_marks (student_id, course_id, lecturer_id, coursework_marks, final_exam_marks) VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE coursework_marks = VALUES(coursework_marks), final_exam_marks = VALUES(final_exam_marks)");

    if ($stmt) {
        $stmt->bind_param("sisdd", $student_id, $course_id, $lecturer_id, $coursework_marks, $final_exam_marks);
        $stmt->execute();
        $stmt->close();
        $success_message = "Marks updated successfully!";
    } else {
        $error_message = "Error updating marks.";
    }
}

// Prepare the query to fetch student data with optional course filter
$studentList = [];
if (!empty($_GET['course_id'])) {
    $selected_course = $_GET['course_id'];
    $query = "SELECT s.student_id, s.fullname FROM course_registrations cr
              INNER JOIN semester_courses sc ON cr.semester_course_id = sc.id
              INNER JOIN students s ON cr.student_id = s.student_id
              WHERE sc.course_id = ? AND cr.status = 'Approved'";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $selected_course);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $studentList[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            margin: 50px auto;
            max-width: 90%;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .container h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }
        .filters select, .filters button {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        .filters button {
            background-color: rgb(0, 23, 229);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: rgb(70, 83, 192);
            color: #fff;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include 'navbar_lecturer.php'; ?>

<div class="container">
    <h1>Enter Student Marks</h1>

    <form method="GET" action="">
        <select name="course_id" onchange="this.form.submit()">
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $course) : ?>
                <option value="<?= htmlspecialchars($course['course_id']) ?>" 
                    <?= isset($_GET['course_id']) && $_GET['course_id'] == $course['course_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['course_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($studentList)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Coursework Marks</th>
                    <th>Final Exam Marks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($studentList as $student) : 
                    // Fetch existing marks
                    $coursework_marks = null;
                    $final_exam_marks = null;
                    $marksQuery = "SELECT coursework_marks, final_exam_marks FROM student_marks WHERE student_id = ? AND course_id = ?";
                    $stmt = $conn->prepare($marksQuery);
                    if ($stmt) {
                        $stmt->bind_param("si", $student['student_id'], $selected_course);
                        $stmt->execute();
                        $stmt->bind_result($coursework_marks, $final_exam_marks);
                        $stmt->fetch();
                        $stmt->close();
                    }
                ?>
                    <tr>
                        <form method="POST" action="">
                            <td><?= htmlspecialchars($student['student_id']) ?>
                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>">
                            </td>
                            <td><?= htmlspecialchars($student['fullname']) ?></td>
                            <td><input type="number" name="coursework_marks" min="0" max="60" value="<?= $coursework_marks ?? '' ?>" required></td>
                            <td><input type="number" name="final_exam_marks" min="0" max="40" value="<?= $final_exam_marks ?? '' ?>" required></td>
                            <td>
                                <input type="hidden" name="course_id" value="<?= htmlspecialchars($selected_course) ?>">
                                <button type="submit" name="update_marks">Update</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No students found.</p>
    <?php endif; ?>
</div>

</body>
</html>
