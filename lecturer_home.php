<?php
session_start();
require 'database.php';

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
$courseQuery = "SELECT course_name FROM courses WHERE lecturer_id = ?";
$stmt = $conn->prepare($courseQuery);

if ($stmt) {
    $stmt->bind_param("s", $lecturer_id);
    $stmt->execute();
    $courseResult = $stmt->get_result();
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row['course_name'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Homepage</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgb(235, 235, 235);
        }

        /* Main Container */
        .container {
            margin: 100px auto 50px;
            max-width: 800px;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
        }

        .container h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }

        .container p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .course-list {
            list-style-type: none;
            padding: 0;
        }

        .course-list li {
            font-size: 18px;
            padding: 10px;
            background-color: #f4f4f4;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: center;
                padding: 15px;
            }

            .navbar .right-section {
                flex-direction: row;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar_lecturer.php'; ?>

<!-- Main Content -->
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>This is your Lecturer Portal. Here you can manage and track your students' progress.</p>
    
    <h2>Your Teaching Courses</h2>
    <ul class="course-list">
        <?php if (!empty($courses)): ?>
            <?php foreach ($courses as $course): ?>
                <li><?= htmlspecialchars($course) ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No courses assigned.</li>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>
