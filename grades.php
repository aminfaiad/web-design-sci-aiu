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

// Fetch semesters in which the student has registered courses
$semesters = [];
$query = "
    SELECT DISTINCT s.semester_id, s.semester_name 
    FROM semesters s
    JOIN semester_courses sc ON s.semester_id = sc.semester_id
    JOIN course_registrations cr ON sc.id = cr.semester_course_id
    WHERE cr.student_id = '$student_id' AND cr.status = 'Approved'
    ORDER BY s.semester_name ASC
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $semesters[$row['semester_id']] = $row['semester_name'];
    }
}

// Fetch registered courses and grades for the selected semester
$selected_semester = '';
$grades = [];

if (isset($_POST['view_grades']) && isset($_POST['semester']) && !empty($_POST['semester'])) {
    $selected_semester = mysqli_real_escape_string($conn, $_POST['semester']);

    // Query registered courses and grades for the selected semester
    $query = "
        SELECT 
            c.course_name, 
            IFNULL(sm.coursework_marks, 'Not Available') AS coursework_marks, 
            IFNULL(sm.final_exam_marks, 'Not Available') AS final_exam_marks 
        FROM course_registrations cr
        JOIN semester_courses sc ON cr.semester_course_id = sc.id
        JOIN courses c ON sc.course_id = c.course_id
        LEFT JOIN student_marks sm ON sm.course_id = c.course_id AND sm.student_id = '$student_id'
        WHERE cr.student_id = '$student_id' AND sc.semester_id = '$selected_semester' AND cr.status = 'Approved'
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
    <title>View Grades by Semester</title>
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
    <h1>View Grades by Semester</h1>
    <form method="POST">
        <label for="semester">Select Semester</label>
        <select name="semester" id="semester" required>
            <option value="">--Select Semester--</option>
            <?php foreach ($semesters as $id => $name): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo ($id == $selected_semester) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="view_grades">View Grades</button>
    </form>

    <?php if (!empty($grades)): ?>
        <h2>Grades for <?php echo htmlspecialchars($semesters[$selected_semester]); ?></h2>
        <table>
            <tr>
                <th>Course</th>
                <th>Coursework Marks</th>
                <th>Final Exam Marks</th>
                <th>Total Marks</th>
                <th>Grade</th>
            </tr>
            <?php foreach ($grades as $grade): 
                $coursework_marks = $grade['coursework_marks'] !== 'Not Available' ? $grade['coursework_marks'] : 'Not Available';
                $final_exam_marks = $grade['final_exam_marks'] !== 'Not Available' ? $grade['final_exam_marks'] : 'Not Available';

                $total_marks = (is_numeric($coursework_marks) && is_numeric($final_exam_marks)) 
                    ? $coursework_marks + $final_exam_marks 
                    : 'Not Available';

                $grade_letter = 'Not Available';
                if (is_numeric($total_marks)) {
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
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($coursework_marks); ?></td>
                    <td><?php echo htmlspecialchars($final_exam_marks); ?></td>
                    <td><?php echo htmlspecialchars($total_marks); ?></td>
                    <td><?php echo $grade_letter; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($selected_semester): ?>
        <p>No registered courses found for <?php echo htmlspecialchars($semesters[$selected_semester]); ?>.</p>
    <?php endif; ?>
</div>

</body>
</html>
