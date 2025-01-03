<?php
ob_start();
session_start();
include 'db_connection.php';
include 'config/email_config.php';

$message = '';
$messageType = '';
$redirect = false;
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $conn = getDatabaseConnection();

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === $role) {
                // Generate 6-digit verification code
                $verificationCode = sprintf("%06d", mt_rand(0, 999999));
                
                // Store user data and verification code in session
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['temp_user_role'] = $role;
                $_SESSION['temp_user_email'] = $user['email'];
                $_SESSION['verification_code'] = $verificationCode;
                $_SESSION['code_expiry'] = time() + (10 * 60); // 10 minutes
                
                if (EmailService\sendVerificationCode($user['email'], $verificationCode)) {
                    // Store verification attempt
                    $storeTwoFASql = "INSERT INTO verification_attempts (user_id, verification_code, expiry) VALUES (?, ?, ?)";
                    $twoFAStmt = $conn->prepare($storeTwoFASql);
                    $expiryTime = date('Y-m-d H:i:s', $_SESSION['code_expiry']);
                    $twoFAStmt->bind_param('iss', $user['id'], $verificationCode, $expiryTime);
                    
                    if ($twoFAStmt->execute()) {
                        $message = "Verification code sent to " . substr($user['email'], 0, 3) . "***" . 
                                  substr($user['email'], strpos($user['email'], '@'));
                        $messageType = "success";
                        $redirect = true;
                        $redirectUrl = 'verify_code.php';
                    } else {
                        $message = "Error storing verification. Please try again.";
                        $messageType = "error";
                    }
                    $twoFAStmt->close();
                } else {
                    $message = "Failed to send verification code. Please try again.";
                    $messageType = "error";
                }
            } else {
                $message = "Invalid role selected.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid password.";
            $messageType = "error";
        }
    } else {
        $message = "User not found.";
        $messageType = "error";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .login-container {
            background-color: #e0e0e0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h2 {
            color: #003366;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .login-container input,
        .login-container select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .login-container input:focus,
        .login-container select:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 51, 102, 0.2);
        }

        .login-container label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .login-container button:hover {
            background-color: #00509e;
        }

        .login-container p {
            margin-top: 20px;
            color: #666;
        }

        .login-container a {
            color: #003366;
            text-decoration: none;
            font-weight: 500;
        }

        .login-container a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .forgot {
            display: block;
            margin-top: 15px;
        }

        footer {
            text-align: center;
            width: 100%;
            padding: 20px 0;
            background-color: #003366;
            color: white;
            position: fixed;
            bottom: 0;
            left: 0;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Username" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required>
            
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="customer">Customer</option>
            </select>
            
            <button type="submit">Login</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">SignUp</a></p>
        <a href="forgot_password.php" class="forgot">Forgot Password?</a>
    </div>

    <footer>
        &copy; 2024 Railway Lost and Found. All rights reserved.
    </footer>

    <?php if ($redirect): ?>
    <script>
        setTimeout(() => {
            window.location.href = '<?php echo $redirectUrl; ?>';
        }, 3000);
    </script>
    <?php endif; ?>
</body>
</html>
<?php
ob_end_flush();
?>