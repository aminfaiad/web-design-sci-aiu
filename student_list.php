<?php
session_start();
require 'database.php'; // Include database connection file

// Check if user is logged in and their role is lecturer
if (isset($_SESSION['username']) && $_SESSION['role'] == 'lecturers') {
    $username = $_SESSION['username'];
} else {
    header("Location: login.php");
    exit;
}

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

// Prepare the query to fetch student data with optional course filter
$query = "SELECT DISTINCT s.student_id, s.fullname, sem.semester_name 
FROM course_registrations cr
INNER JOIN semester_courses sc ON cr.semester_course_id = sc.id
INNER JOIN students s ON cr.student_id = s.student_id
INNER JOIN semesters sem ON sc.semester_id = sem.semester_id
WHERE cr.status = 'Approved'";


$params = [];
$types = "";
if (!empty($_GET['course_id'])) {
    $query .= " AND sc.course_id = ?";
    $params[] = $_GET['course_id'];
    $types .= "i";
}

$stmt = $conn->prepare($query);

if ($stmt && count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$studentList = [];
while ($row = $result->fetch_assoc()) {
    $studentList[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
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
        .filters {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .filters select, .filters button {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        .filters select {
            width: 250px;
        }
        .filters button {
            background-color: rgb(0, 23, 229);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .filters button:hover {
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
        .no-data {
            text-align: center;
            color: #888;
            font-size: 16px;
            padding: 20px;
        }
    </style>
</head>
<body>

<?php include 'navbar_lecturer.php'; ?>


<div class="container">
    <h1>Student List</h1>

    <!-- Filters Section -->
    <form method="GET" action="">
        <div class="filters">
            <select name="course_id">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?= htmlspecialchars($course['course_id']) ?>" 
                        <?= isset($_GET['course_id']) && $_GET['course_id'] == $course['course_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter</button>
        </div>
    </form>

    <!-- Student List Table -->
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($studentList)) : ?>
                <?php foreach ($studentList as $row) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['semester_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3" class="no-data">No students found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
