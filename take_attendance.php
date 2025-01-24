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

// Handle adding a new class schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $course_id = $_POST['course_id'];
    $class_date = $_POST['class_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $insert_query = "INSERT INTO class_schedule (course_id, lecturer_id, class_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param('issss', $course_id, $lecturer_id, $class_date, $start_time, $end_time);
    $stmt->execute();
    $_SESSION['message'] = "Class scheduled successfully!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Filter and fetch class schedules
$selected_course = isset($_GET['course_id']) ? $_GET['course_id'] : '';
$filter_query = "SELECT cs.class_id, c.course_name, cs.class_date, cs.start_time, cs.end_time FROM class_schedule cs INNER JOIN courses c ON cs.course_id = c.course_id WHERE cs.lecturer_id = ?";

if (!empty($selected_course)) {
    $filter_query .= " AND cs.course_id = ?";
    $stmt = $conn->prepare($filter_query);
    $stmt->bind_param('si', $lecturer_id, $selected_course);
} else {
    $stmt = $conn->prepare($filter_query);
    $stmt->bind_param('s', $lecturer_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Classes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }
        h1 {
            color: #004aad;
            text-align: center;
        }
        form, table {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
        }
        select, input[type="date"], input[type="time"], button {
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
        a {
            color: #004aad;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'navbar_lecturer.php'; ?>

    <h1>Scheduled Classes</h1>

    <form method="GET">
        <label for="course">Filter by Course:</label>
        <select name="course_id" id="course" onchange="this.form.submit()">
            <option value="">-- All Courses --</option>
            <?php while ($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php if ($selected_course == $course['course_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Class Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['class_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                    <td>
                        <a href="attendance_form.php?class_id=<?php echo $row['class_id']; ?>">Take Attendance</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h1>Add New Class</h1>
    <form method="POST">
        <label for="course">Select Course:</label>
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php
            $stmt = $conn->prepare($courses_query);
            $stmt->bind_param('s', $lecturer_id);
            $stmt->execute();
            $courses_result = $stmt->get_result();
            while ($row = $courses_result->fetch_assoc()) {
            ?>
                <option value="<?php echo htmlspecialchars($row['course_id']); ?>"> 
                    <?php echo htmlspecialchars($row['course_name']); ?> 
                </option>
            <?php } ?>
        </select>

        <label for="class_date">Class Date:</label>
        <input type="date" name="class_date" id="class_date" required>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" id="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" id="end_time" required>

        <button type="submit" name="add_class">Add Class</button>
    </form>
    <script>
    // Get the current date and time
    const now = new Date();

    // Format the date as YYYY-MM-DD
    const formattedDate = now.toISOString().split('T')[0];
    document.getElementById('class_date').value = formattedDate;

    // Format the time as HH:MM
    const pad = (num) => num.toString().padStart(2, '0');

    const currentHours = pad(now.getHours());
    const currentMinutes = pad(now.getMinutes());
    document.getElementById('start_time').value = `${currentHours}:${currentMinutes}`;

    // Calculate the end time (1 hour later)
    now.setHours(now.getHours() + 1);
    const endHours = pad(now.getHours());
    const endMinutes = pad(now.getMinutes());
    document.getElementById('end_time').value = `${endHours}:${endMinutes}`;
</script>
</body>
</html>
