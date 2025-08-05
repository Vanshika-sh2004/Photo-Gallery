<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.97);
            padding: 40px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 700px;
            min-height: 450px;
            animation: fadeIn 1s ease-in-out;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 30px;
            color: #333;
            font-size: 32px;
        }

        .form-group {
            margin-bottom: 25px;
            margin-top:16px;
        }

        label {
            display: block;
            text-align: left;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
            font-size: 16px;
            margin-left:4rem;
        
        }

        input[type="text"],
        input[type="password"] {
            width: 70%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            transition: border 0.3s ease;
            display: block;
            margin-left:4rem;
            margin-bottom:1rem;
        }

        input:focus {
            border-color: #f77a59;
            outline: none;
        }

        .password-wrapper {
            position: relative;
            margin: 0 auto;
    
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 13px;
            color: #777;
            background: none;
            border: none;
        }

        .login-container button[type="submit"] {
            width: 80%;
            padding:14px  10px;
            background: linear-gradient(to right, #f77a59, #ff6347);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top:1.4rem;
        }

        .login-container button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(247, 122, 89, 0.4);
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            font-weight: 500;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login as Admin</h2>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">उपयोगकर्ता नाम / Username</label>
            <input type="text" name="username" id="username" placeholder="Enter username" required>
        </div>

        <div class="form-group">
            <label for="password">पासवर्ड / Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Enter password" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
            </div>
        </div>

        <button type="submit">Login</button>
    </form>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        const toggleBtn = document.querySelector(".toggle-password");
        const type = passwordField.getAttribute("type");

        if (type === "password") {
            passwordField.setAttribute("type", "text");
            toggleBtn.textContent = "Hide";
        } else {
            passwordField.setAttribute("type", "password");
            toggleBtn.textContent = "Show";
        }
    }
</script>

</body>
</html>
