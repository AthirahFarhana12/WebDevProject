<?php
session_start();
include 'db_connection.php';

// Check if user has a pending verification
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['verification_code'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$messageType = '';
$redirect = false;
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedCode = $_POST['verification_code'];
    $storedCode = $_SESSION['verification_code'];
    $codeExpiry = $_SESSION['code_expiry'];

    // Check if code is expired
    if (time() > $codeExpiry) {
        $message = "Verification code has expired. Please login again.";
        $messageType = "error";
        // Clear session and redirect after 3 seconds
        session_destroy();
        header("refresh:3;url=login.php");
    } 
    // Verify the code
    else if ($submittedCode === $storedCode) {
        // Set full session variables
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['role'] = $_SESSION['temp_user_role'];
        
        // Update verification attempt in database
        $conn = getDatabaseConnection();
        $updateSql = "UPDATE verification_attempts SET is_used = TRUE WHERE user_id = ? AND verification_code = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('is', $_SESSION['user_id'], $storedCode);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // Determine dashboard based on role
        $role = $_SESSION['role'];
        switch($role) {
            case 'admin':
                $redirectUrl = 'admin_dashboard.php';
                break;
            case 'staff':
                $redirectUrl = 'staff_dashboard.php';
                break;
            case 'customer':
                $redirectUrl = 'customer_dashboard.php';
                break;
            default:
                $redirectUrl = 'login.php';
        }

        // Clear temporary session variables
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_user_role']);
        unset($_SESSION['verification_code']);
        unset($_SESSION['code_expiry']);
        unset($_SESSION['temp_user_email']);

        $message = "Verification successful! Redirecting to dashboard...";
        $messageType = "success";
        $redirect = true;
    } else {
        // Log failed attempt
        $conn = getDatabaseConnection();
        $logSql = "INSERT INTO verification_failures (user_id, ip_address, attempted_code) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($logSql);
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param('iss', $_SESSION['temp_user_id'], $ipAddress, $submittedCode);
        $stmt->execute();
        $stmt->close();

        // Check number of failed attempts
        $checkSql = "SELECT COUNT(*) as fail_count FROM verification_failures 
                    WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param('i', $_SESSION['temp_user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $failCount = $result->fetch_assoc()['fail_count'];
        $stmt->close();
        $conn->close();

        if ($failCount >= 5) {
            $message = "Too many failed attempts. Please try logging in again after 15 minutes.";
            $messageType = "error";
            session_destroy();
            header("refresh:3;url=login.php");
        } else {
            $message = "Invalid verification code. Please try again. (" . (5 - $failCount) . " attempts remaining)";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Code</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .verify-container {
            background-color: #e0e0e0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        
        .verify-container h2 {
            margin-bottom: 20px;
            color: #003366;
        }
        
        .verify-container p {
            margin-bottom: 20px;
            color: #666;
            font-size: 0.9em;
            line-height: 1.5;
        }
        
        .code-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .code-input input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 20px;
            border: 2px solid #ccc;
            border-radius: 5px;
            margin: 0 2px;
        }
        
        .code-input input:focus {
            border-color: #003366;
            outline: none;
        }
        
        .verify-container button {
            width: 100%;
            padding: 12px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .verify-container button:hover {
            background-color: #00509e;
        }
        
        .verify-container .resend {
            margin-top: 20px;
            color: #00509e;
            text-decoration: none;
            font-size: 0.9em;
            display: block;
        }
        
        .verify-container .resend:hover {
            text-decoration: underline;
        }
        
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
            font-size: 0.9em;
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

        .timer {
            margin-top: 15px;
            font-size: 0.9em;
            color: #666;
        }

        .timer.expiring {
            color: #dc3545;
        }
        
        footer {
            text-align: center;
            width: 100%;
            padding: 20px 0;
            position: fixed;
            bottom: 0;
            background-color: #003366;
            color: white;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <h2>Verify Your Identity</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <p>Please enter the 6-digit verification code sent to your email
            <?php 
                $email = isset($_SESSION['temp_user_email']) ? $_SESSION['temp_user_email'] : '';
                if ($email) {
                    echo substr($email, 0, 3) . '***' . substr($email, strpos($email, '@'));
                }
            ?>
        </p>
        
        <form method="POST" action="">
            <div class="code-input">
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>
            <input type="hidden" id="verification_code" name="verification_code">
            <button type="submit">Verify Code</button>
        </form>
        
        <div id="timer" class="timer">Time remaining: <span>10:00</span></div>
        <a href="login.php" class="resend">Didn't receive the code? Try again</a>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Railway Lost and Found. All rights reserved.
    </footer>

    <script>
        // Handle auto-focus and movement between code inputs
        const inputs = document.querySelectorAll('.code-input input');
        inputs.forEach((input, index) => {
            // Only allow numbers
            input.addEventListener('keypress', function(e) {
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });

            // Handle paste event
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = e.clipboardData.getData('text');
                if (/^\d+$/.test(paste)) {
                    const digits = paste.split('');
                    inputs.forEach((input, i) => {
                        if (digits[i]) {
                            input.value = digits[i];
                            if (i < inputs.length - 1) {
                                inputs[i + 1].focus();
                            }
                        }
                    });
                }
            });

            input.addEventListener('input', function(e) {
                if (this.value.length === 1) {
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Combine inputs into hidden field before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const code = Array.from(inputs).map(input => input.value).join('');
            document.getElementById('verification_code').value = code;
        });

        // Timer functionality
        function startTimer(duration, display) {
            let timer = duration;
            const interval = setInterval(function () {
                const minutes = parseInt(timer / 60, 10);
                const seconds = parseInt(timer % 60, 10);

                display.textContent = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "Code expired";
                    window.location.href = 'login.php';
                } else if (timer < 60) {
                    // Add expiring class when less than 1 minute remains
                    document.querySelector('.timer').classList.add('expiring');
                }
            }, 1000);
        }

        // Start 10 minute countdown
        startTimer(600, document.querySelector('#timer span'));

        <?php if ($redirect): ?>
        setTimeout(() => {
            window.location.href = '<?php echo $redirectUrl; ?>';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>