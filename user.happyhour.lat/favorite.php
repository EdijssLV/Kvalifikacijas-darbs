<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$db = new SQLite3('/var/www/mysite/database/kabinets.db');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["drink_key"])) {
    $user_id = $_SESSION["user_id"];
    $drink_key = $_POST["drink_key"];

    // Check if the drink is already favorited
    $checkQuery = $db->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = :user_id AND drink_key = :drink_key");
    $checkQuery->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
    $checkQuery->bindValue(":drink_key", $drink_key, SQLITE3_TEXT);
    $result = $checkQuery->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result['count'] > 0) {
        // If already favorited, remove from favorites
        $deleteQuery = $db->prepare("DELETE FROM favorites WHERE user_id = :user_id AND drink_key = :drink_key");
        $deleteQuery->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
        $deleteQuery->bindValue(":drink_key", $drink_key, SQLITE3_TEXT);
        $deleteQuery->execute();
    } else {
        // Otherwise, add to favorites
        $insertQuery = $db->prepare("INSERT INTO favorites (user_id, drink_key) VALUES (:user_id, :drink_key)");
        $insertQuery->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
        $insertQuery->bindValue(":drink_key", $drink_key, SQLITE3_TEXT);
        $insertQuery->execute();
    }
}

// Redirect back to profile
header("Location: profile.php");
exit;