<?php
session_start();
require 'database.php'; // Include database connection file

// Initialize variables for error and success messages
$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($newPassword) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if username exists
            $roles = ['students', 'lecturers'];
            $userFound = false;

            foreach ($roles as $role) {
                $query = "SELECT * FROM $role WHERE username = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $userFound = true;

                    // Update the password for the user
                    $updateQuery = "UPDATE $role SET password = ? WHERE username = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("ss", $newPassword, $username);

                    if ($updateStmt->execute()) {
                        $success = "Password reset successfully! You can now log in with your new password.";
                    } else {
                        $error = "Failed to reset the password. Please try again.";
                    }

                    break;
                }
            }

            if (!$userFound) {
                $error = "Username not found.";
            }
        } catch (mysqli_sql_exception $e) {
            $error = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SMS</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
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
            color:rgb(0, 28, 119);
        }

        /* Main Container */
        .container {
            margin: 50px auto;
            max-width: 400px;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            font-size: 22px;
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
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button[type="submit"] {
            padding: 10px;
            background-color:rgb(0, 28, 119);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color:rgb(0, 28, 119);
        }

        .success {
            color: green;
            text-align: center;
            margin-top: 10px;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color:rgb(0, 28, 119);
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
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

    <!-- Reset Password Form -->
    <div class="container">
        <h2>Reset Password</h2>
        <?php 
        if (!empty($success)) echo "<p class='success'>$success</p>"; 
        if (!empty($error)) echo "<p class='error'>$error</p>"; 
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Reset Password</button>
        </form>
        <div class="back-link">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>


</body>
</html>
