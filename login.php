<?php
session_start();
// Include database connection
include("database.php");

// Initialize error variable
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Adjusted roles for this specific use case
    $roles = array(
        'students' => 'student_home.php',
        'lecturers' => 'lecturer_home.php',
    );

    $usernameCorrect = false;

    try {
        foreach ($roles as $role => $homePage) {
            // Table and column names updated based on the database schema
            $query = "SELECT * FROM $role WHERE username=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                // Note: Replace with password_verify() for secure implementation
                if ($password === $row['password']) { 
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                    if ($role == 'students') {
                        // Fetch and store student_id from the database
                        $_SESSION['student_id'] = $row['student_id'];
                    } elseif ($role == 'lecturers') {
                        // Fetch and store lecturer_id from the database
                        $_SESSION['lecturer_id'] = $row['lecturer_id'];
                    }

                    header("Location: $homePage");
                    exit();
                }
                $usernameCorrect = true;
            }
        }
    } catch (mysqli_sql_exception $e) {
        $error = "An error occurred while trying to log in. Please try again later.";
    }

    if (!$usernameCorrect) {
        $error = "Username or password is incorrect. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMS</title>
    <style>
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

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 15px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .password-container input[type="password"],
        .password-container input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            transition: border 0.3s ease;
        }

        input:focus {
            border: 2px solidrgb(0, 28, 119);
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            padding-right: 40px; /* Space for eye icon */
        }

        .password-container .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #555;
        }

        .password-container .toggle-password svg {
            width: 24px;
            height: 24px;
        }

        .toggle-password:focus {
            outline: none;
        }

        .caps-warning {
            font-size: 12px;
            margin-top: -5px;
            margin-bottom: 10px;
            color: red;
            display: none;
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
            background-color:            colorgb(0, 28, 119);

        }

        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        .forgot-password, .register-link {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a, .register-link a {
            color:rgb(0, 28, 119);
            text-decoration: none;
        }

        .forgot-password a:hover, .register-link a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div>
            <a href="login.php" style="display: flex; align-items: center; text-decoration: none;">
                <img src="logo.png" alt="Logo">
                <h1>STUDENT MANAGEMENT SYSTEM</h1>
            </a>
        </div>
    </div>

    <!-- Login Form -->
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                    👁️
                </button>
            </div>
            <p class="caps-warning" id="capsWarning">Caps Lock is ON</p>

            <button type="submit">Login</button>
        </form>
        <div class="forgot-password">
            <p><a href="reset_password.php">Forgot Password?</a></p>
        </div>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>

</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(function () {
            document.querySelector('.container').classList.add('visible');
        }, 500); // 1 second delay
    });

    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("password");
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }
    }
</script>
</html>
