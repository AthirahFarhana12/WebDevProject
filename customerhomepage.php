<?php
// File: customer_homepage.php
require_once 'db_connection.php'; // Assuming a database connection is established
$items = $db->query("SELECT * FROM found_items WHERE status = 'unclaimed'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="customer.css">
    <title>Customer Homepage</title>
    <style>
        /* Ensure there's enough space below the header for content */
        body {
            margin: 0;
            padding: 0;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        main {
            margin-top: 80px; /* Space for the fixed header */
            padding: 20px;
            height: calc(100vh - 80px); /* Ensure the main content area fills the remaining screen height */
            overflow-y: auto; /* Enable scrolling for the main content */
        }

        .items-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
            max-height: 100%; /* Allow the items container to take up the remaining space */
        }

        .item-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
            width: 250px;
            text-align: center;
            background-color: #f9f9f9;
        }

        .item-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .item-card h4 {
            margin: 10px 0;
        }

        .item-card p {
            font-size: 0.9rem;
            color: #555;
        }

        .item-card button {
            padding: 10px 15px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .item-card button:hover {
            background-color: #00509e;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <h1>Lost and Found System</h1>
        </div>
        <nav>
            <ul>
                <li><a href="customerhomepage.php">Home</a></li>
                <li><a href="claim_submission.php">Claim Submission</a></li>
                <li><a href="profile_page.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <center><h2>Welcome to the Lost and Found Management System</h2></center>
        <center><p>Browse the items below and claim if one belongs to you.</p></center>
        <section>

            <div class="items-container">
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Item Image">
                        <h4><?= htmlspecialchars($item['item_name']) ?></h4>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <form action="claim_submission.php" method="GET">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <button type="submit">Claim This Item</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Railway Organization. All Rights Reserved.</p>
    </footer>
</body>
</html>
