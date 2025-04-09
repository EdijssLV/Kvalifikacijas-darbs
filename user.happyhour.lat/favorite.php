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

    // Check if drink is already liked
    $check = $db->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = :uid AND drink_key = :dkey");
    $check->bindValue(":uid", $user_id, SQLITE3_INTEGER);
    $check->bindValue(":dkey", $drink_key, SQLITE3_TEXT);
    $res = $check->execute()->fetchArray(SQLITE3_ASSOC);

    if ($res['count'] > 0) {
        // Already liked → remove
        $del = $db->prepare("DELETE FROM favorites WHERE user_id = :uid AND drink_key = :dkey");
        $del->bindValue(":uid", $user_id, SQLITE3_INTEGER);
        $del->bindValue(":dkey", $drink_key, SQLITE3_TEXT);
        $del->execute();
    } else {
        // Not liked yet → insert
        $ins = $db->prepare("INSERT INTO favorites (user_id, drink_key) VALUES (:uid, :dkey)");
        $ins->bindValue(":uid", $user_id, SQLITE3_INTEGER);
        $ins->bindValue(":dkey", $drink_key, SQLITE3_TEXT);
        $ins->execute();
    }
}

header("Location: profile.php");
exit;