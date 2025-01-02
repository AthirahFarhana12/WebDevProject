<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=LostAndFound', 'root', ''); // Replace with your DB credentials
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
