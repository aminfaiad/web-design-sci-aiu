<?php
session_start();
require_once 'database.php';

// Initialize error and success message variables
$error = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $fullname = trim($_POST['fullname']); // Capture full name
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $table_name = $role; // Table name is the same as the role

        // Generate appropriate ID based on the role
        if ($role === 'students') {
            $id_prefix = 'AIU2210';
            $id_column = 'student_id';
        } elseif ($role === 'lecturers') {
            $id_prefix = 'AIU';
            $id_column = 'lecturer_id';
        } else {
            $error = "Invalid role selected.";
        }

        if (empty($error)) {
            // Check if the username already exists
            $check_query = "SELECT username FROM $table_name WHERE username = ?";
            $stmt_check = $conn->prepare($check_query);
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Generate a unique ID by counting existing entries
                $query = "SELECT COUNT(*) AS count FROM $table_name";
                $result = $conn->query($query);
                $row = $result->fetch_assoc();
                $count = $row['count'] + 1;

                // Format the new ID
                $generated_id = $id_prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

                // Prepare the insert query with the new ID
                $query = "INSERT INTO $table_name ($id_column, fullname, username, password) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);

                if (!$stmt) {
                    $error = "Error preparing query: " . $conn->error;
                } else {
                    // Bind parameters for the query
                    $stmt->bind_param("ssss", $generated_id, $fullname, $username, $password);

                    if ($stmt->execute()) {
                        $successMessage = "Registration successful. Your ID: $generated_id. You can now log in.";
                    } else {
                        $error = "Error saving data: " . $stmt->error;
                    }

                    $stmt->close();
                }
            }
            $stmt_check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SMS</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('kdacademy.webp');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            position: relative;
            z-index: 1;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
            z-index: -1;
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
            width: 80px;
            height: 80px;
            margin-right: 20px;
        }

        .navbar h1 {
            font-size: 24px;
            color: rgb(0, 28, 119);
        }

        /* Main Container */
        .container {
            margin: 50px auto;
            max-width: 400px;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
            opacity: 0; /* Initially hidden */
            transform: translateY(20px); /* Slight slide-down effect */
            transition: opacity 1.0s ease, transform 1.0s ease; /* Smooth transition */
        }

        .container.visible {
            opacity: 1; /* Fully visible */
            transform: translateY(0); /* Slide to its original position */
        }

        .container h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="password"],
        select {
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button[type="submit"] {
            padding: 10px;
            background-color: rgb(0, 28, 119);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: rgb(0, 28, 119);
        }

        .error {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="left-section">
            <a href="login.php" style="display: flex; align-items: center; text-decoration: none;">
                <img src="logo.png" alt="Logo">
                <h1>STUDENT MANAGEMENT SYSTEM</h1>
            </a>
        </div>
    </div>

    <!-- Register Form -->
    <div class="container">
        <h1>Register</h1>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($successMessage)) echo "<p class='success'>$successMessage</p>"; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="fullname">Full Name:</label>
            <input type="text" name="fullname" id="fullname" required>

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="students">Student</option>
                <option value="lecturers">Lecturer</option>
            </select>

            <button type="submit" name="register">Register</button>
        </form>
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(function () {
            document.querySelector('.container').classList.add('visible');
        }, 500); // 1 second delay
    });
</script>
</html>
