<?php
session_start();
require 'database.php'; // Include your database connection file

// Check if user is logged in and their role is lecturer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'lecturers') {
    header("Location: login.php");
    exit;
}

// Fetch lecturer ID based on username from session
$lecturer_username = $_SESSION['username'];
$lecturer_query = "SELECT lecturer_id FROM lecturers WHERE username = ?";
$stmt = $conn->prepare($lecturer_query);
$stmt->bind_param('s', $lecturer_username);
$stmt->execute();
$stmt->bind_result($lecturer_id);
$stmt->fetch();
$stmt->close();

// Fetch courses taught by the lecturer
$courses_query = "SELECT course_id, course_name FROM courses WHERE lecturer_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param('s', $lecturer_id);
$stmt->execute();
$courses_result = $stmt->get_result();

// Handle class scheduling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $course_id = $_POST['course'];
    $class_date = $_POST['class_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $query = "INSERT INTO class_schedule (course_id, lecturer_id, class_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('issss', $course_id, $lecturer_id, $class_date, $start_time, $end_time);
    $stmt->execute();
    $_SESSION['message'] = "Class scheduled successfully!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch scheduled classes for the lecturer
$scheduled_classes_query = "SELECT c.course_name, cs.class_date, cs.start_time, cs.end_time FROM class_schedule cs JOIN courses c ON cs.course_id = c.course_id WHERE cs.lecturer_id = ? ORDER BY cs.class_date, cs.start_time";
$stmt = $conn->prepare($scheduled_classes_query);
$stmt->bind_param('s', $lecturer_id);
$stmt->execute();
$scheduled_classes_result = $stmt->get_result();

// Display the success message after redirection
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']); // Clear the message after displaying it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule Management</title>
</head>
<body>
    <h1>Scheduled Classes</h1>
    <table border="1">
        <tr>
            <th>Course Name</th>
            <th>Class Date</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($scheduled_classes_result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                <td><?php echo htmlspecialchars($row['class_date']); ?></td>
                <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                <td><?php echo htmlspecialchars($row['end_time']); ?></td>
            </tr>
        <?php } ?>
    </table>

    <h1>Add New Class</h1>
    <form method="POST">
        <label for="course">Select Course:</label>
        <select name="course" id="course" required>
            <option value="">-- Select Course --</option>
            <?php while ($row = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo htmlspecialchars($row['course_id']); ?>"> <?php echo htmlspecialchars($row['course_name']); ?> </option>
            <?php } ?>
        </select>

        <label for="class_date">Class Date:</label>
        <input type="date" name="class_date" required>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" required>

        <button type="submit" name="add_class">Add Class</button>
    </form>
</body>
</html>
