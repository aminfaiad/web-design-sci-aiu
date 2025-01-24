<?php
session_start();
require_once 'database.php';

// Redirect if user is not logged in or not a student
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch the student ID based on the session username
$username = $_SESSION['username'];
$student_query = "SELECT student_id FROM students WHERE username = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result && $student_result->num_rows > 0) {
    $student_data = $student_result->fetch_assoc();
    $student_id = $student_data['student_id'];
} else {
    header("Location: login.php");
    exit;
}

// Handle course addition and removal
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action']) && isset($_POST['course_id']) && isset($_POST['semester_id'])) {
        $course_id = $_POST['course_id'];
        $semester_id = $_POST['semester_id'];

        // Count the number of registered courses for the semester
        $count_query = "SELECT COUNT(*) as total FROM course_registrations cr 
                        JOIN semester_courses sc ON cr.semester_course_id = sc.id 
                        WHERE cr.student_id = ? AND sc.semester_id = ?";
        $stmt = $conn->prepare($count_query);
        $stmt->bind_param("si", $student_id, $semester_id);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $row = $count_result->fetch_assoc();
        $registered_courses_count = $row['total'];

        if ($_POST['action'] == 'add') {
            if ($registered_courses_count >= 3) {
                $error_message = "You cannot register for more than 3 courses.";
            } else {
                // Add course to registration
                //$insert_query = "INSERT INTO course_registrations (student_id, semester_course_id, status) VALUES (?, ?, 'Pending')";
                $insert_query = "INSERT INTO course_registrations (student_id, semester_course_id, status) VALUES (?, ?, 'Approved')";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("si", $student_id, $course_id);
                if ($stmt->execute()) {
                    $message = "Course added successfully!";
                } else {
                    $error_message = "Error adding course: " . $stmt->error;
                }
            }
        } elseif ($_POST['action'] == 'drop') {
            // Drop course from registration
            $delete_query = "DELETE FROM course_registrations WHERE student_id = ? AND semester_course_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("si", $student_id, $course_id);
            if ($stmt->execute()) {
                $message = "Course dropped successfully!";
            } else {
                $error_message = "Error dropping course: " . $stmt->error;
            }
        }
    }
}

// Fetch semesters from the semesters table
$semesters = [];
$semester_query = "SELECT semester_id, semester_name FROM semesters";
$semester_result = $conn->query($semester_query);

if ($semester_result && $semester_result->num_rows > 0) {
    while ($row = $semester_result->fetch_assoc()) {
        $semesters[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        select, input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-button {
            background-color: #28a745;
        }
        .drop-button {
            background-color: #dc3545;
        }
        .message {
            color: green;
            font-weight: bold;
            margin-top: 15px;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 15px;
        }
        .course-list {
            margin-top: 20px;
            text-align: left;
        }
        .course-list div {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>


<div class="container">
    <h2>Course Registration</h2>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label for="semester">Select Semester:</label>
        <select id="semester" name="semester" onchange="fetchCourses(this.value)">
            <option value="" disabled selected>Select a Semester</option>
            <?php foreach ($semesters as $semester): ?>
                <option value="<?php echo $semester['semester_id']; ?>">
                    <?php echo $semester['semester_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="courses-container" class="course-list"></div>
</div>

<script>
    function fetchCourses(semesterId) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `fetch_courses.php?semester_id=${semesterId}&student_id=<?php echo $student_id; ?>`, true);
        xhr.onload = function () {
            if (this.status === 200) {
                document.getElementById('courses-container').innerHTML = this.responseText;
            }
        };
        xhr.send();
    }
</script>
</body>
</html>
