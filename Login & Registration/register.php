<?php
ob_start();
session_start();
include 'db_connection.php';
include 'config/email_config.php';

$message = '';
$messageType = '';
$redirectToLogin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $retypePassword = $_POST['retype-password'];

    $conn = getDatabaseConnection();

    // Check for duplicate username or email
    $checkSql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('ss', $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existing = $checkResult->fetch_assoc();
        if ($existing['username'] === $username) {
            $message = 'Username already exists.';
        } else {
            $message = 'Email already registered.';
        }
        $messageType = 'error';
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssss', $username, $firstName, $lastName, $email, $hashedPassword);

        if ($stmt->execute()) {
            // Send welcome email
            if (EmailService\sendWelcomeEmail($email, $firstName)) {
                $message = 'Registration successful! Redirecting to login page...';
                $messageType = 'success';
                $redirectToLogin = true;
            } else {
                $message = 'Registration successful! You can now login.';
                $messageType = 'success';
                $redirectToLogin = true;
            }
        } else {
            $message = 'Registration failed: ' . $stmt->error;
            $messageType = 'error';
        }

        $stmt->close();
    }
    $checkStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SignUp</title>
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

        .signup-container {
            background-color: #e0e0e0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .signup-container h2 {
            color: #003366;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .signup-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .signup-container input:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 51, 102, 0.2);
        }

        .name-container {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .name-container input {
            margin-bottom: 0;
        }

        .signup-container button {
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

        .signup-container button:hover {
            background-color: #00509e;
        }

        .signup-container p {
            margin-top: 20px;
            color: #666;
        }

        .signup-container a {
            color: #003366;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-container a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
            display: none;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
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
            .signup-container {
                padding: 20px;
            }

            .name-container {
                flex-direction: column;
                gap: 15px;
            }

            .name-container input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>SignUp</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateForm()">
            <input type="text" id="username" name="username" placeholder="Username" required minlength="3">
            
            <div class="name-container">
                <input type="text" id="first-name" name="first-name" placeholder="First Name" required>
                <input type="text" id="last-name" name="last-name" placeholder="Last Name" required>
            </div>
            
            <input type="email" id="email" name="email" placeholder="email@example.com" required>
            
            <input type="password" id="password" name="password" placeholder="Password" required>
            <input type="password" id="retype-password" name="retype-password" placeholder="Retype password" required>
            
            <button type="submit">Sign Up</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <footer>
        &copy; 2024 Railway Lost and Found. All rights reserved.
    </footer>

    <script>
        function showMessage(message, type) {
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            
            // Insert message at the top of the form
            const form = document.querySelector('form');
            form.parentNode.insertBefore(messageDiv, form);
            
            // Scroll to message
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function validateForm() {
            const username = document.getElementById('username').value;
            const firstName = document.getElementById('first-name').value;
            const lastName = document.getElementById('last-name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const retypePassword = document.getElementById('retype-password').value;

            // Remove any existing messages
            const existingMessages = document.querySelectorAll('.message:not(.success)');
            existingMessages.forEach(msg => msg.remove());

            // Username validation
            if (username.length < 3) {
                showMessage('Username must be at least 3 characters long', 'error');
                return false;
            }

            // Name validation
            if (!firstName.trim() || !lastName.trim()) {
                showMessage('Please enter both first and last names', 'error');
                return false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Please enter a valid email address', 'error');
                return false;
            }

            // Password validation
            if (password.length < 6) {
                showMessage('Password must be at least 6 characters long', 'error');
                return false;
            }

            if (!/[A-Z]/.test(password)) {
                showMessage('Password must include at least one uppercase letter', 'error');
                return false;
            }

            if (!/\d/.test(password)) {
                showMessage('Password must include at least one number', 'error');
                return false;
            }

            if (!/[!@#$%^&*]/.test(password)) {
                showMessage('Password must include at least one special character (!@#$%^&*)', 'error');
                return false;
            }

            if (password !== retypePassword) {
                showMessage('Passwords do not match', 'error');
                return false;
            }

            return true;
        }

        <?php if ($redirectToLogin): ?>
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
<?php
ob_end_flush();
?>