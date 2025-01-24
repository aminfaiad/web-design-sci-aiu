<?php
session_start();
require 'database.php'; // Include database connection file


if (isset($_POST['logout'])) {
    // Destroy the session and redirect to login page
    session_destroy();
    header("Location: login.php");
    exit;
}

// Dynamic welcome message
if (isset($_SESSION['username']) && $_SESSION['role'] == 'students') {
    $username = $_SESSION['username'];
    $welcomeMessage = "Welcome, $username! This is the Student Homepage.";
} else {
    // If user is not logged in or their role is not student, redirect to login page
    header("Location: login.php");
    exit;
}

//Assuming student_id is available from session
$student_id = $_SESSION['student_id']; 

 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Homepage</title>
    <style>
        /* Global Styles */
        body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-image: url('kdacademy.webp');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed; /* Makes it look better on scroll */
}

        /* Navbar Styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 15px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar .left-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar img {
            width: 100px;
            height: 100px;
            margin-right: 20px;
        }

        .navbar h1 {
            font-size: 24px;
            color:rgb(0, 28, 119);
        }

        .navbar .right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar a, .dropbtn, .logout-btn {
            font-size: 16px;
            color: #333;
            text-decoration: none;
            padding: 10px 15px;
            font-weight: bold;
            background-color: transparent;
            border: none;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .navbar a:hover, .dropbtn:hover, .logout-btn:hover {
            color:rgb(0, 28, 119);
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 40px;
            left: 0;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            z-index: 1;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Nested Dropdown Styles (Right to Left) */
        .nested-dropdown {
            position: relative;
        }

        .nested-dropdown-content {
            display: none;
            position: absolute;
            top: 0;
            right: 100%; /* Submenu appears to the left of the parent menu */
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            z-index: 1;
        }

        .nested-dropdown:hover .nested-dropdown-content {
            display: block;
        }

        .logout-btn {
            background-color:rgb(137, 27, 38);
            color: #fff;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color:rgb(91, 19, 26);
        }


         /* Modal Styles */
         .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            width: 50%;
            height: 70%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 10px;
            background-color: #f57c00;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .modal iframe {
            flex: 1;
            border: none;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        /* Main Content Layout */
        .main-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 50px auto;
            max-width: 1200px;
            padding: 30px;
        }

        .container {
            flex: 1;
            margin-right: 20px;
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
    </style>

</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="left-section">
        <a href="student_home.php" style="display: flex; align-items: center; text-decoration: none;">
            <img src="logo.png" alt="Logo">
            <div class="title-container">
                <h1>STUDENT MANAGEMENT SYSTEM</h1>
                <h2>Student Portal</h2>
            </div>
        </a>
    </div>
    <div class="right-section">
        <a href="student_home.php">Home</a>
        <a href="student_register_course_semester.php">Register Courses & Semester</a>
        <div class="dropdown">
            <button class="dropbtn">Attendance</button>
            <div class="dropdown-content">
                <!-- Nested Dropdown-->
                <div class="nested-dropdown">
                    <a href="student_viewstudents_attendance.php">View Attendance Record</a>
                </div>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Grades</button>
        </div>
        <form method="post" style="display: inline;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>
</div>


<!-- Main Content -->
<div class="main-content">
    <div class="container">
        <h1>Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>This is your Student Portal. Here you can track your progress.</p>
    </div>
</div>

</body>
</html>
