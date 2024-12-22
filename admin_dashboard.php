<?php
// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$db = "ClaimsManagementDB";

// Create connection
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch statistics
$totalVisitors = $conn->query("SELECT COUNT(DISTINCT CustomerID) AS total FROM AuditLogs")->fetch_assoc()['total'];
$reportedLostItems = $conn->query("SELECT COUNT(*) AS total FROM ClaimSubmission")->fetch_assoc()['total'];
$itemsFound = $conn->query("SELECT COUNT(*) AS total FROM Item")->fetch_assoc()['total'];
$successfullyReturned = $conn->query("SELECT COUNT(*) AS total FROM Item WHERE Status='Returned'")->fetch_assoc()['total'];
$unclaimedItems = $conn->query("SELECT COUNT(*) AS total FROM Item WHERE Status='Unclaimed'")->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        header { background: #007bff; color: #fff; padding: 15px; text-align: center; font-size: 1.5em; }
        .container { display: flex; flex-wrap: wrap; justify-content: center; margin: 20px; }
        .card { background: #fff; margin: 10px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.3); width: 250px; text-align: center; }
        .card h3 { margin: 0 0 10px; font-size: 1.2em; color: #333; }
        .card p { margin: 0; font-size: 2em; color: #007bff; }
    </style>
</head>
<body>
    <header>Admin Dashboard</header>
    <div class="container">
        <div class="card">
            <h3>Total Visitors</h3>
            <p><?php echo $totalVisitors; ?></p>
        </div>
        <div class="card">
            <h3>Reported Lost Items</h3>
            <p><?php echo $reportedLostItems; ?></p>
        </div>
        <div class="card">
            <h3>Items Found</h3>
            <p><?php echo $itemsFound; ?></p>
        </div>
        <div class="card">
            <h3>Successfully Returned</h3>
            <p><?php echo $successfullyReturned; ?></p>
        </div>
        <div class="card">
            <h3>Unclaimed Items</h3>
            <p><?php echo $unclaimedItems; ?></p>
        </div>
    </div>
</body>
</html>
