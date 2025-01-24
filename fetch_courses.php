<?php
require_once 'database.php';

if (isset($_GET['semester_id']) && isset($_GET['student_id'])) {
    $semester_id = $_GET['semester_id'];
    $student_id = $_GET['student_id'];

    // Get all courses for the semester
    $query = "
        SELECT sc.id, c.course_name 
        FROM semester_courses sc
        JOIN courses c ON sc.course_id = c.course_id
        WHERE sc.semester_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $course_id = $row['id'];
        $course_name = $row['course_name'];

        // Check if student is already registered for the course
        $check_query = "SELECT * FROM course_registrations WHERE student_id = ? AND semester_course_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $student_id, $course_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $is_registered = $check_result->num_rows > 0;

        echo "<div>";
        echo "<span>$course_name</span>";
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='course_id' value='$course_id'>";
        echo "<input type='hidden' name='semester_id' value='$semester_id'>";
        if ($is_registered) {
            echo "<button type='submit' name='action' value='drop' class='button drop-button'>Drop Course</button>";
        } else {
            echo "<button type='submit' name='action' value='add' class='button add-button'>Add Course</button>";
        }
        echo "</form>";
        echo "</div>";
    }
}
?>
