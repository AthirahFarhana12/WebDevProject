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

// Fetch users
$admins = $conn->query("SELECT * FROM Admin");
$staffs = $conn->query("SELECT * FROM Staff");
$customers = $conn->query("SELECT * FROM Customer");

// Handle Delete
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $role = $_POST['role'];
    if ($role == 'Admin') $conn->query("DELETE FROM Admin WHERE AdminID = $id");
    elseif ($role == 'Staff') $conn->query("DELETE FROM Staff WHERE StaffID = $id");
    elseif ($role == 'Customer') $conn->query("DELETE FROM Customer WHERE CustomerID = $id");
    header("Location: display_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
            width: 90%; 
            margin: 20px auto;
        }
        table {
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            background-color: #fff;
        }
        th, td {
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: center;
        }
        th {
            background-color: #007bff; 
            color: white;
        }
        h2 {
            color: #007bff; 
            border-bottom: 2px solid #ddd; 
            padding-bottom: 10px; 
            margin-top: 30px;
        }
        button, a {
            display: inline-block; 
            margin: 5px; 
            padding: 8px 12px; 
            color: white; 
            background-color: #007bff; 
            text-decoration: none; 
            border: none; 
            border-radius: 5px;
        }
        button:hover, a:hover {
            background-color: #0056b3; 
            cursor: pointer;
        }
        .add-button {
            display: inline-block; 
            margin: 10px 0; 
            padding: 10px 20px; 
            background-color: #28a745; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
        }
        .add-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <header>User Management</header>
    <div class="container">
        <h2>Admin Table</h2>
        <table>
            <tr><th>ID</th><th>Email</th><th>Actions</th></tr>
            <?php while ($row = $admins->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['AdminID']; ?></td>
                    <td><?php echo $row['AdminEmail']; ?></td>
                    <td>
                        <a href="update_user.php?id=<?php echo $row['AdminID']; ?>&role=Admin">Update</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['AdminID']; ?>">
                            <input type="hidden" name="role" value="Admin">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h2>Staff Table</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
            <?php while ($row = $staffs->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['StaffID']; ?></td>
                    <td><?php echo $row['StaffName']; ?></td>
                    <td><?php echo $row['StaffEmail']; ?></td>
                    <td>
                        <a href="update_user.php?id=<?php echo $row['StaffID']; ?>&role=Staff">Update</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['StaffID']; ?>">
                            <input type="hidden" name="role" value="Staff">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <a href="add_staff.php" class="add-button">Add Staff</a>

        <h2>Customer Table</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
            <?php while ($row = $customers->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['CustomerID']; ?></td>
                    <td><?php echo $row['CustomerName']; ?></td>
                    <td><?php echo $row['CustomerEmail']; ?></td>
                    <td>
                        <a href="update_user.php?id=<?php echo $row['CustomerID']; ?>&role=Customer">Update</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['CustomerID']; ?>">
                            <input type="hidden" name="role" value="Customer">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
