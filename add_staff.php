<?php
// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$db = "ClaimsManagementDB";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert Logic
if (isset($_POST['add_staff'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conn->query("INSERT INTO Staff (StaffName, StaffEmail, StaffPassword) VALUES ('$name', '$email', '$password')");
    header("Location: display_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff</title>
    <style>
        body {
            font-family: Arial, sans-serif; 
            margin: 0; 
            background-color: #f4f4f9; 
            color: #333;
        }
        header {
            background-color: #007bff; 
            color: #fff; 
            padding: 10px; 
            text-align: center; 
            font-size: 24px;
        }
        .container {
            width: 50%; 
            margin: 30px auto; 
            background-color: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block; 
            margin: 10px 0 5px; 
            color: #333;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px;
        }
        button {
            background-color: #007bff; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>Add Staff</header>
    <div class="container">
        <form method="POST">
            <label for="name">Staff Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" name="add_staff">Add Staff</button>
        </form>
    </div>
</body>
</html>
