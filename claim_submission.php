<?php
// Start the session to store and display messages
session_start(); 

require_once 'db_connection.php'; // Assuming a database connection is established

// Check if the 'item_id' parameter exists in the URL
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    // Fetch the item details from the database using the 'item_id'
    $stmt = $db->prepare("SELECT * FROM found_items WHERE id = :item_id");
    $stmt->bindParam(':item_id', $item_id);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // If the item exists in the database, show the claim form
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get data from form
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];

            // Insert the claim into the database (assuming the database table is named 'claims')
            $claimStmt = $db->prepare("INSERT INTO claims (item_id, name, phone, email) VALUES (:item_id, :name, :phone, :email)");
            $claimStmt->bindParam(':item_id', $item_id);
            $claimStmt->bindParam(':name', $name);
            $claimStmt->bindParam(':phone', $phone);
            $claimStmt->bindParam(':email', $email);

            if ($claimStmt->execute()) {
                // Set a session message indicating success
                $_SESSION['claim_message'] = 'Your claim has been submitted successfully!';
                // Set a session variable for redirect (to be used in JS)
                $_SESSION['redirect_to_homepage'] = true;
            } else {
                // Set a session message indicating failure
                $_SESSION['claim_message'] = 'There was an error submitting your claim. Please try again.';
            }

            // Refresh the page to display the message
            header("Location: claim_submission.php?item_id=$item_id");
            exit;  // Stop further execution to prevent any accidental output
        }
    } else {
        echo "Item not found.";
        exit;
    }
} else {
    echo "Invalid item.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="customer.css">
    <title>Claim Submission</title>
    <style>
        .claim-form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .claim-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .claim-form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        .claim-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .claim-form button {
            width: 100%;
            padding: 10px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .claim-form button:hover {
            background-color: #00509e;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .item-details {
            text-align: center;
            margin-bottom: 30px;
        }

        .item-details img {
            max-width: 300px;
            height: auto;
            border-radius: 5px;
        }

        .item-details h4 {
            margin-top: 10px;
            font-size: 1.2rem;
        }

        .item-details p {
            color: #555;
        }

        /* Notification style */
        .notification {
            background-color: #28a745;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-notification {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <h1>Claim Submission</h1>
        </div>
    </header>
    <main>
        <h2>Claim Your Lost Item</h2>

        <!-- Display notification message if it exists -->
        <?php
        if (isset($_SESSION['claim_message'])) {
            $message_class = (strpos($_SESSION['claim_message'], 'error') !== false) ? 'error-notification' : 'notification';
            echo "<div class='notification $message_class'>" . $_SESSION['claim_message'] . "</div>";
            // Clear the session message after it has been displayed
            unset($_SESSION['claim_message']);
        }
        ?>

        <!-- Display the item details -->
        <div class="item-details">
            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Item Image">
            <h4><?= htmlspecialchars($item['item_name']) ?></h4>
            <p><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></p>
        </div>

        <!-- Claim form -->
        <form class="claim-form" action="claim_submission.php?item_id=<?= htmlspecialchars($item_id) ?>" method="POST">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" required>

            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Submit Claim</button>
        </form>

        <a href="customerhomepage.php" class="back-link">Back to Homepage</a>
    </main>

    <!-- JavaScript to handle automatic redirect after displaying message -->
    <script>
        <?php if (isset($_SESSION['redirect_to_homepage']) && $_SESSION['redirect_to_homepage']): ?>
            // Delay redirect by 2 seconds to allow the success message to appear
            setTimeout(function() {
                window.location.href = "customerhomepage.php";
            }, 2000);
            // Clear the session flag after redirecting
            <?php unset($_SESSION['redirect_to_homepage']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
