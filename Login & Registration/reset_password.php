<?php
ob_start();
session_start();
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$messageType = '';
$validToken = false;  // Initialize this at the start

// First, verify the token from URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    $conn = getDatabaseConnection();

    // Check if token exists and is not expired or used
    $sql = "SELECT pr.*, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? 
            AND pr.expiry > NOW() 
            AND pr.is_used = FALSE";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $reset_info = $result->fetch_assoc();
            $_SESSION['reset_user_id'] = $reset_info['user_id'];
            $_SESSION['reset_token'] = $token;
            $validToken = true;  // Set to true only if token is valid
        } else {
            $message = "Invalid or expired reset link. Please request a new one.";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "An error occurred. Please try again.";
        $messageType = "error";
    }
    $conn->close();
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['reset_user_id'])) {
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];
    
    // Validate password
    if (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        $conn = getDatabaseConnection();
        
        // Begin transaction
        $conn->begin_transaction();
        try {
            // Update user's password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateSql = "UPDATE users SET password = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('si', $hashedPassword, $_SESSION['reset_user_id']);
            $updateStmt->execute();

            // Mark reset token as used
            $tokenSql = "UPDATE password_resets SET is_used = TRUE WHERE token = ?";
            $tokenStmt = $conn->prepare($tokenSql);
            $tokenStmt->bind_param('s', $_SESSION['reset_token']);
            $tokenStmt->execute();

            // Commit transaction
            $conn->commit();

            $message = "Password successfully reset! Redirecting to login page...";
            $messageType = "success";
            
            // Clear reset session data
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_token']);
            
            // Redirect after showing message
            header("refresh:3;url=login.php");
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $message = "An error occurred. Please try again.";
            $messageType = "error";
        }
        
        if (isset($updateStmt)) $updateStmt->close();
        if (isset($tokenStmt)) $tokenStmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
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

        .reset-container {
            background-color: #e0e0e0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .reset-container h2 {
            color: #003366;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .reset-container p {
            margin-bottom: 20px;
            color: #666;
        }

        .reset-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .reset-container input:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 51, 102, 0.2);
        }

        .reset-container button {
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

        .reset-container button:hover {
            background-color: #00509e;
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

        .back-link {
            margin-top: 20px;
            display: block;
            color: #003366;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Password</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <form method="POST" action="" onsubmit="return validateForm()">
                <input type="password" id="new-password" name="new-password" placeholder="New Password" required>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php else: ?>
            <p>Invalid or expired reset link. Please request a new password reset.</p>
            <a href="forgot_password.php" class="back-link">Back to Forgot Password</a>
        <?php endif; ?>
    </div>

    <footer>
        &copy; 2024 Railway Lost and Found. All rights reserved.
    </footer>

    <script>
        function validateForm() {
            const password = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (password.length < 6) {
                alert('Password must be at least 6 characters long');
                return false;
            }

            if (!/[A-Z]/.test(password)) {
                alert('Password must include at least one uppercase letter');
                return false;
            }

            if (!/\d/.test(password)) {
                alert('Password must include at least one number');
                return false;
            }

            if (!/[!@#$%^&*]/.test(password)) {
                alert('Password must include at least one special character (!@#$%^&*)');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
<?php
ob_end_flush();
?>