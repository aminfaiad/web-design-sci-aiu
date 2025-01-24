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

// Fetch registered courses for the student
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

// Fetch grades based on selected course
$selected_course = '';
$grades = [];
if (isset($_POST['view_grades']) && isset($_POST['course']) && !empty($_POST['course'])) {
    $selected_course = mysqli_real_escape_string($conn, $_POST['course']);

    // Query grades from student_marks table
    $query = "
        SELECT 
            c.course_name, 
            l.fullname AS lecturer_name, 
            sm.coursework_marks, 
            sm.final_exam_marks 
        FROM student_marks sm
        JOIN courses c ON sm.course_id = c.course_id
        JOIN lecturers l ON sm.lecturer_id = l.lecturer_id
        WHERE sm.student_id = '$student_id' AND sm.course_id = '$selected_course'
    ";
    
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $grades[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades</title>
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

        select, button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #004aad;
            border-radius: 8px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        button {
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
    <h1>View Grades by Course</h1>
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
        <button type="submit" name="view_grades">View Grades</button>
    </form>

    <?php if (!empty($grades)): ?>
        <h2>Grades for <?php echo htmlspecialchars($courses[$selected_course]); ?></h2>
        <table>
            <tr>
                <th>Course</th>
                <th>Lecturer</th>
                <th>Coursework Marks</th>
                <th>Final Exam Marks</th>
                <th>Total Marks</th>
                <th>Grade</th>
            </tr>
            <?php foreach ($grades as $grade): 
                $total_marks = $grade['coursework_marks'] + $grade['final_exam_marks'];
                $grade_letter = '';
                if ($total_marks >= 90) {
                    $grade_letter = 'A';
                } elseif ($total_marks >= 80) {
                    $grade_letter = 'B';
                } elseif ($total_marks >= 70) {
                    $grade_letter = 'C';
                } elseif ($total_marks >= 60) {
                    $grade_letter = 'D';
                } else {
                    $grade_letter = 'F';
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($grade['lecturer_name']); ?></td>
                    <td><?php echo htmlspecialchars($grade['coursework_marks']); ?></td>
                    <td><?php echo htmlspecialchars($grade['final_exam_marks']); ?></td>
                    <td><?php echo $total_marks; ?></td>
                    <td><?php echo $grade_letter; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($selected_course): ?>
        <p>No grades found for <?php echo htmlspecialchars($courses[$selected_course]); ?>.</p>
    <?php endif; ?>
</div>

</body>
</html>
