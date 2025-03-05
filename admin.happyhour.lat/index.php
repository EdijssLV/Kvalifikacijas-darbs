<?php
session_start();
require 'config.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';

    if (password_verify($password, PASSWORD_HASH)) {
        $_SESSION['loggedin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid password!";
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Kabinets</title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="icon" href="/var/www/mysite/images/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/var/www/mysite/images/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="icon" href="/var/www/mysite/images/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/var/www/mysite/images/favicon-192x192.png" sizes="192x192" type="image/png">

    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Kabinets">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#c6995f">
</head>
<body class="formLapa" background="http://st.depositphotos.com/1987851/1904/i/450/depositphotos_19041043-Old-wallpaper-seamless-texture.jpg">
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="password" id="pswd" name="password" placeholder="Parole.."><br>
        <button type="submit" class="btn">Log in</button>
    </form>
</body>
</html>