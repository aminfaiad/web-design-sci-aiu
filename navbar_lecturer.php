<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle logout action
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php"); // Redirect to login page after logout
    exit();
}
?>

<!-- Navbar Styles -->
<style>
    .navbar-wrapper #navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #ffffff;
        padding: 15px 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .navbar-wrapper #navbar .navbar-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .navbar-wrapper #navbar img {
        width: 100px;
        height: 100px;
        margin-right: 20px;
    }

    .navbar-wrapper #navbar .navbar-title h1 {
        font-size: 24px;
        color: rgb(0, 28, 119);
    }

    .navbar-wrapper #navbar .navbar-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .navbar-wrapper #navbar .navbar-btn {
        all: unset;
        font-size: 16px;
        color: #333 !important;
        text-decoration: none;
        padding: 10px 15px;
        font-weight: bold;
        background-color: transparent !important;
        border: none;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .navbar-wrapper #navbar .navbar-btn:hover {
        color: rgb(0, 28, 119);
    }

    .navbar-wrapper #navbar .navbar-dropdown {
        position: relative;
    }

    .navbar-wrapper #navbar .navbar-dropdown-content {
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

    .navbar-wrapper #navbar .navbar-dropdown-content a {
        color: #333;
        padding: 12px 20px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        transition: background-color 0.3s ease, color 0.3s ease;
        font-weight: bold;
    }

    .navbar-wrapper #navbar .navbar-dropdown:hover .navbar-dropdown-content {
        display: block;
    }

    .navbar-wrapper #navbar .navbar-logout-btn {
        all: unset;
        background-color: rgb(137, 27, 38) !important;
        color: #fff !important;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
    }

    .navbar-wrapper #navbar .navbar-logout-btn:hover {
        background-color: rgb(91, 19, 26) !important;
    }
</style>

<!-- Navbar HTML -->
<div class="navbar-wrapper">
    <div id="navbar">
        <div class="navbar-left">
            <a href="lecturer_home.php" style="display: flex; align-items: center; text-decoration: none;">
                <img src="logo.png" alt="Logo">
                <div class="navbar-title">
                    <h1>LECTURER MANAGEMENT SYSTEM</h1>
                    <h2>Lecturer Portal</h2>
                </div>
            </a>
        </div>
        <div class="navbar-right">
            <a href="lecturer_home.php" class="navbar-btn">Home</a>

            <div class="navbar-dropdown">
                <button class="navbar-btn">Students</button>
                <div class="navbar-dropdown-content">
                    <a href="student_list.php">Student List</a>
                    <a href="enter_marks.php">Enter Student Marks</a>
                    <a href="view_marks.php">View Student Marks</a>
                </div>
            </div>

            <div class="navbar-dropdown">
                <button class="navbar-btn">Manage Attendance</button>
                <div class="navbar-dropdown-content">
                    <a href="take_attendance.php">Take Attendance</a>
                    <a href="view_attendance.php">View Attendance</a>
                </div>
            </div>

            <form method="post" style="display: inline;">
                <button type="submit" name="logout" class="navbar-logout-btn">Logout</button>
            </form>
        </div>
    </div>
</div>
