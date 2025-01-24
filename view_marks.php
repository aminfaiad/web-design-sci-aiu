<?php
session_start();
require 'database.php'; // Include database connection file

// Check if user is logged in and their role is lecturer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'lecturers') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

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

// Fetch student marks
$studentMarks = [];
if (!empty($_GET['course_id'])) {
    $selected_course = $_GET['course_id'];
    $query = "SELECT s.student_id, s.fullname, sm.coursework_marks, sm.final_exam_marks,
                     (sm.coursework_marks + sm.final_exam_marks) AS final_marks,
                     CASE 
                        WHEN (sm.coursework_marks + sm.final_exam_marks) >= 90 THEN 'A'
                        WHEN (sm.coursework_marks + sm.final_exam_marks) >= 80 THEN 'B'
                        WHEN (sm.coursework_marks + sm.final_exam_marks) >= 70 THEN 'C'
                        WHEN (sm.coursework_marks + sm.final_exam_marks) >= 60 THEN 'D'
                        ELSE 'F'
                     END AS grade
              FROM student_marks sm
              INNER JOIN students s ON sm.student_id = s.student_id
              WHERE sm.course_id = ? AND sm.lecturer_id = ?";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("is", $selected_course, $lecturer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $studentMarks[] = $row;
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
    <title>View Marks</title>
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
        select, button {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        select {
            width: 250px;
        }
        button {
            background-color: rgb(0, 23, 229);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: rgb(0, 23, 180);
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
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<?php include 'navbar_lecturer.php'; ?>

<div class="container">
    <h1>View Student Marks</h1>

    <form method="GET" action="">
        <select name="course_id" onchange="this.form.submit()">
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $course) : ?>
                <option value="<?= htmlspecialchars($course['course_id']) ?>">
                    <?= htmlspecialchars($course['course_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($studentMarks)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Coursework Marks</th>
                    <th>Final Exam Marks</th>
                    <th>Final Marks</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($studentMarks as $mark) : ?>
                    <tr>
                        <td><?= htmlspecialchars($mark['student_id']) ?></td>
                        <td><?= htmlspecialchars($mark['fullname']) ?></td>
                        <td><?= htmlspecialchars($mark['coursework_marks']) ?></td>
                        <td><?= htmlspecialchars($mark['final_exam_marks']) ?></td>
                        <td><?= htmlspecialchars($mark['final_marks']) ?></td>
                        <td><?= htmlspecialchars($mark['grade']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No student marks found.</p>
    <?php endif; ?>
</div>

</body>
</html>
