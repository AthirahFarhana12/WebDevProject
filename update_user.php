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

// Fetch User Data
$id = $_GET['id'];
$role = $_GET['role'];
$table = '';
$fields = [];

if ($role == 'Admin') {
    $table = 'Admin';
    $fields = $conn->query("SELECT * FROM Admin WHERE AdminID = $id")->fetch_assoc();
} elseif ($role == 'Staff') {
    $table = 'Staff';
    $fields = $conn->query("SELECT * FROM Staff WHERE StaffID = $id")->fetch_assoc();
} elseif ($role == 'Customer') {
    $table = 'Customer';
    $fields = $conn->query("SELECT * FROM Customer WHERE CustomerID = $id")->fetch_assoc();
}

// Update Logic
if (isset($_POST['update'])) {
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $newRole = $_POST['role'];

    if ($newRole == 'Admin') {
        $conn->query("INSERT INTO Admin (AdminEmail, AdminPassword) VALUES ('$email', '$password')");
        $conn->query("DELETE FROM $table WHERE {$role}ID = $id");
    } elseif ($newRole == 'Staff') {
        $conn->query("INSERT INTO Staff (StaffName, StaffEmail, StaffPassword) VALUES ('$name', '$email', '$password')");
        $conn->query("DELETE FROM $table WHERE {$role}ID = $id");
    } elseif ($newRole == 'Customer') {
        $conn->query("INSERT INTO Customer (CustomerName, CustomerEmail, CustomerPassword) VALUES ('$name', '$email', '$password')");
        $conn->query("DELETE FROM $table WHERE {$role}ID = $id");
    }

    header("Location: display_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
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
        input, select {
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
    <header>Update User</header>
    <div class="container">
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $fields[$role == 'Staff' ? 'StaffName' : ($role == 'Customer' ? 'CustomerName' : 'AdminEmail')]; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $fields[$role == 'Admin' ? 'AdminEmail' : ($role == 'Staff' ? 'StaffEmail' : 'CustomerEmail')]; ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="<?php echo $fields[$role == 'Admin' ? 'AdminPassword' : ($role == 'Staff' ? 'StaffPassword' : 'CustomerPassword')]; ?>" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="Admin" <?php if($role == 'Admin') echo 'selected'; ?>>Admin</option>
                <option value="Staff" <?php if($role == 'Staff') echo 'selected'; ?>>Staff</option>
                <option value="Customer" <?php if($role == 'Customer') echo 'selected'; ?>>Customer</option>
            </select>

            <button type="submit" name="update">Update</button>
        </form>
    </div>
</body>
</html>
